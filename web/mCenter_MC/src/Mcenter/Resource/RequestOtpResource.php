<?php

namespace Mcenter\Resource;

use Mcenter\DAO\UserInfoDAO;
use Mcenter\Service\OTPCodeGenerator;
use Mcenter\Exception;
use Mcenter\Service\Mail;
use Tonic;
use Mcenter\Lib\MCBaseResource;

/**
 * Generate OTP and send it to Client via Email or SMS
 *
 * @uri /user/requestOTP
 * @author hoanvd
 */
class RequestOtpResource extends MCBaseResource {

	/**
	 * Request OTP
	 *
	 * @method POST
	 * @provides application/json
	 * @json
	 * @return Tonic\Response
	 */
	public function requestOTP() {
		$retVal = false;
		$message = '';
		// get parameters
		$request = $this->request;
		$params = $request->getParamsPost();
		$username = $this->getUsername();
		$option = isset($params['option']) ? $params['option'] : null;
		$tenantId = $this->getTenantId();
		try {
			$userInfoDAO = new UserInfoDAO($tenantId);
			$userInfo = $userInfoDAO->getUserInfoByUsername($username);
			if (is_array($userInfo)) {
				switch ($option) {
					case 1:
						$retVal = $this->requestOTPByEmail($username, true, $userInfo['tenant_id']);
						break;
					case 2 :
						$retVal = $this->requestOTPBySMS($username, true, $userInfo['tenant_id']);
						break;
					default:
						break;
				}
			}
			if (!$retVal) {
				$message = 'Can\'t not generate OTP Code. Please contact administration';
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
	 * Generate OTP and send it to Client via Email
	 *
	 * @param type $userName Username who request OTP
	 * @param type $isForce
	 * @param type $tenantId Tenant Id
	 */
	public function requestOTPByEmail($username, $isForce, $tenantId) {
		$retVal = false;
		try {
			$userInfoDAO = new UserInfoDAO($tenantId);
			$userInfo = $userInfoDAO->getUserInfoByUsername($username);
			if (is_array($userInfo)) {
				if ($userInfo['email'] != '') {
					if ($isForce || $userInfo['otp'] == '') {
						$otp = OTPCodeGenerator::generateOTP($tenantId);
						if ($otp != '') {
							try {
								// Save TO DB the OTP
								$isUpdateOtpOk = $userInfoDAO->updateOTP($username, $otp);
								// logger.info("Update OTP for user {} successfully {}", userName, isOk);
								if ($isUpdateOtpOk) {
                                    $modelMap = array(
										'{username}' => $username,
										'{OTP}' => $otp
									);
                                    $toList[] = $userInfo['email'];
									$mailUtil = Mail\MailUtils::getInstance();
                                    $mailUtil->sendEmail(Mail\EmailAction::REQUEST_OTP_PASSWORD, $modelMap, null, $toList, array(),
                                            $tenantId);
									$retVal = true;
								}
							} catch (\Exception $exc) {
								// logger.error("Error in sending email", e);
								throw new Exception\SystemException($exc);
							}
						} else {
							// logger.error("Error in generating OTP for user:" + userName);
							throw new Exception\SystemException('Error in generating OTP. Please contact administrator');
						}
					}
				} else {
					// logger.error("User {} has no email", userName);
					throw new Exception\SystemException('User ' . $username . ' has no email. Please contact administrator');
				}
			} else {
				// logger.error("User {} does not exist", userName);
				throw new Exception\SystemException('User ' . $username . ' does not exist. Please contact administrator');
			}
		} catch (\Exception $exc) {
      $this->getLogger()->error($exc->getMessage());
		}
		return $retVal;
	}

	/**
	 * Generate OTP and send it to Client via SMS
	 *
	 * @param type $username Username who request OTP
	 * @param type $isForce
	 * @param type $tenantId
	 * @return boolean
	 * @throws \SystemException
	 */
	public function requestOTPBySMS($username, $isForce, $tenantId) {
		$retVal = false;
		try {
			$userInfoDAO = new UserInfoDAO($tenantId);
			$userInfo = $userInfoDAO->getUserInfoByUsername($username);
			if (is_array($userInfo)) {
				if (($userInfo['email'] != '')) {
					if ($isForce || $userInfo['otp'] === '') {
						$otp = OTPCodeGenerator::generateOTP($tenantId);
						if ($otp != '') {
							try {
								// Save TO DB the OTP
								$isUpdateOtpOk = $userInfoDAO->updateOTP($username, $otp);
								// logger.info("Update OTP for user {} successfully {}", userName, isOk);
								if ($isUpdateOtpOk) {
                                    $modelMap = array(
										'{username}' => $username,
										'{OTP}' => $otp
									);
                                    $toList[] = $userInfo['phone_number'];
									$mailUtil = Mail\MailUtils::getInstance();
                                    $mailUtil->sendEmail(Mail\EmailAction::SMS_OTP_REQUEST, $modelMap, null, $toList, array(),
                                            $tenantId);
									$retVal = true;
								}
							} catch (\Exception $exc) {
								// logger.error("Error in sending email", e);
								throw new Exception\SystemException($exc);
							}
						} else {
							// logger.error("Error in generating OTP for user:" + userName);
							throw new Exception\SystemException('Error in generating OTP. Please contact administrator');
						}
					}
				} else {
					// logger.error("User {} has no email", userName);
					throw new Exception\SystemException('User ' . $username . ' has no email. Please contact administrator');
				}
			} else {
				// logger.error("User {} does not exist", userName);
				throw new Exception\SystemException('User ' . $username . ' does not exist. Please contact administrator');
			}
		} catch (\Exception $exc) {
      $this->getLogger()->error($exc->getMessage());
		}
		return $retVal;
	}

}
