<?
/**
 * 定时任务处理方法类
 *
 * 将需要定时处理的方法放到本类中，方便在定时任务类中添加处理设置
 *
 * @package     ConsoleAction
 * @author      Xuni
 * @since       2015-04-15
 */
class CronjobAction extends QueueCommonAction
{

	/**
	 * 定时任务（测试）
     * 用于测试定时任务是否正常运行！
	 * @author	xuni
	 * @since	2015-06-26
	 *
	 * @access	public
	 * @return	boolean
	 */
    public function test()
    {
        return true;
    	//Log::write('hello world', date('Y-m-d').'-cronjob-test.log');
    }
    
    
    /**
    * 执行删除过期的广告
    * @author	Far
    * @since	2015-06-30
    * 每月10号凌晨执行
    * @access	public
    * @return	boolean
    */
    public function delAd(){
	   return $this->load('ad')->delPastAd();
    }
    
    /**
    * 执行删除过期的精品特卖
    * @author	Far
    * @since	2015-10-11
    * 每周一凌晨执行
    * @access	public
    * @return	boolean
    */
    public function delGoodsSale(){
	   return $this->load('channel')->delGoodsSale();
    }
    

    /**
    * 更新注册日期
    * @author   Xuni
    * @since    2016-07-27
    * 每周五午夜23点23分执行
    * @access   public
    * @return   boolean
    */
    public function updateRegDate(){
       return $this->load('task')->runRegDate(0);
    }

    /**
     * 更新有效期截止日期
     * @author   Xuni
     * @since    2016-10-10
     * 每周六凌晨0点11分执行
     * @access   public
     * @return   boolean
     */
    public function updateEndDate(){
        return $this->load('task')->runEndDate(0);
    }

    /**
     * 更新即将到期的数据
     * @author   Xuni
     * @since    2016-10-10
     * 每天凌晨0点31分执行
     * @access   public
     * @return   boolean
     */
    public function updateSoonFallDue()
    {
        return $this->load('task')->runSoonFallDue(0);
    }

    /**
     * 更新商品小于5项的数据
     * @author   Xuni
     * @since    2016-10-10
     * 每天凌晨0点51分执行
     * @access   public
     * @return   boolean
     */
    public function updateGoodsLessFive()
    {
        return $this->load('task')->runGoodsLessFive(0);
    }

    /**
     * 获取特价到期数据，放入队列进行处理
     * @author   Xuni
     * @since    2016-10-11
     * 每10分钟执行一次
     * @access   public
     * @return   boolean
     */
    public function updateOffpriceDown()
    {
        return $this->load('task')->runOffpriceGoods(0);
    }

}
?>