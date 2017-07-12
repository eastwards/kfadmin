<?
/**
 * 网站首页
 *
 * 网站首页
 *
 * @package	Action
 * @author	void
 * @since	2014-12-17
 */
class IndexAction extends AppAction
{
	/**
	 * 后台登录页面(默认)
	 * 
	 * @author	garrett
	 * @since	2015-07-16
	 *
	 * @access	public
	 * @return	void
	 */
	public function index()
	{
		setcookie("wm-kefu-user-id", '123', time()+3600*24);

		$this->set('kf_id', '123');
		$this->set('kf_name', '客服123');
		$this->display();
	}
	
	/**
	 * 后台框架配置(默认)
	 * 
	 * @author	garrett
	 * @since	2015-07-16
	 *
	 * @access	public
	 * @return	void
	 */
	public function main()
	{
		if( $this->isLogin == false ){
			$this->redirect('', '/index/');
		}
		
		$this->display();
	}
	
	/**
	 * 左侧菜单(默认)
	 * 
	 * @author	garrett
	 * @since	2015-07-16
	 *
	 * @access	public
	 * @return	void
	 */
	public function left()
	{
		$menu 	= require_once ConfigDir.'/menu.config.php';
		$user   = $this->load('member')->get($this->username);
		$output = "";
		if ( !empty($user) ){
			foreach($menu as $item)
			{	//获取二级栏目
				$child_str = "";
				if(!empty($item['child']))
				{
					$child_first = '<UL class="menu" style="display:none;">';
					$child_middle = '';
					foreach($item['child'] as $child)
					{
						if(in_array($child['auth'], $this->hasRole)){
							$child_middle .= '<LI><a href="'.$child['url'].'" target="rightFrame" ><SPAN>'. $child['title'] . '</SPAN></a></LI>';
						}
					}
					if ( $child_middle ) $child_str .= $child_first.$child_middle.'</UL>';
				}
				//获取一级栏目
				if(empty($item['child'])){
					if(in_array($item['auth'], $this->hasRole)){
						$output .= '<LI><a href="'.$item['url'].'" target="rightFrame" ><DIV class="m_header"><SPAN style=\'background-image: url("/Static/'.$item['ico'].'");\' class="label">'.$item['title'].'</SPAN></DIV></a>';
					}
				}else{
					if(!empty($child_str)){
						$output .= '<LI><DIV class="m_header"><SPAN style=\'background-image: url("/Static/'.$item['ico'].'");\' class="label">'.$item['title'].'</SPAN><SPAN class="arrow up"></SPAN></DIV>';
						$output .= $child_str;
					}
				}
				$output .= '</LI>';
			}
		}
		$this->set('output' , $output);
		$this->display();
	}
	
	/**
	 * 后台框架头部显示内容(默认)
	 * 
	 * @author	garrett
	 * @since	2015-07-16
	 *
	 * @access	public
	 * @return	void
	 */
	public function top()
	{
		$this->set('count' , 0);
		$this->display();
	}
	
	/**
	 * 后台框架右侧显示内容(默认)
	 * 
	 * @author	garrett
	 * @since	2015-07-16
	 *
	 * @access	public
	 * @return	void
	 */
	public function right()
	{	
		$total 		= $this->load('internal')->countSaleStatus();
		$list 		= $this->load('internal')->getNoVerifySale();
		$noverify 	= count($list);
		$this->set('total' , $total);
		$this->set('noverify' , $noverify);
		$this->display();
	}
}
?>