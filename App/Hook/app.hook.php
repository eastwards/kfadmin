<?
/**
 * 登陆检查
 *
 * 登录检查、其它检查等
 *
 * @package	Hook
 * @author	void
 * @since	2014-12-09
 */
class Hook
{
	/**
	 * 当前被请求的控制器
	 */
	public $mod    = '';

	/**
	 * 当前被请求的操作
	 */
	public $action = '';


	/**
	 * 钩子方法拦截用户操作
	 * @author	void
	 * @since	2014-12-09
	 *
	 * @access	public
	 * @return	void
	 */
	public function work()
	{
		//$this->check($this->mod, $this->action );
	}

	/**
	 * 拦截用户操作(验证登录、验证权限)
	 * @author	void
	 * @since	2014-12-15
	 *
	 * @access	private
	 * @param	string  $mod	控制器
	 * @param	string  $action 操作
	 * @return	void
	 */
	private function check($mod, $action)
	{
		$mods = array(
			'index' => '*',
			'authcode' => '*',
			);
		$allow = false;
		
		if ( isset($mods[$mod]) ) {
			if ( is_array($mods[$mod]) ) {
				$allow = in_array($action, $mods[$mod]) ? true : false;
			} else {
				$allow = $mods[$mod] == '*' ? true : false;
			}
		}
		
		$login = LoginAuth::check();
		if ( !$allow && !$login) {
			goUrl('您还没有登录!', '/');
			exit;
		}

		if ( $login ) {
			return '';
			$role = LoginAuth::get('role');
			$op   = "$mod.$action";
			if ( !hasAuth($role, $op) ) {
				$url = isset($_SERVER['HTTP_REFERER']) 
					   ? $_SERVER['HTTP_REFERER'] 
					   : '/index/main/';
				goUrl('您没有操作权限', $url);
			}
		}
	}
}
?>