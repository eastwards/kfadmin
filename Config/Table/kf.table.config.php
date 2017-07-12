<?
$prefix		= 'kf_';
$dbId		= 'kf';
$configFile	= array( ConfigDir.'/Db/kf.master.config.php' );

$tbl['msglog'] = array(
	'name'		=> $prefix.'msglog',
	'dbId'		=> $dbId, 
	'configFile'=> $configFile,
);

$tbl['test'] = array(
	'name'		=> $prefix.'test',
	'dbId'		=> $dbId, 
	'configFile'=> $configFile,
);


?>