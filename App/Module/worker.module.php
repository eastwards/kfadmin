<?
/**
 * worker 多进程处理类
 *
 * 多进程处理对应功能类
 * 
 * @package     Module
 * @author      Xuni
 * @since       2016-10-18
 */
class WorkerModule extends AppModule
{
    public $runningKey   = '_trade_worker_default_' ;//主进程名
    public $cacheType    = 'redisQc' ;//缓存类型

    public $pid ;//主进程ID
    protected $worker ;//worker对象
    protected $running ;//是否运行
    protected $child ;//子进程数组

    const COUNT     = 10 ;
    const TIMEOUT   = 60 ;

    public function __construct()
    {
        // 只能在命令行中执行
        if (php_sapi_name() != "cli") {
            exit("code只能在cli命令行下执行");
        }
        // 检查PHP版本
        if (version_compare(PHP_VERSION, '5.3.0', 'lt')) 
        {
            exit("PHP版本必须5.3+");
        }
        // 需要pcntl扩展支持
        if ( !function_exists('pcntl_fork') ) 
        {
            exit("必须有pcntl扩展");
        }

        $this->objC = $this->com($this->cacheType);//获取缓存资源
        if ( !is_object($this->objC) ) {
           exit("缓存不可用");
        }
    }

    /*********************************************************
     *  设置worker类与方法
     *
     *
     *********************************************************/
    public function setWorker($worker, $method='run')
    {
        if ( empty($worker) || empty($method) ) {
            exit("worker与method不能为空");
        }
        //如果worker是类对象
        if ( is_object($worker) ){
            $callback = $worker;
            $worker = get_class($callback);
        }else{
            //组件对象
            $callback = $this->load($worker);
        }
        
        if ( !method_exists($callback, $method) ) {
            exit("worker中未找到{$method}方法");
        }
        $this->runningKey  .= "{$worker}_{$method}_" ;
        $this->callback     = array($callback, $method);
        return $this;
    }

    /*********************************************************
     *  使用callback方法进行多进程处理
     *
     *
     *********************************************************/
    public function setCallback($function)
    {
        if ( !is_callable($function) ){
            exit("方法不是callback类型");
        }
        $this->runningKey  .= md5(time())."_" ;
        $this->callback = $function;
        return $this;
    }

    /*********************************************************
     *  执行worker ，count决定子进程数量
     *
     *
     *********************************************************/
    public function run($count=1)
    {
        if ( !empty($this->pid) && $this->running ) {
            exit("worker正在运行");
        }
        $isRun = $this->objC->get($this->runningKey);
        if ( !empty($isRun) ){
            exit("worker正在运行");
        }
        $this->objC->close();

        $count = (intval($count) > 10 || intval($count) <= 0) ? self::COUNT : intval($count) ;

        for ($i = 0 ; $i < $count ; $i++) { 
            $pid = pcntl_fork();
            if ( $pid == -1 ){
                exit("fork未成功");
            }elseif ( $pid ){
                $this->running  = true;
                $this->pid      = posix_getpid();
                $this->child[]  = $pid;
            }else{
                call_user_func( $this->callback );
                exit(0);
            }
        }

        //$flag = $this->objC->set($this->runningKey, $this->pid, self::TIMEOUT);
        return $this->listen();
        //$this->wait();
    }

    /*********************************************************
     * 创建子进程监控主进程是否退出
     *
     *
     *********************************************************/
    protected function listen()
    {
        $flag = $this->objC->set($this->runningKey, $this->pid, self::TIMEOUT);
        if ( !$flag ){
            exit("缓存状态未成功");
        }
        //echo "listen start";        
        $this->objC->close();

        $pid = pcntl_fork();
        if ( $pid == -1 ){
            exit("listen进程fork未成功");
        }elseif ( $pid ){
            //listenning
            return $this;
        }else{
            while (true) {
                $isOut = exec("ps -ax | awk '{ print $1 }' | grep -e \"^{$this->pid}\"");
                error_log("isOut : $isOut -".date('Y-m-d H:i:s')." \n ", 3, LogDir.'/test.log');
                if ( !$isOut ) break;
                                
                $this->objC->set($this->runningKey, $this->pid, self::TIMEOUT);
                error_log("listen wait".date('Y-m-d H:i:s')." \n ", 3, LogDir.'/test.log');

                sleep(2);
            }
            error_log("listen ok -".date('Y-m-d H:i:s')." \n ", 3, LogDir.'/test.log');
            $this->objC->remove($this->runningKey);
            exit(0);
        }
    }

    /*********************************************************
     * 等待子进程退出
     *
     *
     *********************************************************/
    public function wait($sleep = 100000)
    {
        error_log("wait init {$this->runningKey}".date('Y-m-d H:i:s')." \n ", 3, LogDir.'/test.log');
        while ( count($this->child) > 0 ) {
            foreach ($this->child as $key => $id) {
                $res = pcntl_waitpid($pid, $status, WNOHANG);
                if ( $res == -1 || $res > 0 ) {
                    unset($this->child[$key]);
                    error_log("wait unset {$this->runningKey}".date('Y-m-d H:i:s')." \n ", 3, LogDir.'/test.log');
                }
            }
            error_log("wait {$this->runningKey}".date('Y-m-d H:i:s')." \n ", 3, LogDir.'/test.log');
            if ( empty($this->child) ) {
                error_log("wait empty {$this->runningKey}".date('Y-m-d H:i:s')." \n ", 3, LogDir.'/test.log');
                break;
            }
            usleep($sleep);
        }
        $this->running = false;
        error_log("wait ok {$this->runningKey}".date('Y-m-d H:i:s')." \n ", 3, LogDir.'/test.log');
    }

    protected function isRunning($pid)
    {
        //if ($this->running !== true) {
        //    return false;
        //}
                
        return true;
    }

}
?>