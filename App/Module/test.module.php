<?
/**
 * 队列处理
 *
 * 处理队列方法
 * 
 * @package	Module
 * @author	Xuni
 * @since	2015-06-18
 */
class TestModule extends AppModule
{
    
    public function run($data)
    {        
        error_log('test run start '.date('Y-m-d H:i:s')." \n ", 3, LogDir.'/test.log');
        //sleep(rand(1,2));
        while ( true ) {
            $size   = $this->com('redisQ')->name('tradeQueue')->size();

            if ( $size == 0 ) {
                error_log('queue size zero '.date('Y-m-d H:i:s')." \n ", 3, LogDir.'/test.log');
                break;
            }else{
                $data = $this->com('redisQ')->name('tradeQueue')->pop();
                error_log("queue size ({$size})".date('Y-m-d H:i:s')." \n ", 3, LogDir.'/test.log');
            }
         
            error_log('queue ok '.date('Y-m-d H:i:s')." \n ", 3, LogDir.'/test.log');
            sleep(rand(1,2));
        }
        return true;
    }

    
}
?>