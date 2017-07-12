<?
define('WebDir', __DIR__);						//定义项目路径
require('./Spring/Spring.php');		    //载入框架入口文件
require(LibDir.'/Util/Tool/MakeCode.php');	//载入代码生成工具

//指定数据库名、表前缀
$configs = array(
	array(
        'name'      => 'kf',
        'db'        => 'kfadmin',
        'prefix'    => 'kf_',
        'contain'   => '*',
        ),
	);

Spring::init();
//指定数据库配置文件存放路径
MakeCode::$configFileDir = WebDir.'/Config/Db';
MakeCode::create($configs);
?>