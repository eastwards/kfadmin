<?
/**
* 国内商标
*
* 国内商标商品创建，修改，删除等
*
* @package	Module
* @author	Xuni
* @since	2015-12-30
*/
class LogModule extends AppModule
{
    public $models = array(
        'api'       => 'apiLog',
        'system'    => 'systemLog',
    );

    /**
     * 添加API日志
     * @author      Xuni
     * @since       2016-03-01
     * 
     * @access      public
     * @param       array     $params     数据包
     * @return      void
     */
    public function addApiLog($param, $type, $status, $desc='', $memo='')
    {
        $data = array(
            'user'      => $param['user'],
            'type'      => $type,
            'status'    => $status,
            'data'      => $param['data'],
            'desc'      => $desc,
            'memo'      => $memo,
            );

        return $this->_addApiLog($data);
    }

    /**
     * 获取用户信息
     * @author      Xuni
     * @since       2016-03-01
     *
     * @access      public
     * @param       array     $params     数据包
     * @return      void
     */
    protected function _addApiLog($params)
    {
        $user       = empty($params['user']) ? 0 : $params['user'];
        $msg        = empty($params['desc']) ? '' : $params['desc'];
        $memo       = empty($params['memo']) ? '' : $params['memo'];
        $type       = empty($params['type']) ? 0 : $params['type'];
        $status     = empty($params['status']) ? 0 : $params['status'];
        $data       = empty($params['data']) ? 0 : $params['data'];

        if ( is_array($data) ) $data = serialize($data);
        if ( is_array($memo) ) $memo = serialize($memo);

        $log = array(
            'user'      => $user,
            'type'      => $type,
            'status'    => $status,
            'data'      => $data,
            'created'   => time(),
            'desc'      => $msg,
            'memo'      => $memo,
            );

        return $this->import('api')->create($log);
    }

    public function addSystemLog($params)
    {
        $msg        = empty($params['desc']) ? '' : $params['desc'];
        $memo       = empty($params['memo']) ? '' : $params['memo'];
        $type       = empty($params['type']) ? '0' : $params['type'];
        $action     = empty($params['action']) ? '0' : $params['action'];
        $status     = empty($params['status']) ? '1' : $params['status'];
        $data       = empty($params['data']) ? '' : $params['data'];

        $data       = is_array($data) ? serialize($data) : $data;
        $memo       = is_array($memo) ? serialize($memo) : $memo;

        $log = array(
            'type'      => $type,
            'action'    => $action,
            'status'    => $status,
            'data'      => $data,
            'desc'      => $msg,
            'created'   => time(),
            'memo'      => $memo,
            );

        return $this->import('system')->create($log);
    }

}
?>