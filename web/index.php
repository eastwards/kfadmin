<?
/**
 * 项目入口
 */
set_time_limit(0);							//设置程序运行超时时间
ob_start();									//打开磁盘缓冲(加快速度)
define('WebDir', '.');						//定义项目路径
define('ProjectDir', '../');
define('ResourceDir', ProjectDir.'/Resource');
define('AppDir', ProjectDir.'/App');
define('DataDir', ProjectDir.'/Data');
define('ConfigDir', ProjectDir.'/Config');
define('StaticDir', '/static/');

require('../Spring/Spring.php');			//载入入口文件
require(ConfigDir.'/app.config.php');		//载入应用全局配置
Spring::run();								//启动框架
ob_end_flush();								//输出全部内容到浏览器
?>