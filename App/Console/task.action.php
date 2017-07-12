<?
/**
 * 后台任务控制器
 *
 * 执行后台任务
 *
 * @package	Action
 * @author	void
 * @since	2015-11-20
 */
class TaskAction extends ConsoleAction
{	
	/**
	 * 执行后台任务
	 * @author	void
	 * @since	2015-11-20
	 *
	 * @access	public
	 * @return	void
	 */
	public function work()
	{
		print time();
	}

	public function queue()
	{
		for ($i=0; $i < 100; $i++) { 
			
			$data 	= array(time());
			$method = rand(100, 10000);
			$this->load('queuelib')->addQueue($method, $data, 'test123');

		}
	}

	public function fork()
	{
		umask(0);
		$obj = $this->load('worker');

		$this->com('redisQc')->set('bbbb', 123, 60, 0);

		$obj->setWorker('test', 'run')->run(3);
		$obj->wait();

		sleep(1);
		$this->com('redisQc')->remove('bbbb', 0);
		// $redis->delete('bbbb');
		echo "\n fork finish... \n";
	}

	public function callback()
	{
		umask(0);
		$obj = $this->load('worker');
		$obj->setCallback(function(){
			sleep(rand(1,2));
			echo rand(1,9)."\n";
			sleep(rand(1,2));
			error_log("child end".date('Y-m-d H:i:s')." \n ", 3, LogDir.'/test.log');
		})->run(5);
		$obj->wait();
	}
}
?>