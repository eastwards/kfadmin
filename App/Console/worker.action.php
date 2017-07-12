<?
/**
 * 队列进程管理
 *
 * 对默认队列进程数进行管控，可根据服务器性能设置总进程数量。
 * 注意：在程序意外中止时，会有小于2秒的暂时停止。
 *
 * @package     ConsoleAction
 * @author      Xuni
 * @since       2015-04-15
 */
class WorkerAction extends QueueCommonAction
{
    const TOTAL     = 10;//总进程数
    const SECOND    = 5;//超时秒数

    protected $cacheType      = 'redisQc';//缓存类型
    protected $courseNum      = self::TOTAL;
    protected $cacheName      = 'tradeQueueCache';//进程的缓存标识，在本文件中判断重复会出现多进程！！！
    protected $queueMethod    = '/queuework/test/';//对应处理队列的方法
    protected $thread;//进程列表
    protected $objC;//缓存资源
    protected $cmd;//框架cmd命令

    public function before()
    {
        //可以在超时后执行，destruct无法执行。
        register_shutdown_function(array(&$this,'destroy'));

        $this->cmd  = sprintf("%s %s%s ",PHPPath, CmdDir, "/cmd.php");
        $this->objC = $this->com($this->cacheType);//获取缓存资源

        if ( !is_object($this->objC) ) exit('queue cache error');
    }
    
    /**
     * 管理队列并执行它
     *
     * @access public
     * @return void
     */
    public function run()
    {
        set_time_limit(0);
        $cache = $this->objC->get($this->cacheName);//获取队列管理管理进程标识
        $cache && exit('queue running');//同时只能启动一个管理进程。

        $num = 0;
        for ($i = 0; $i < self::TOTAL; $i++) {             
            //设置此管理进程的标识，同时只能启动一个管理进程。
            //(2秒超时，防止后台杀死进程后再无法启动进程)
            $flag = $this->objC->set($this->cacheName, true, self::SECOND);
            if ( !$flag ) exit('queue cache can not use');

            if ( $this->courseNum > 0 ) {
                 $this->objC->close();
                if ( $this->doQueue() ) $this->courseNum -= 1;
            }
        }
        $this->wait();
        //执行完成后删除本队列管理进程标识。
        $this->objC->remove($this->cacheName);
        exit('queue finished');
    }

    public function wait()
    {
        while ( true ) {
            //每小于固定进程数后，检查进程是否执行完毕。（完毕后会释放数量）
            if  ( $this->courseNum < self::TOTAL ) $this->listening();

            if ( $this->courseNum >= self::TOTAL ) break;

            //如果进程已满，sleep 100毫秒//0.1秒
            if ( $this->courseNum <= 0 ) usleep(100000);//100毫秒//0.1秒
        }
    }

    /**
     * 数据入队
     *
     * @access public
     * @param  array    $queue       队列数据
     * @return bool
     */
    protected function doQueue()
    {
        $resouce        = array(array('pipe','r'));
        $cmd            = $this->cmd.$this->queueMethod;
        $this->thread[] = proc_open($cmd, $resouce, $tmp);
        return true;
    }

    /**
     * 关闭进程
     *
     * @access public
     * @param  object    $resouce       进程资源
     * @return bool
     */
    protected function closeCourse($resouce)
    {
        //wirte log //TODO
        @proc_close($resouce);
    }


    /**
     * 处理所有进程
     *
     * @access public
     * @param  string    $kill       是否杀死进程（'yes'、'no'）
     * @return bool
     */
    protected function listening($kill = 'no')
    {
        if ( !is_array($this->thread) ) return true;
        $tmpThread = $this->thread;
        foreach ($tmpThread as $key => $course) {
            //防止处理时间超过设置时间。
            $this->objC->set($this->cacheName, true, self::SECOND);
            
            //判断资源是否释放
            if ( !is_resource($this->thread[$key]) ){
                $this->courseNum++;//释放
                unset($this->thread[$key]);
                echo "child unset({$key})"."\n";
                //error_log("child unset({$key})".date('Y-m-d H:i:s')." \n ", 3, LogDir.'/test.log');
                continue;
            }
            $status = @proc_get_status($this->thread[$key]);
            if ($status['running'] == false || $kill == 'yes'){
                $this->closeCourse($this->thread[$key]);//释放
                $this->courseNum++;//释放
                unset($this->thread[$key]);
                echo "child unset({$key})"."\n";
                //error_log("child unset({$key})".date('Y-m-d H:i:s')." \n ", 3, LogDir.'/test.log');
            }
        }
    }

    /**
     * 销毁队列进程与标识
     *
     * @access public
     * @return void
     */
    public function destroy()
    {
        //是否在退出时删除所有数量（进程数由courseNum控制）
        //异常退出时，关闭所有执行的进程（正常退出，表示队列已空。）
        $this->listening('yes');
        $this->thread   = null;
        $this->objC     = null;
    }


}
?>