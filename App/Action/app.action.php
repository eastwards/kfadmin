<?
/**
 * 应用公用控制器
 *
 * 存放应用的公共方法
 *
 * @package	Action
 * @author	void
 * @since	2015-01-28
 */
abstract class AppAction extends Action
{
	/**
	 * 每页显示多少行
	 */
	public $rowNum  = 20;

	public $username = null;
	
	public $userId   = null;
	
	public $roleId   = null;
	/**
	 * 前置操作(框架自动调用)
	 * @author	void
	 * @since	2015-01-28
	 *
	 * @access	public
	 * @return	void
	 */
	public function before()
	{
		$this->mineId = $_COOKIE['wm-kefu-user-id'];
		//自定义业务逻辑
		$this->getUser();

		//有用户账号就必须判断账号是否有效
		if ( !empty($this->username) ){
			$userinfo = $this->load('member')->get($this->username);
			if(empty($userinfo) || $userinfo['isUse'] == 2) {
				Session::clear(COOKIE_USER);
				$this->redirect('', '/role/error');
			}
			$this->roleId 	= $userinfo['roleId'];
			$roleInfo 		= $this->load('role')->getRoleById($this->roleId);
			$_role 			= empty($roleInfo['role']) ? array() : explode(',', $roleInfo['role']);
			if ( $roleInfo['isUse'] == 2 ){
				Session::clear(COOKIE_USER);
				$this->redirect('', '/role/error');
			}
			$this->hasRole = $_role;
			$this->set('roleId' , $this->roleId);
			$this->set('_role_' , $this->hasRole);
		}
		
		$roleList = C('ROLE_LIST');
		if ( isset($roleList[$this->mod.'/'.$this->action]) ) {//权限判断范围
			//判断权限时，必须要登录状态
			if ( empty($this->username) || empty($this->userId) ){
				$this->redirect('', '/index/');
			}
			$roleNo = $roleList[$this->mod.'/'.$this->action];
			if ( !in_array($roleNo, $this->hasRole) ){
				$this->redirect('', '/role/error');
			}
		}
		
        $this->set('static_version', 11456);//静态文件版本号>>控制js,css缓存
		$this->set('username' , $this->username);
		$this->set('userId'   , $this->userId);
	}

	/**
	 * 后置操作(框架自动调用)
	 * @author	void
	 * @since	2015-01-28
	 *
	 * @access	public
	 * @return	void
	 */
	public function after()
	{

	}

	/**
	 * 输出json数据
	 *
	 * @author	Xuni
	 * @since	2015-11-06
	 *
	 * @access	public
	 * @return	void
	 */
	protected function returnAjax($data=array())
	{
		$jsonStr = json_encode($data);
		exit($jsonStr);
	}

	private function getUser()
	{
		$userinfo = Session::get(COOKIE_USER);
		if ( empty($userinfo) ){
			$this->username = '';
			$this->userId 	= '';
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

	//图片上传
	public function ajaxUploadPic()
    {
    	$kb = $this->input('size', 'int', 0);
        $msg = array(
            'code'  => 0,
            'msg'   => '',
            'img'   => '',
            );
        if ( empty($_FILES) || empty($_FILES['fileName']) ) {
            $msg['msg'] = '请上传图片';
            $this->returnAjax($msg);
        }
        if ( $kb > 0 && ($kb*1024 < $_FILES['fileName']['size']) ){
        	$msg['msg'] = "文件大小超过 $kb KB限制";
        	$this->returnAjax($msg);
        }
        $obj = $this->load('upload')->upload('fileName', 'img');
        if ( $obj->_imgUrl_ ){
            $msg['code']    = 1;
            $msg['img']     = $obj->_imgUrl_;
        }else{
            $msg['msg']     = $obj->msg;
        }
        $this->returnAjax($msg);
    }


	//获取来源页面地址并保存
	protected function getReferrUrl($action)
	{
		//配置项
		$referrArr 	= array(
			'internal_edit' => '/internal/index/',
			'viewuser' => '/visitlog/userlist/',
                        'patent_edit' => '/patent/index/',
			); 
		if ( empty($referrArr[$action]) ) return '/index/main/';

		$_referr 	= Session::get($action);
		if ( empty($_referr) ){
			if ( strpos($_SERVER['HTTP_REFERER'], $referrArr[$action]) !== false ){
				Session::set($action, $_SERVER['HTTP_REFERER']);
			}else{
				Session::set($action, $referrArr[$action]);
			}
		}else{
			if ( strpos($_SERVER['HTTP_REFERER'], $referrArr[$action]) !== false ){
				Session::set($action, $_SERVER['HTTP_REFERER']);
			}
		}
		return Session::get($action);
	}

	/**
	 * 检测当前url地址(操作)是否发送站内信
	 * @param $uid int|string 站内信的发送对象(群发以逗号隔开)
	 * @param $sendtype int 站内信的发送方式,默认对一,2对多,3全体
	 */
	protected function checkMsg($uid = null,$sendtype=1){
		if(!$uid) return;//用户为空,直接返回
		//设置发送的类型
		if(!in_array($sendtype,array(1,2,3))){
			$sendtype = 1;
		}
		//得到当前url地址
		$url = 'http://'.$_SERVER['HTTP_HOST'].'/'.$this->mod.'/'.$this->action;
		//得到监控触发的信息
		$monitor = $this->load('messege')->getMonitor();
		if($monitor){
			//判断当前url是否发送信息
			foreach($monitor as $item){
				if(strpos($item['url'],$url)!==false){
					$params = array();
					$params['title'] = $item['title'];
					$params['type'] = $item['type'];
					$params['sendtype'] = $sendtype;
					$params['content'] = $item['content'];
					$params['uids'] = $uid;//当前用户
					$this->load('messege')->createMsg($params);
					break;
				}
			}
		}
	}
}
?>