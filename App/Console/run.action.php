<?
/**
 * 后台执行入口
 *
 * 执行所有后台功能队列，总是在进程中执行着。
 * 服务器环境需要配置cronjob 对其进行实时执行。
 *
 * @package     Action
 * @author      Xuni
 * @since       2015-01-13
 */
class RunAction extends QueueCommonAction
{
    private $cacheType      = 'redisQc';//缓存类型
    //进程管理设置
    private $runName    = array(
                 array(
                     'path' => '/cron/run/',
                     'name' => 'tradeCronCache',//用于重复进程判断，需要在执行时设置缓存值为true
                     ),
//                 array(
//                     'path' => '/queue/index/',
//                     'name' => 'tradeQueueCache',//用于重复进程判断，需要在执行时设置缓存值为true
//                     ),
                );

    /**
     * 执行设置的队列管理或其他管理进程
     *
     * @access public
     * @return void
     */
    public function index()
    {
        is_array($this->runName) || die('nothing to do');
        //$objRs = $this->com($this->cacheType);//获取缓存资源
        foreach ($this->runName as $course) {
            $cmd = PHPPath." ".CmdDir."/cmd.php ".$course['path'];
            $this->execInBg($cmd);
        }
    }

    /**
     * 后台执行进程
     *
     * @access public
     * @param  string    $cmd       需要执行的命令表达式
     * @return void
     */
    function execInBg($cmd)
    { 
        if (substr(php_uname(), 0, 7) == "Windows"){
            pclose(popen("start /B ". $cmd, "r"));
        }else { 
            exec($cmd . " > /dev/null &");   
        } 
    } 

}
?>