<?php

namespace Mcenter\Resource;

use Tonic;
use Mcenter\DAO\UserInfoDAO;
use Mcenter\Service\OTPCodeGenerator;
use Mcenter\Lib\GoogleAuthenticator;
use Mcenter\Lib\MCBaseResource;

/**
 * Verify OTP resource
 *
 *
 * @uri /user/verifyOTP
 * @author hoanvd
 */
class VerifyOtpResource extends MCBaseResource
{
  const NUM_OF_VERIFY_FAIL = 3;
  /**
   * Verify OTP
   *
   * @method POST
   * @provides application/json
   * @json
   * @return Tonic\Response
   */
  public function verifyOTP()
  {
    $retVal = false;
    $reLogin = false;
    $validateValue = 0;
    $message = '';
    $request = $this->request;
    $params = $request->getParamsPost();
    $otp = isset($params['otp']) ? $params['otp'] : null;
    $username = $this->getUsername();
    $tenantId = $this->getTenantId();
    $type = isset($params['type']) ? $params['type'] : 1;
    $userInfo = null;

    try {
        if ($username != '' && $otp != '') {
          if ($this->getTotalTimeVerifyFail() === self::NUM_OF_VERIFY_FAIL) {
            $reLogin = true;
            $this->unsetUserInfo();
            $message = 'Too many verify OTP failed. Please login again.';
          } else {
            $userInfoDao = new UserInfoDAO($tenantId);
            // 1 true; 0 : not match ; -1 : expired
            if ($type == 1) {
              $validateValue = $userInfoDao->verifyOTP($username, $otp);

              if ($validateValue === 1) {
                $isUpdateOtpOk = $userInfoDao->updateOTP($username, null);
                $userInfoDao->updateUserStatus($username, '1');
                $userInfo = $userInfoDao->getUserInfoByUsername($username);
                $retVal = true;
                $message = 'OTP authenticated successful';
              } else {
                $retVal = false;
                if ($validateValue === -1) {
                  // expired : update null OTP. message
                  $isUpdateOtpOk = $userInfoDao->updateOTP($username, null);
                  $message = 'OTP code is expired. Please login again for new OTP code';
                } else if ($validateValue === 0) {
                  $message = 'OTP code does not match.';
                }
              }
            } else if ($type == 2) {
              $googleAuthenticator = new GoogleAuthenticator();
              // get secret key of user
              $userInfo = $userInfoDao->getUserInfoByUsername($username);
              $secretKey = $userInfo['secret'];
              if ($secretKey == '') {
                $message = "OTP code does not match or OTP code is expired";
              } else {
                $isCodeValid = $googleAuthenticator->verifyCode($secretKey, $otp);
                if ($isCodeValid) {
                  $retVal = true;
                  $userInfoDao->updateUserStatus($username, '1');
                  $message = 'OTP authenticated successful';
                } else {
                  $message = 'OTP code does not match or OTP code is expired';
                }
              }
            } else {
              $message = 'Type is not valid!';
            }
          }
        }
    } catch (\Exception $exc) {
      $message = 'Error has occured. Please contact Administration';
    }

    $filterUserInfo = null;
    if ($retVal) {
      $this->setTotalTimeVerifyFail(0);
      // Generate token for this user
      $token = md5(uniqid(mt_rand(), true));
      $currentToken = $userInfoDao->getTokenByUserName($username);
      $tokenArray = json_decode($currentToken, true);
      $authorizeToken = \Mcenter\Lib\MCSession::getInstance()->getSessionId();
      $tokenArray[$authorizeToken] = $token;
      $userInfoDao->updateToken($username, json_encode($tokenArray));
      // filter userinfo
      $filterUserInfo = array(
        'email' => array_key_exists('email', $userInfo) ? $userInfo['email'] : null,
        'firstName' => array_key_exists('first_name', $userInfo) ? $userInfo['last_name'] : null,
        'lastName' => array_key_exists('last_name', $userInfo) ? $userInfo['last_name'] : null,
        'phoneNumber' => array_key_exists('phone_number', $userInfo) ? $userInfo['phone_number'] : null,
        'roleId' => array_key_exists('role_id', $userInfo) ? $userInfo['role_id'] : null,
        'tenantId' => array_key_exists('tenant_id', $userInfo) ? $userInfo['tenant_id'] : null,
        'userName' => array_key_exists('user_name', $userInfo) ? $userInfo['user_name'] : null,
        'token' => $token,
        'status' => array_key_exists('status', $userInfo) ? $userInfo['status'] : null,
      );
  } else {
    $this->setTotalTimeVerifyOTPFail();
    if ($this->getTotalTimeVerifyFail() === self::NUM_OF_VERIFY_FAIL) {
      $reLogin = true;
      $this->unsetUserInfo();
    }
  }

    return json_encode(array(
      'message' => $message,
      'success' => $retVal ? true : false,
      'userInfo' => $filterUserInfo,
      'token' => ($retVal == true && $userInfo != null) ? (array_key_exists('token', $userInfo) ? $userInfo['token'] : null) : null,
      'reLogin' => $reLogin
    ));
  }
  
  private function setTotalTimeVerifyOTPFail()
  {
    $totalTimeVerifyFail = $this->getTotalTimeVerifyFail();
    $totalTimeVerifyFail += 1;
    $this->setTotalTimeVerifyFail($totalTimeVerifyFail);
  }

}
