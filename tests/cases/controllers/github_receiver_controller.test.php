<?php
App::import('Controller', 'GithubReceiver.GithubReceiver');

class TestGithubReceiverController extends CakeTestCase {
	var $GithubReceiverController;
	
	function startCase() {
		echo '<h2>start</h2>';
	}
	
	function startTest(){
		$this->GithubReceiverController = new GithubReceiverController();
		$this->GithubReceiverController->is_log = false;
	}
	
	function endTest(){
		unset($this->GithubReceiverController);
		Configure::delete('GithubReceiver');
	}
	// _check()
	function testInternalCheck(){
		$this->assertIdentical($this->GithubReceiverController->_check(true, "") ,true);
	}
	function testInternalCheckFalse(){
		$this->assertIdentical($this->GithubReceiverController->_check(false, "") ,false);
	}
	// _setDefaults()
	function testInternalSetDefaults(){
		$tmp = array(
			'enable' => false,
			'filters' => array(
				'branches' => array("refs/heads/master"),
				'url' => "http://github.com/monsat/github_receiver",
			),
		);
		Configure::write('GithubReceiver', $tmp);
		$defaults = $this->GithubReceiverController->settings;
		$result = $this->GithubReceiverController->_setDefaults();
		$this->assertIdentical($result, Set::merge($defaults, $tmp));
	}
	function testInternalSetDefaultsNull(){
		$defaults = $this->GithubReceiverController->settings;
		$result = $this->GithubReceiverController->_setDefaults();
		$this->assertIdentical($result, $defaults);
	}
	// _enableCheck()
	function testInternalEnableCheck(){
		Configure::write('GithubReceiver', array(
			'enable' => true,
		));
		$this->GithubReceiverController->_setDefaults();
		$this->assertTrue($this->GithubReceiverController->_checkEnable());
	}
	function testInternalEnableCheckFalse(){
		Configure::write('GithubReceiver', array(
			'enable' => false,
		));
		$this->GithubReceiverController->_setDefaults();
		$this->assertFalse($this->GithubReceiverController->_checkEnable());
	}
	function testInternalEnableCheckNull(){
		Configure::write('GithubReceiver', array());
		$this->GithubReceiverController->_setDefaults();
		$this->assertFalse($this->GithubReceiverController->_checkEnable());
	}
	// _loadPayload()
	function testLoadPayload(){
		$_POST['payload'] = json_encode(array('test'=>"test-test"));
		$this->assertIdentical($this->GithubReceiverController->_loadPayload() ,json_decode($_POST['payload']));
	}
	// _checkBranches()
	function testCheckBranches(){
		// empty
		$_POST['payload'] = json_encode(array('ref' => "refs/heads/master"));
		$this->GithubReceiverController->_loadPayload();
		$this->assertTrue($this->GithubReceiverController->_checkBranches());
		// true
		$this->GithubReceiverController->_setDefaults();
		list($this->filter_name, $this->filter) = array('filtername', array('branches' => array("refs/heads/master")));
		$this->assertTrue($this->GithubReceiverController->_checkBranches());
	}
	function testCheckBranchesFalse(){
		$_POST['payload'] = json_encode(array('ref' => "refs/heads/master"));
		$this->GithubReceiverController->_setDefaults();
		$this->GithubReceiverController->_loadPayload();
		list($this->GithubReceiverController->filter_name, $this->GithubReceiverController->filter) = array('filtername', array('branches' => array("refs/heads/nobranch")));
		$this->assertFalse($this->GithubReceiverController->_checkBranches());
	}
	// _checkUrl()
	function testCheckUrl(){
		// empty
		$_POST['payload'] = json_encode(array('repository' => array('url' => "http://github.com/monsat/github_receiver")));
		$this->GithubReceiverController->_loadPayload();
		$this->assertTrue($this->GithubReceiverController->_checkUrl());
		// true
		$this->GithubReceiverController->_setDefaults();
		list($this->GithubReceiverController->filter_name, $this->GithubReceiverController->filter) = array('filtername', array('url' => "http://github.com/monsat/github_receiver"));
		$this->assertTrue($this->GithubReceiverController->_checkUrl());
	}
	function testCheckUrlFalse(){
		$_POST['payload'] = json_encode(array('repository' => array('url' => "http://github.com/monsat/github_receiver")));
		$this->GithubReceiverController->_setDefaults();
		$this->GithubReceiverController->_loadPayload();
		list($this->GithubReceiverController->filter_name, $this->GithubReceiverController->filter) = array('filtername', array('url' => "http://github.com/monsat/norepository"));
		$this->assertFalse($this->GithubReceiverController->_checkUrl());
	}
	// _filepath()
	function testFilepath(){
		$result = CACHE . "github_pull_prepare_test";
		$this->GithubReceiverController->filter_name = "test";
		$this->assertIdentical($this->GithubReceiverController->_filepath("pull_prepare"), $result);
	}
	// _isReadyFileExists()
	function testIsReadyFileExists() {
	}
	// _putPrepareFile()
	function testPutPrepareFile() {
	}
}