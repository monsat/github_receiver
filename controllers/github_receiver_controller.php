<?php

class GithubReceiverController extends GithubReceiverAppController {
	function receive() {
		if ($this->RequestHandler->isPost() && !empty($this->data["payload"])) {
			debug(true);
		}
		exit;
	}
}