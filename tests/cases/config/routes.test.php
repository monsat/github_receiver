<?php

App::import('Core', 'Dispatcher');
class UrlRoutingTestCase extends CakeTestCase {
	var $Disp;
	var $url;

	function startCase() {
		$this->Disp =& new Dispatcher();
	}
	function endCase() {
		unset($this->Disp);
	}

	function startTest($method) {
		$method = substr($method, 4);
	}
	function endTest() {
	}
	/*
	 * http://www.1x1.jp/blog/2009/05/cakephp_routers_php_unittest.html
	 */
	public function testIndex() {
		// parse params
		$this->_set("/github_receiver/github_receiver/receive");
		$params = $this->Disp->parseParams($this->url);
		$this->assertIdentical($params['plugin'] ,"github_receiver");
		$this->assertIdentical($params['controller'] ,"github_receiver");
		$this->assertIdentical($params['action'] ,"receive");
		$this->assertIdentical($params['pass'] ,array());
		$this->assertIdentical($params['named'] ,array());
		//debug($params);
		
		// reverse routing
		$url = Router::url(array('plugin'=>"github_receiver", 'controller'=>"github_receiver", 'action'=>"receive"));
		$this->assertIdentical($url ,$this->url);
		//debug($url);
	}
	
	function _set($string) {
		$this->url = $string;
		return true;
	}

}