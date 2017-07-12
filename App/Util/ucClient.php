<?php
/**
 * 用户管理中心类
 * 
 * @author Inna
 * @since 2015-03-04
 */
class ucClient
{
    /**
     * 接口请求体
     * 
     * @access public
     * @author Inna
     * @since 2015-03-09
     * @param string $type 接口名
     * @param array 额外参数
     * @return mixed
     */
    public static function ucRequest($type, $extra = array())
    {
        $timestamp	= time();
        $code   	= md5(NAV_LOGIN_KEY.NAV_LOGIN_SYSTEMID.$timestamp);
        $param 		= array(
            'systemId'  => NAV_LOGIN_SYSTEMID,
            'code'      => $code,
            'timestamp' => $timestamp,
        );
        if(!empty($extra) && is_array($extra)) {
            $param = array_merge($extra, $param);
        }
        $url 	= NAV_SSO_HOST.$type.'/?'.http_build_query($param);
        $result = file_get_contents($url);
        return $result;
    }
    /**
     * 登陆
     *  
     * @param int $uid
     * @return string
     */
    public static function synlogin($uid, $return=false)
    {
        if($return) {
            return self::ucRequest('synlogin', array('uid' => $uid));
        }
		echo self::ucRequest('synlogin', array('uid' => $uid));
    }
    /**
     * 退出
     * 
     * @param string $token
     * @return string
     */
    public static function synlogout($token, $return=false)
    {
		if($return) {
            return self::ucRequest('synlogout', array('token' => $token));
        }
		echo self::ucRequest('synlogout', array('token' => $token));
    }
    /**
     * 通过key获取用户信息
     * 
     * @param string $key
     * @return array
     */
    public static function userInfo($key)
    {
        return json_decode(self::ucRequest('getUserInfo', array('key' => $key)), true);
    }
}