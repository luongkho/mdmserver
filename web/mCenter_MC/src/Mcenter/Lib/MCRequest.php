<?php

namespace Mcenter\Lib;

use \Tonic\Request;

/**
 * User resource
 *
 *
 * @uri /user/
 * @author hoanvd
 */
class MCRequest extends Request {

	public function getParamsPost() {
		return $_POST;
	}

	public function getParamsGet() {
		return $_GET;
	}

}
