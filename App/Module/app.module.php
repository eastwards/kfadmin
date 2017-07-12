<?
/**
 * 应用公用业务组件
 *
 * 应用相关的业务方法
 * 
 * @package	Model
 * @author	void
 * @since	2015-06-12
 */
abstract class AppModule extends Module
{
	public function __construct()
	{
		//自定义业务逻辑
		$this->getUser();
	}

	/**
	 * 获取业务对象(系统对接时使用)
	 * @author	void
	 * @since	2015-03-26
	 *
	 * @access	public
	 * @param	string	$name	业务代理类名
	 * @return	object  返回业务对象
	 */
	public function importBi($name)
	{
		static $config = array();
		if ( empty($config) ) {
			require(ConfigDir.'/Extension/service.config.php');
		}
		
		static $objList = array();
		if ( isset($objList[$name]) && $objList[$name] ) {
			return $objList[$name];
		}

		$file = BiDir.'/'.strtolower($name).'.bi.php';
		require_once($file);
		$className      = $name.'Bi';
		$bi             = new $className();
		$bi->url        = $config[$bi->apiId]['url'];
		$objList[$name] = $bi;
		
		return $bi;
	}

	protected function getUser()
	{
		$userinfo = Session::get(COOKIE_USER);
		if ( empty($userinfo) ){
			$this->username = '';
			$this->userId 	= '0';
			$this->isLogin 	= false;
			return false;
		}else{
			$userinfo = unserialize($userinfo);
		}
		$this->username = $userinfo['username'];
		$this->userId 	= $userinfo['userId'];
		$this->isLogin 	= true;
		return true;
	}
        
  	/**
	 * 执行原生sql语句
	 * @author	void
	 * @since	2015-06-19
	 *
	 * @access	public
	 * @param	string	$dbName	数据库名
	 * @return	mixed
	 */
	protected function fetchAll($dbName, $sql)
	{
		static $db = null;
		if ( $db == null ) {
			$db = new DbQuery($dbName);
		}
		
		return $db->fetchAll($sql);
	}
	
	/**
	 * 检测当前url地址(操作)是否发送站内信
	 * @param $uid int|string 站内信的发送对象(群发以逗号隔开)
	 * @param $sendtype int 站内信的发送方式,默认对一,2对多,3全体
	 */
	protected function checkMsg($uid = null,$url="systemapi/addsale/"){
		if(!$uid) return;//用户为空,直接返回
		$sendtype 	= 1;
		//得到当前url地址
		$url 		= TRADE_URL.$url;
		//得到监控触发的信息
		$monitor 	= $this->load('messege')->getMonitor();
		if($monitor){
			//判断当前url是否发送信息
			foreach($monitor as $item){
				if(strpos($item['url'],$url)!==false){
					$params = array();
					$params['title'] 	= $item['title'];
					$params['type'] 	= $item['type'];
					$params['sendtype'] = $sendtype;
					$params['content'] 	= $item['content'];
					$params['uids'] 	= $uid;//当前用户
					$this->load('messege')->createMsg($params);
					break;
				}
			}
		}
	}

}
?>