<?php

namespace Mcenter\Resource;

use Tonic;
use Mcenter\DAO\UserInfoDAO;
use Mcenter\Lib\MCBaseResource;
/**
 * Update email resource
 * @uri /user/updateEmail
 * @author danhtn
 */
class UpdateEmailResource extends MCBaseResource {

	const EMAIL_PATTERN = "/^^[_A-Za-z0-9-\\+]+(\\.[_A-Za-z0-9-]+)*@[A-Za-z0-9-]+(\\.[A-Za-z0-9]+)*(\\.[A-Za-z]{2,})$/";

	/**
	 * Process update email request
	 * @method POST
	 * @return Array Return value
	 */
	public function updateEmail() {
		$retVal = false;
		$message = "";
		$request = $this->request;
		$params = $request->getParamsPost();
		$username = $this->getUsername();
		$token = isset($params['token']) ? $params['token'] : null;
		$tenantId = $this->getTenantId();
		$newEmail = isset($params['newEmail']) ? $params['newEmail'] : null;
		if ($newEmail != null) {
			if (isset($newEmail) && preg_match(self::EMAIL_PATTERN, $newEmail)) {
				try {
					//validate token
					$isVerified = $this->verifyTokenForUser($username, $token, $tenantId);
					$dao = new UserInfoDAO($tenantId);
					$userInfo = $dao->getUserInfoByEmail($newEmail);
					if ($userInfo === null || $userInfo === false) {
						if ($isVerified) {

							// changeEmail
							$updateInfo = array(
								'user_name' => $username,
								'value' => $newEmail
							);
							$isOk = $dao->updateUserByCol("email", $updateInfo);
							if ($isOk) {
								$retVal = true;
							} else {
								$message = "Can't not update email. DB  Error";
							}
						} else {
							$message = "Token does not match. Please try again";
						}
					} else {
						$message = "Email is used. Please change email.";
					}
				} catch (\Exception $e) {
					// TODO Auto-generated catch block
					$message = "Exeption has occured.Please try again or contact Administrator";
          $this->getLogger()->error($e->getMessage());
				}
			} else {
				$message = "New email is not valid";
			}
		} else {
			$message = "New email  is null";
		}
		$returnVal = $this->preReturn($retVal, $message);
		return new Tonic\Response(Tonic\Response::OK, json_encode($returnVal));
	}

	/**
	 * Verify token user
	 *
	 * @param type $username Username need to verify
	 * @param type $token Token to verify
	 * @param type $tenantId Tenant Id
	 * @return true on success, false on failure
	 */
	public function verifyTokenUser($username, $token, $tenantId) {
		$retVal = false;
    $this->getLogger()->debug('Token:' . $token);
		try {
			if (isset($username) && isset($token)) {
				$userInfoDao = new UserInfoDAO($tenantId);
				$userInfo = array(
					'user_name' => $username,
					'token' => $token,
          'tenant_id' => $tenantId
				);
				$retVal = $userInfoDao->verifyToken($userInfo);
			}
		} catch (\Exception $e) {
      $this->getLogger()->error($e->getMessage());
		}
		return $retVal;
	}

	/**
	 * Prepare return value
	 *
	 * @param type $success Status of respond
	 * @param type $message Messageto Client
	 * @return array
	 */
	public function preReturn($success, $message) {
		return array(
			'message' => $message,
			'success' => $success ? "true" : "false"
		);
	}

}
