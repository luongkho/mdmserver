<?php

namespace Mcenter\Resource;
use Tonic\Resource,
    Tonic\Response;
/**
 * Description of IndexResource
 *
 * @uri /
 * @author hoanvd
 */
class IndexResource extends Resource {

	/**
	 * Returns the welcome message.
	 * @method GET
	 */
	public function welcomeMessage() {
		$body = <<<END
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<title>Insert title here</title>
</head>
<body>
This is RESTful created by Jersey
</body>
</html>
END;
		return new Response(Response::OK, $body, array(
			'content-type' => 'text/html'
		));
	}

}