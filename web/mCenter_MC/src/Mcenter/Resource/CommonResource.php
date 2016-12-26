<?php

namespace Mcenter\Resource;

use Tonic\Resource;

/**
 * Common resource
 *
 *
 * @uri /otps
 * @author hoanvd
 */
class CommonResource extends \Tonic\Resource {

	/**
	 * 
	 * 
	 * @uri /save
	 * @method POST
	 * @param  int $otpLength
	 * @param int $timeExpire Description
	 * @param  int $outType
	 * @param string $token Description
	 * @param string $userName Description
	 * @param string $tenantId Description
	 * @return Array
	 */
	public function saveOTPConfig($otpLength, $timeExpire, $otpType, $token, $userName, $tenantId) {
		
	}

	/**
	 * 
	 * @param string $userName Description
	 * @param string $token Description
	 * @param string $tenantId Description
	 * @return bool
	 */
	public function verifyTokenUser($userName, $token, $tenantId) {
		
	}

	/**
	 * 
	 * 
	 * @uri /getSetting
	 * @method POST
	 * @param string $token Description
	 * @param string $userName Description
	 * @param string $tenantId Description
	 * @return Array
	 */
	public function getOTPConfig($token, $userName, $tenantId) {
		
	}

}
