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
 * @uri /otps/getSetting
 * @author hoanvd
 */
class GetOtpSettingResource extends MCBaseResource {

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
	public function getOTPConfig()
  {
    $request = $this->request;
		$params = $request->getParamsPost();
    $token = $params['token'];
    $retVal = TRUE;
    $message = '';
    $isOk = true;
    $OTPTypeString = '';
    $otpInfo = null;
    //get params
    $username = $this->getUsername();
    $tenantId = $this->getTenantId();
    try {
      if ($isOk) {
        $isVerified = $this->verifyTokenForUser($username, $token, $tenantId);
        if ($isVerified) {
          $commonService = new CommonService();
          $otpInfo = $commonService->getOTPConfig($tenantId);
          if ($otpInfo != null) {
            $OTPType = $otpInfo['otp_type'];
            if (self::$ALPHABET_AND_NUMERIC == $OTPType) {
              $OTPTypeString = 1;
            } else if (self::$ONLY_ALPHABET == $OTPType) {
              $OTPTypeString = 2;
            } else if (self::$ONLY_NUMERIC == $OTPType) {
              $OTPTypeString = 3;
            } else {
              $retVal = false;
              $message = "Get OTP setting for tenant: " . $tenantId . " has occured error";
            }
            $returnVal = array(
              'OTPLength' => $otpInfo['otp_length'],
              'timeExpire' => $otpInfo['time_expire'],
              'OTPType' => $OTPTypeString
            );
          } else {
            $retVal = false;
            $message = "Can't get OTP setting of tenant : " . $tenantId;
          }
        } else {
          $retVal = false;
          $message = 'User identify is not valid. Please try again';
        }
      }
    } catch (\Exception $exc) {
      $message = 'Error has occured. Please contact Administration';
      $this->getLogger()->error($exc->getMessage());
    }
    $result = array(
      'message' => $message,
      'otpInfo' => isset($returnVal) ? $returnVal : null,
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
