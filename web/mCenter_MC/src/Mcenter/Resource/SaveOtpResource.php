<?php

namespace Mcenter\Resource;

use Mcenter\DAO\UserInfoDAO;
use Mcenter\Service\CommonService;
use Tonic;
use Mcenter\Lib\MCBaseResource;

/**
 * Save otp config user resource
 *
 *
 * @uri /otps/save
 * @author hoanvd
 */
class SaveOtpResource extends MCBaseResource {

	public static $ALPHABET_AND_NUMERIC = 'Alphabet and Numeric';
	public static $ONLY_ALPHABET = 'Only Alphabet';
	public static $ONLY_NUMERIC = 'Only Numeric';


	/**
	 * Save OTP configuration
	 *
	 * @method POST
	 * @provides application/json
	 * @json
	 * @return Tonic\Response
	 */
	public function saveOTPConfig() {
		$retVal = false;
		$message = '';
		$isOk = true;
		$OTPTypeString = '';
		//get params
		$request = $this->request;
		$params = $request->getParamsPost();
		$OTPLength = isset($params['OTPLength']) ? $params['OTPLength'] : null;
		$timeExpire = isset($params['timeExpire']) ? $params['timeExpire'] : null;
		$OTPType = isset($params['OTPType']) ? $params['OTPType'] : null;
		$token = isset($params['token']) ? $params['token'] : null;
		$username = $this->getUsername();
        $tenantId = $this->getTenantId();
		try {
			if (intval($OTPLength) >= 4 && intval($OTPLength <= 10)) {
				if (intval($timeExpire) >= 30 && intval($timeExpire <= 300)) {
					if (1 == $OTPType) {
						$OTPTypeString = self::$ALPHABET_AND_NUMERIC;
					} else if (2 == $OTPType) {
						$OTPTypeString = self::$ONLY_ALPHABET;
					} else if (3 == $OTPType) {
						$OTPTypeString = self::$ONLY_NUMERIC;
					} else {
						$isOk = false;
						$message = 'OTP Type is not valid. Please try again';
					}
					if ($isOk) {
						$isVerified = $this->verifyTokenForUser($username, $token, $tenantId);
						if ($isVerified) {
							$commonService = new CommonService();
							$saveOtpConfigOk = $commonService->saveOTPConfig($OTPLength, $timeExpire, $OTPTypeString, $tenantId);
							if (!$saveOtpConfigOk) {
								$retVal = false;
								$message = 'Update OTP Setting error';
								throw new \RuntimeException('Update fail');
							} else {
								$retVal = true;
							}
						} else {
							$retVal = false;
							$message = 'User identify is not valid. Please try again';
						}
					}
				} else {
					$message = 'timeExpired must be bigger than 30 and smaller than 300. Please try again.';
				}
			} else {
				$message = 'OTPLength must be bigger than 4 and smaller than 10. Please try again';
			}
		} catch (\Exception $exc) {
			$message = 'Error has occured. Please contact Administration';
      $this->getLogger()->error($exc->getMessage());
		}
		$result = array(
				'message' => $message,
				'success' => $retVal ? "true" : "false",
		);
		return new Tonic\Response(Tonic\Response::OK, json_encode($result));
	}
/**
 * Verify token user
 *
 * @param type $username Username to verify
 * @param type $token Token to verify
 * @param type $tenantId Tenant Id
 * @return boolean true on success or false on failure
 */
	public function verifyTokenUser($username, $token, $tenantId) {
		$retVal = false;
		if ($username != '' && $token != '') {
			$userInfoDao = new UserInfoDAO($tenantId);
			$userInfo = array(
					'user_name' => $username,
					'token' => $token,
          'tenant_id' => $tenantId
			);
			$retVal = $userInfoDao->verifyToken($userInfo);
		}
		return $retVal;
	}

}
