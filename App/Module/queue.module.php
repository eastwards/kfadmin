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
class QueueModule extends AppModule
{
    /**
     *  默认队列处理的方法，方便记录日志与管理
     */
    public $methodList = array(
        'offpriceDown'       => '21',//特价下架，回归定价
        );

    /*
     * 特价下架，回归定价
     *
     * @author	Xuni
     * @since	2015-06-18
     */
    public function offpriceDown($data)
    {
        return $this->load('queuelib')->offpriceDown($data);
    }

    
}
?>