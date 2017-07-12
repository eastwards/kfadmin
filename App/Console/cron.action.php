<?
/**
 * 队列进程管理
 *
 * 对默认队列进程数进行管控，可根据服务器性能设置总进程数量。
 * 注意：在程序意外中止时，会有小于2秒的暂时停止。
 *
 * @package     ConsoleAction
 * @author      Xuni
 * @since       2015-06-15
 */
class CronAction extends QueueCommonAction
{
    private $cacheType      = 'redisQc';//缓存类型
    //private $cacheName      = 'tradeCronCache';//进程的缓存标识，在本文件中判断重复会出现多进程！！！
    private $queueModule    = '/cronjob/%s/';//队列执行组件

    function __construct()
    {
        //可以在超时后执行，destruct无法执行。
        //register_shutdown_function(array(&$this,'destroy'));
        $this->cmd = sprintf("%s %s%s ",PHPPath, CmdDir, "/cmd.php");
        //$this->cacheName = $this->encodeCacheName($this->cacheName);
    }

    private function taskList()
    {
        $list = array(
            //每隔什么时间（秒）
            array(
                'type' => 'time',//设置间隔时间执行
                'time' => 3600,//格式为秒
                'func' => 'test',
                'name' => 'test-3600-check-cron',//设置执行文件唯一标识（每个task执行名称需不同）
                ),
            array(
                'type' => 'time',//设置间隔时间执行
                'time' => 600,//格式为秒
                'func' => 'updateOffpriceDown',
                'name' => 'trade_update_OffpriceDown_cron',//设置执行文件唯一标识（每个task执行名称需不同）
            ),
             //每周什么时段
            array(
                'type' => 'week',//设置时间执行一次
                'day'  => '5',// 1~7 （周一到周日）
                'time' => '23:23',//24小时制时间如：08:30（表示早上8点半）
                'func' => 'updateRegDate',
                'name' => 'trade_update_regDate_cron',//设置执行文件唯一标识（每个task执行名称需不同）
                ),
            array(
                'type' => 'week',//设置时间执行一次
                'day'  => '6',// 1~7 （周一到周日）
                'time' => '00:11',//24小时制时间如：08:30（表示早上8点半）
                'func' => 'updateEndDate',
                'name' => 'trade_update_endDate_cron',//设置执行文件唯一标识（每个task执行名称需不同）
            ),
            //每天什么时段
            array(
                'type' => 'day',//设置时间执行一次
                'time' => '00:51',//24小时制时间如：08:30（表示早上8点半）
                'func' => 'updateGoodsLessFive',
                'name' => 'trade_update_goodsLessFive_cron',//设置执行文件唯一标识（每个task执行名称需不同）
            ),
            array(
                'type' => 'day',//设置时间执行一次
                'time' => '00:31',//24小时制时间如：08:30（表示早上8点半）
                'func' => 'updateSoonFallDue',
                'name' => 'trade_update_soonFallDue_cron',//设置执行文件唯一标识（每个task执行名称需不同）
            ),
             //每周什么时段（）
             array(
                 'type' => 'week',//设置时间执行一次
                 'day'  => '1',// 1~7 （周一到周日）
                 'time' => '01:00',//24小时制时间如：08:30（表示早上8点半）
                 'func' => 'delGoodsSale',
                 'name' => 'testweek1500',//设置执行文件唯一标识（每个task执行名称需不同）
                 ),
            //每月什么时段
            array(
                'type' => 'month',//设置时间执行一次
                'day'  => '10',// 1~28、29、30、31 （每月几号）
                'time' => '00:00',//24小时制时间如：08:30（表示早上8点半）
                'func' => 'delAd',
                'name' => 'Ad_month_cron',//设置执行文件唯一标识（每个task执行名称需不同）
                ),
            );

        return $list;
    }

    public function run()
    {
        set_time_limit(0);
        $objRs = $this->com($this->cacheType);//获取缓存资源
        //$cache = $objRs->get($this->cacheName);//获取队列管理管理进程标识

        if ( !is_object($objRs) ) {
            $this->errorLog('', 'reids server client error');
            exit('cron cache error');
        }
        //$cache && exit('task running');//处理中时，不再处理
        //$objRs->set($this->cacheName, true, 2);//设置60秒为效，保证此方法只执行一次。
        $list = $this->taskList();
        foreach ($list as $k => $v) {
            $res = $this->doRun($v);
        }
        //$objRs->remove($this->cacheName);
        exit('task finished');
    }

    private function doRun($params)
    {
        $type   = $params['type'];
        $day    = $params['day'] ? $params['day'] : 1;
        $time   = $params['time'];
        $func   = $params['func'];
        $name   = $params['name'];

        if ( $type == 'day' ) return $this->doDay($time, $func, $name, $params);
        if ( $type == 'time' ) return $this->doTime($time, $func, $name, $params);
        if ( $type == 'week' ) return $this->doWeek($day, $time, $func, $name, $params);
        if ( $type == 'month' ) return $this->doMonth($day, $time, $func, $name, $params);
        return false;
    }

