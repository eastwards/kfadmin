<?
/**
 * 404控制器
 *
 * 显示404错误
 *
 * @package	Action
 * @author	void
 * @since	2014-12-09
 */
class ErrorAction extends Action
{
	/**
	 * 显示404错误
	 * @author	void
	 * @since	2014-12-09
	 *
	 * @access	public
	 * @return	void
	 */
	public function index()
	{
		//根据应用需要自行定义
		MessageBox::halt('正在开发中...');
	}
}
?>