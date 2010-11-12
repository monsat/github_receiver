<?php

class GithubReceiverController extends GithubReceiverAppController {
	var $components = array("RequestHandler");
	
	function receive() {
		if ($this->RequestHandler->isPost() && !empty($this->data["payload"])) {
		}
		exit;
	}
}