    //每隔什么时间执行（秒）
    private function doTime($time, $func, $name, $params)
    {
        $objRs = $this->com($this->cacheType);//获取缓存资源
        $cache = $objRs->get($name);//获取进程标识
        if (  $time <= 0 || $cache ) return false;

        $flag = $objRs->set($name, true, $time);//设置60秒为效，保证每间隔多少时间执行一次。
        if ( !$flag ){
            $this->errorLog($params);
            return false;
        }

        //执行相关文件
        $res = $this->doQueue($func);
        $log = array(
            'type'      => '4',
            'action'    => '50',
            'data'      => $params,
            'status'    => $res==true?1:2,
            'desc'      => $name,
            'memo'      => 'doTime-'.$time,
        );
        $this->load('log')->addSystemLog($log);
        //Log::write(print_r($log,1), date('Y-m-d').'-cronjob-doTime.log');
        return $res;
    }

    //每天什么时间执行
    private function doDay($time, $func, $name, $params)
    {
        $objRs = $this->com($this->cacheType);//获取缓存资源
        $cache = $objRs->get($name);//获取进程标识

        if ( $cache ) return false; 
        if ( $time != date('H:i') ) return false;//是否为当前设置时间
        
        $flag = $objRs->set($name, true, 62);//设置60秒为效，保证此方法只执行一次。
        if ( !$flag ){
            $this->errorLog($params);
            return false;
        }

        //执行相关文件
        $res = $this->doQueue($func);
        $log = array(
            'type'      => '4',
            'action'    => '51',
            'data'      => $params,
            'status'    => $res==true?1:2,
            'desc'      => $name,
            'memo'      => 'doDay-'.$time,
        );
        $this->load('log')->addSystemLog($log);
        //Log::write(print_r($log,1), date('Y-m-d').'-cronjob-doDay.log');
        return $res;
    }
    
    //每周几什么时段执行
    private function doWeek($day, $time, $func, $name, $params)
    {
        $objRs = $this->com($this->cacheType);//获取缓存资源
        $cache = $objRs->get($name);//获取进程标识

        if ( $cache ) return false; 
        if ( $day == 7 ) $day = 0;
        //判断周几是否正确
        if (  $day != date('w') ) return false;
        if ( $time != date('H:i') ) return false;//是否为当前设置时间
        
        $flag = $objRs->set($name, true, 62);//设置60秒为效，保证此方法只执行一次。
        if ( !$flag ){
            $this->errorLog($params);
            return false;
        }

        //执行相关文件
        $res = $this->doQueue($func);
        $log = array(
            'type'      => '4',
            'action'    => '52',
            'data'      => $params,
            'status'    => $res==true?1:2,
            'desc'      => $name,
            'memo'      => 'doWeek-'.$day.'-'.$time,
        );
        $this->load('log')->addSystemLog($log);
        //Log::write(print_r($log,1), date('Y-m-d').'-cronjob-doWeek.log');
        return $res;
    }

    //每月什么日期什么时间执行
    private function doMonth($day, $time, $func, $name, $params)
    {
        $objRs = $this->com($this->cacheType);//获取缓存资源
        $cache = $objRs->get($name);//获取进程标识
        if ( $cache ) return false; 

        //判断日期是否正确
        if (  $day != date('j') ) return false;
        if ( $time != date('H:i') ) return false;//是否为当前设置时间

        $flag = $objRs->set($name, true, 62);//设置60秒为效，保证此方法只执行一次。
        if ( !$flag ){
            $this->errorLog($params);
            return false;
        }

        //执行相关文件
        $res = $this->doQueue($func);
        $log = array(
            'type'      => '4',
            'action'    => '53',
            'data'      => $params,
            'status'    => $res==true?1:2,
            'desc'      => $name,
            'memo'      => 'doMonth-'.$day.'-'.$time,
        );
        $this->load('log')->addSystemLog($log);
        //Log::write(print_r($log,1), date('Y-m-d').'-cronjob-doMonth.log');
        return $res;
    }

    /**
     * 执行文件
     *
     * @access public
     * @param  array    $queue       队列数据
     * @return bool
     */
    private function doQueue($func)
    {
        $resouce    = array(array('pipe','r'));
        $param      = sprintf($this->queueModule, $func);
        $cmd        = $this->cmd.$param;
        $thread     = proc_open($cmd, $resouce, $tmp);
        $res        = is_resource($thread) ? true : false;
        return $res;
    }

    private function errorLog($params, $memo='redis cache can not set anymore')
    {
        $log = array(
            'type'      => '4',
            'action'    => '60',
            'data'      => $params,
            'status'    => 2,
            'desc'      => 'redis error',
            'memo'      => $memo,
        );
        $this->load('log')->addSystemLog($log);
    }

}
?>