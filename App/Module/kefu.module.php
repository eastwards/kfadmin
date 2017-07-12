<?
/**
 * 应用业务组件基类
 *
 * 存放业务组件公共方法
 * 
 * @package	Model
 * @author	void
 * @since	2015-11-20
 */
class KefuModule extends AppModule
{
	/**
     * 引用业务模型
     */
    public $models = array(
        'msglog'		=> 'msglog',
    );

	public function getUnreadMsg($toId)
	{
		$r['eq'] = array(
			'toId' 		=> $toId,
			'isSend'	=> 2,
		);
		$r['limit'] = 1000;
		$r['order'] = array('timeline'=>'asc');

		$data = $this->import('msglog')->find($r);
		return $data;
	}

	public function makeMsg($data)
	{
		if ( empty($data) || !is_array($data) ) return array();
		$_data = [];
		foreach ($data as $k => $v) {
			$_arr = array(
				'username' 		=> $v['fromName'].$v['fromId'],
				'avatar' 		=> $v['avatar'],	
				'id' 			=> $v['fromId'],	
				'type' 			=> $v['type']==2?'':'kefu',	
				'content' 		=> $v['content'],	
				'timestamp' 	=> $v['timeline']*1000,
			);
			$_data[] = json_encode($_arr);
		}
		return $_data;
	}

	public function modifyMsglogIsSend($ids, $isSend)
	{
		$r['in'] 	= ['id'=>$ids];
		$data 		= ['isSend'=>1];
		return $this->import('msglog')->modify($data, $r);
	}

	public function getHistoryById($id, $mineId, $page)
	{
		$r['raw'] = " (fromId = {$id} AND toId = {$mineId}) OR (fromId = {$mineId} AND toId = {$id}) ";

		$r['page']	= $page;
		$r['limit']	= 10;
		$r['order']	= ['id'=>'asc'];

		return $this->import('msglog')->findAll($r);
	}
	
	public function getHistoryCount($id, $mineId)
	{
		$r['raw'] = " (fromId = {$id} AND toId = {$mineId}) OR (fromId = {$mineId} AND toId = {$id}) ";

		return $this->import('msglog')->count($r);
	}
}
?>