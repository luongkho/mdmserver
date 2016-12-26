<?php

namespace Mcenter\Service;

use Mcenter\DAO\OTPConfigurationDAO;
use Mcenter\Resource\SaveOtpResource;
use Mcenter\Util\RandomStringUtil;

/**
 * Description of OTPCodeGenerator
 * Generate OTP code
 *
 * @author hoanvd
 */
class OTPCodeGenerator {

	/**
	 * Generate OTP code
	 *
	 * @param string $tenantId
	 * @return string OTP code
	 */
	public static function generateOTP($tenantId) {
		$code = '';
		//remove l,o,i
		$alphabet = array('a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'j', 'k', 'm', 'n', 'p', 'q', 'r', 's', 't', 'u',
			'v', 'w', 'x', 'y', 'z', 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'J', 'K', 'L', 'M', 'N', 'P', 'Q',
			'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z');
		$alphanumerric = array('a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'j', 'k', 'm', 'n', 'p', 'q', 'r', 's', 't',
			'u', 'v', 'w', 'x', 'y', 'z', 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'J', 'K', 'L', 'M', 'N', 'P',
			'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', '1', '2', '3', '4', '5', '6', '7', '8', '9');
		$numeric = array('1', '2', '3', '4', '5', '6', '7', '8', '9');
		try {
			$otpConfigDAO = new OTPConfigurationDAO($tenantId);
			$config = $otpConfigDAO->getOTPConfig();
			if (is_array($config) && $config['otp_length'] > 0) {
				$OTPType = $config['otp_type'];
				if (strtolower(SaveOtpResource::$ALPHABET_AND_NUMERIC) === strtolower($OTPType)) {
					$code = RandomStringUtil::random($config['otp_length'], $alphanumerric);
				}
				if (strtolower(SaveOtpResource::$ONLY_ALPHABET) === strtolower($OTPType)) {
					$code = RandomStringUtil::random($config['otp_length'], $alphabet);
				}
				if (strtolower(SaveOtpResource::$ONLY_NUMERIC) === strtolower($OTPType)) {
					$code = RandomStringUtil::random($config['otp_length'], $numeric);
				}
				$code = substr($code, 0, 6);
			}
		} catch (\Exception $exc) {
      $this->getLogger()->error($exc->getMessage());
		}
		return $code;
	}

}