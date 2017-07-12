<?php 
/**
 * 登录
 *
 * 网站首页
 *
 * @package	Action
 * @author	void
 * @since	2014-12-17
 */
class KefuAction extends AppAction
{

	/**
	 * 
	 *
	 */
	public function getUnreadMsg()
	{
		$id = $this->input('id', 'string', '');

		$res = $this->load('kefu')->getUnreadMsg($id);
		if ( empty($res) ){
			echo json_encode(['code'=>0,'msg'=>'','unread_messages'=>[]]);
			exit;
		}
		$data = array(
			'code' 				=> 0,
			'msg'				=> '',
			'unread_messages'	=> $this->load('kefu')->makeMsg($res),
		);
		echo( json_encode($data) );
		$ids = arrayColumn($res, 'id');
		$this->load('kefu')->modifyMsglogIsSend($ids, 1);
		exit;
	} 

	public function getKefuInfo()
	{
		$arr = [
		    "code"=> 0,
		    "msg"=> "",
		    "data"=> [
		        "mine"=> [
		            "username"=> "客服123",
		            "id"=> "123",
		            "status"=> "online",
		            "sign"=> "客服在线",
		            "avatar"=> "http://xishanpo.com/static/images/default_avatar_male_180.gif"
		        ]
		    ]
		];
		echo json_encode($arr);
	}

	public function history()
	{
		$this->set('limit', 10);
		$this->display();
	}

	public function getHistory()
	{
		$id 	= $this->input('id', 'string', '');
		$type 	= $this->input('type', 'string', '');
		$page 	= $this->input('page', 'int', '1');
		$flag 	= $this->input('flag', 'int', '');
		$mineId = '';//$this->input('mineId', 'string', '');

		$_mineId = empty($mineId) ? $this->mineId : $mineId;
		if( !empty($flag) ){
			$count = $this->load('kefu')->getHistoryCount($id, $_mineId);
			return $this->returnAjax(['code' => 1, 'data' => $count, 'msg' => 'success']);
		}

		if ( empty($id) ) return $this->returnAjax(['code' => -1, 'data' => '', 'msg' => '参数错误']);

		$res = $this->load('kefu')->getHistoryById($id, $_mineId, $page);

		if ( empty($res['rows']) ) return $this->returnAjax(['code' => -1, 'data' => '', 'msg' => '没有记录']);
		$res['limit'] = 10;
		return $this->returnAjax(['code' => 1, 'data' => $res, 'msg' => 'success']);
	}
}
?>