<?php
class Sugar_Test_comparison implements Sugar_Test {
	public function __construct()
	{
	}

	public function getExpected()
	{
		return file_get_contents(dirname(__FILE__).'/comparison.txt');
	}
	
	public function getResult(Sugar $sugar)
	{
		$tpl = $sugar->getTemplate('comparison.tpl');
		return $tpl->fetch();
	}
}
