<?php

namespace Mcenter\Resource;

use Tonic;
use Mcenter\DAO\UserInfoDAO;
use Mcenter\Lib\MCBaseResource;
/**
 * Update phone number resource
 * @uri /user/updatePhoneNumber
 * @author danhtn
 */
class UpdatePhoneResource extends MCBaseResource {

	const PHONE_PATTERN = "/^\\d{3}-\\d{7}/";

	/**
	 * Process update phone number request
	 * @method POST
	 * @return Array Return value
	 */
	public function updatePhoneNumber() {
		$retVal = false;
		$message = "";
		$request = $this->request;
		$params = $request->getParamsPost();
		$username = $this->getUsername();
		$token = isset($params['token']) ? $params['token'] : null;
		$tenantId = $this->getTenantId();
		$newPhoneNumber = isset($params['newPhoneNumber']) ? $params['newPhoneNumber'] : null;

		if ($newPhoneNumber != null) {
//      if (preg_match(self::PHONE_PATTERN, $newPhoneNumber)) {
			try {
				// validate token
				$isVerified = $this->verifyTokenForUser($username, $token, $tenantId);
				if ($isVerified) {
					$dao = new UserInfoDAO($tenantId);
					// changePhoneNumber
					$updateInfo = array(
						'user_name' => $username,
						'value' => $newPhoneNumber
					);
					$isOk = $dao->updateUserByCol("phone_number", $updateInfo);
					if ($isOk) {
						$retVal = true;
					} else {
						$message = "Can't not update phoneNumber.DB  Error";
					}
				} else {
					$message = "Token does not match. Please try again";
				}
			} catch (\Exception $e) {
				// TODO Auto-generated catch block
				$message = "Exeption has occured.Please try again or contact Administrator";
        $this->getLogger()->error($e->getMessage());
			}
//      } else {
//        $message = "PhoneNumber is not right format";
//      }
		} else {
			$message = "PhoneNumber is null";
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
