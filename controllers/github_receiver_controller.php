<?php
/*
 * If you want to customize settings, you write configs in app/config/bootstrap.php
	$example = array(
		'filters' => array(
			'filtername' => array(
				'branches' => array(), // act if the branches are included
				'url' => "", // act if the url is same
			),
		),
	);
 * Configure::write('GithubReceiver', $example);
*/
class GithubReceiverController extends GithubReceiverAppController {
	var $uses = null;
	var $settings = array(
		'enable' => false,
		'cache_dir' => CACHE,
		'is_log' => true,
		'prefix' => "github_",
		'prepare_filename' => "pull_prepare",
		'ready_filename' => "pull_ready",
		'filters' => array(),
	);
	var $is_act = true;
	var $payload;
	var $fileter_name;
	var $fileter;
	var $is_post = false;
	
	function beforeFilter() {
	}
	function receive() {
		$this->autoRender = false;
		if (false && !$this->_isPostFromGithub()) {
			return false;
		}
		$this->_loadPayload();
		// default settings
		$this->_setDefaults();
		// Act if enable flag is true
		if ($this->_checkEnable()) {
			if (empty($this->settings['filters'])) {
				$this->settings['filters'] = array('basic' => array());
			}
			foreach ($this->settings['filters'] as $this->filter_name => $this->filter) {
				$this->is_act = true;
				$this->_checkBranches();
				$this->_checkUrl();
				// Act if the ready file is *not* exists
				if ($this->is_act && !$this->_isReadyFileExists()) {
					$this->_putPrepareFile();
				}
			}
			return true;
		}
		return false;
	}
	
	function test() {
		$tmp = array(
			'enable' => true,
			'filters' => array(
				'basic' => array(
					'branches' => array("refs/heads/master"),
					'url' => "http://github.com/monsat/github_receiver",
				),
			),
		);
		Configure::write('GithubReceiver', $tmp);
		$_POST['payload'] = json_encode(
			array(
				'repository' => array('url' => "http://github.com/monsat/github_receiver"),
				'ref' => "refs/heads/master",
			)
		);
		
		$this->is_post = true;
		$this->setAction('receive');
	}
	
	function _isPost() {
		return $this->is_post || $this->RequestHandler->isPost();
	}
	
	function _isPostFromGithub() {
		return !$this->_isPost() || empty($_POST['payload']);
	}
	
	function _loadPayload($post = array('payload' => array())) {
		$post = !empty($_POST['payload']) ? $_POST['payload'] : $post;
		return $this->payload = json_decode($post);
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
	function _isReadyFileExists() {
		$fullpath = $this->_filepath($this->settings['ready_filename']);
		return file_exists($fullpath);
	}
	function _putPrepareFile() {
		$fullpath = $this->_filepath($this->settings['prepare_filename']);
		return file_put_contents($fullpath, $_POST['payload']);
	}
	
	
	function _check($expression, $error_message) {
		$result = true;
		if (!$expression) {
			if ($this->settings['is_log']) {
				$this->log($error_message);
			}
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
}