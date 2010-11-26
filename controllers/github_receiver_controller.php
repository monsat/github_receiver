<?php
/*
 * If you want to customize settings, you write configs in app/config/bootstrap.php
	$example = array(
		'enable' => true,
		'is_log' => true,
		'filters' => array(
			'filtername' => array(
				'branches' => array("refs/heads/master"), // act if the branches are included
				'url' => "http://github.com/monsat/github_receiver", // act if the url is same
			),
		),
		'tests' => json_encode(
			array(
				'repository' => array('url' => "http://github.com/monsat/github_receiver"),
				'ref' => "refs/heads/master",
			)
		),
	);
	Configure::write('GithubReceiver', $example);
*/
class GithubReceiverController extends GithubReceiverAppController {
	var $uses = null;
	var $settings = array(
		'enable' => false,
		'cache_dir' => CACHE,
		'is_log' => true,
		'prefix' => "github_",
		'filename' => "pull",
		'filters' => array(),
	);
	var $is_act = true;
	var $post;
	var $payload;
	var $fileter_name;
	var $fileter;
	var $is_post = false;
	
	function beforeFilter() {
		$this->post = isset($_POST['payload']) ? $_POST['payload'] : null;
	}
	function receive() {
		$this->autoRender = false;
		if (!$this->_isPostFromGithub()) {
			return false;
		}
		$this->_loadPayload();
		// default settings
		$this->_setDefaults();
		// Act
		return $this->_receive();
	}
	
	function test() {
		$this->autoRender = false;
		$this->post = Configure::read('GithubReceiver.tests');
		$this->is_post = true;
		if ($this->_loadPayload() === false){
			debug("You must write settings in bootstrap.php");
		}
		$this->_setDefaults();
		return $this->_receive();
	}
	
	function check() {
		debug($this->_setDefaults());
	}
	
	
	function _receive() {
		// Act if enable flag is true
		if ($this->_checkEnable()) {
			if (empty($this->settings['filters'])) {
				$this->settings['filters'] = array('basic' => array());
			}
			foreach ($this->settings['filters'] as $this->filter_name => $this->filter) {
				$this->is_act = true;
				$this->_checkBranches();
				$this->_checkUrl();
				if ($this->is_act) {
					$this->_putFile();
				}
			}
			return true;
		}
		$this->_log("GitHubReceiver result : false");
		return false;
	}
	
	
	function _isPost() {
		return $this->is_post || $this->RequestHandler->isPost();
	}
	
	function _isPostFromGithub() {
		return $this->_isPost() && !empty($this->post);
	}
	
	function _loadPayload() {
		if (empty($this->post)) {
			return false;
		}
		return $this->payload = json_decode($this->post);
	}
	
	function _setDefaults() {
		$_settings = Configure::read('GithubReceiver');
		if (!$_settings) {
			return $this->settings;
		}
		return $this->settings = Set::merge($this->settings, $_settings);
	}
	
	function _checkEnable() {
		return $this->_check(
			!empty($this->settings['enable']),
			"You must write : Configure::write('GithubReceiver.enable', true);"
		);
	}
	
	function _checkBranches() {
		return $this->_check(
			empty($this->filter['branches']) || in_array($this->payload->ref, $this->filter['branches']),
			"This branch is denied.<br />If you want to accept this branch, <br />You must write : Configure::write('GithubReceiver.filters.branches', array(\"{$this->payload->ref}\"));"
		);
	}
	
	function _checkUrl() {
		return $this->_check(
			empty($this->filter['url']) || $this->payload->repository->url == $this->filter['url'],
			"This Github Repository is denied.<br />If you want to accept this github repository, <br />You must write : Configure::write('GithubReceiver.filters.xxx.url', \"{$this->payload->repository->url}\");"
		);
	}
	function _putFile() {
		$fullpath = $this->_filepath($this->settings['filename']);
		return file_put_contents($fullpath, $this->post);
	}
	
	
	function _check($expression, $error_message) {
		$result = true;
		if (!$expression) {
			$this->_log($error_message);
			$this->is_act = $result = false;
		}
		return $result;
	}
	
	function _filepath($filename = "", $prefix = "", $dir = "") {
		$surfix = empty($this->filter_name) ? "" : "_" . $this->filter_name;
		$dir = empty($dir) ? $this->settings['cache_dir'] : $dir;
		$prefix = empty($prefix) ? $this->settings['prefix'] : $prefix;
		return $dir . $prefix . $filename . $surfix;
	}
	
	function _log($message) {
		if ($this->settings['is_log']) {
			$this->log($message);
		}
	}
}