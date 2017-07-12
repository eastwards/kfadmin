<?
/**
 * 队列处理
 *
 * 默认队列处理功能，暂时用于所有需要的地方
 *
 * @package     ConsoleAction
 * @author      Xuni
 * @since       2015-04-15
 */
class QueueWorkAction extends QueueCommonAction
{
    //private $cacheType          = 'redis';
    private $queueType          = 'redisQ';
    private $queueName          = 'tradeQueue';
    private $queueModuleName    = 'queue';
    private $queueModule;

    function before()
    {
        //可以在超时后执行，destruct无法执行。
        register_shutdown_function(array(&$this,'shutdown'));
        //设置执行队列命令的表达式
        $this->queueModule = $this->load($this->queueModuleName);
        //设置消息列表
        $this->messageList = array(
            '101' => 'model error or model is not object',
            '102' => 'method error',
            '103' => 'method has not',
            '104' => 'execute failed',
            '201' => 'execute success',
        );
    }

    public function test()
    {
        sleep(rand(2,3));
        //echo rand(1,9)."\n";
        sleep(rand(1,2));
        error_log("child end".date('Y-m-d H:i:s')." \n ", 3, LogDir.'/test.log');
    }
    
    /**
     * 管理队列并执行它
     *
     * @access  public
     * @return  void
     */
    public function run()
    {
        $objRq = $this->com($this->queueType);//获取队列资源
        $objRq->name($this->queueName);//设置队列名称

        while (true) {
            $size = $objRq->size();
            if ( $size == 0 ) break;
            $data = $objRq->pop();
            $this->doQueue($data);
        }
    }

    /**
     * 执行队列
     *
     * @access  public
     * @param   array   $queue      队列数据
     * @return  bool
     */
    private function doQueue($_data)
    {
        if ( empty($_data) ) return false;//队列数据为空，不执行

        $method = $_data['method'];//方法
        $data   = $_data['data'];//数据包

        if ( !is_object($this->queueModule) ){
            return $this->writeLog(2, '101', $_data);
        }

        if ( !array_key_exists($method, $this->queueModule->methodList) ){
            return $this->writeLog(2, '102', $_data);
        }

        if ( method_exists($this->queueModule, $method) ){
            $res = $this->queueModule->$method($data);
            if ( $res ) return $this->writeLog(1, '201', $_data);
        }else{
            return $this->writeLog(2, '103', $_data);
        }
        
        return $this->writeLog(2, '104', $_data);
    }

    /*
     * 生成队列处理日志
     *
     * @param   int     $status     是否成功（1：成功，2：失败）
     * @param   int     $key        消息ID
     * @param   array   $_data      队列数据
     */
    private function writeLog($status, $msgNo, $_data)
    {
        $method = empty($_data['method']) ? '': $_data['method'];//方法
        //$data   = empty($_data['data']) ? array(): $_data['data'];//数据包
        $memo   = empty($_data['memo']) ? '': $_data['memo'];//备注（可作为测试用）
        if ( !empty($method) && array_key_exists($method, $this->queueModule->methodList) ){
            $action = $this->queueModule->methodList[$method];
        }else{
            $action = '0';
        }
        $desc   = $this->getMsgByNo($msgNo);
        $log = array(
            'type'      => '3',
            'action'    => $action,
            'data'      => $_data,
            'status'    => $status,
            'desc'      => $desc,
            'memo'      => $memo,
            );
        return $this->load('log')->addSystemLog($log);
    }

    /*
     * 通过消息ID获取消息信息
     *
     * @param   int     $key    消息ID
     */
    private function getMsgByNo($key)
    {
        if ( isset($this->messageList[$key]) ) return $this->messageList[$key];

        return 'no message';
    }

    /**
     * 销毁队列进程与标识
     *
     * @access  public
     * @return  void
     */
    public function shutdown()
    {
        //处理异常退出的日志记录

    }

}
?>