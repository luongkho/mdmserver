<?php

namespace Mcenter\Resource;

use Tonic\Resource;

/**
 * Mail resource
 *
 *
 * @uri /mail
 * @author hoanvd
 */
class MailResource extends Tonic\Resource {

	/**
	 * 
	 * 
	 * @uri /:id
	 * @method GET
	 * @param  string $tenantId
	 * @return Array
	 */
	public function getMailMessage($tenantId) {
		
	}

	/**
	 * 
	 * @method POST
	 * @param  Array $object
	 * @return void
	 */
	public function setMailMessage($object) {
		
	}

	/**
	 * @uri /config
	 * @method GET
	 * @return Array
	 */
	public function getMailConfig() {
		
	}

	/**
	 * @uri
	 * @method PUT
	 * @return void
	 */
	public function updateMailConfig() {
		
	}

}
