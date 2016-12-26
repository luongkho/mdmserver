<?php

namespace Mcenter\Resource;

use Tonic;
use Mcenter\DAO\UserInfoDAO;
use Mcenter\Lib\MCBaseResource;
/**
 * List user resource
 *
 *
 * @uri /user/getListUsers
 * @author hoanvd
 */
class ListUserResource extends MCBaseResource {

	/**
	 * Get list user (Admin role)
	 *
	 * @method POST
	 * @provides application/json
	 * @json
	 * @return Tonic\Response
	 */
	public function getListUser() {
		// return value
		$retVal = false;
		$message = '';
		//get params
		$request = $this->request;
		$params = $request->getParamsPost();
		$userName = $this->getUsername();
		$tenantId = $this->getTenantId();
		$token = isset($params['token']) ? $params['token'] : null;
		// local variable
		$userInfoList = array();
		$users = array();
		try {
			$isVerified = $this->verifyTokenForUser($userName, $token, $tenantId);
			if ($isVerified) {
				$userInfoDAO = new UserInfoDAO($tenantId);
				$userInfoList = $userInfoDAO->getListUserInfo($userName);
				if (is_array($userInfoList)) {
					try {
						$userIndex = 0;
						foreach ($userInfoList as $userInfo) {
							$users[$userIndex]['email'] = $userInfo['email'];
							$users[$userIndex]['firstName'] = $userInfo['first_name'];
							$users[$userIndex]['lastName'] = $userInfo['last_name'];
							$users[$userIndex]['phoneNumber'] = $userInfo['phone_number'];
							$users[$userIndex]['roleId'] = $userInfo['role_id'];
							$users[$userIndex]['status'] = ($userInfo['status']!=null)?$userInfo['status']:"0";
							$users[$userIndex]['tenantId'] = $userInfo['tenant_id'];
							$users[$userIndex]['userName'] = $userInfo['user_name'];
							$userIndex++;
						}
						$retVal = true;
					} catch (\Exception $exc) {
						$message = 'Get userInforList error. Please try again or contact administration';
						$this->getLogger()->error($exc->getMessage());
					}
				} else {
					$message = 'User List is null.';
				}
			} else {
				$message = 'Token does not match. Please try again';
			}
		} catch (\Exception $exc) {
			$retVal = false;
			$message = 'Error has occur. Please try again or contact administration';
      $this->getLogger()->error($exc->getMessage());
		}
		$result = array(
				'message' => $message,
				'success' => $retVal ? "true" : "false",
				'users' => $users
		);
		return new Tonic\Response(Tonic\Response::OK, json_encode($result));
	}

	/**
	 * Verify token user
	 *
	 * @param type $userName the user need to verify
	 * @param type $token the token to verify
	 * @param type $tenantId Tenant Id
	 * @return boolean true if verify token success, otherwise false
	 */
	public function verifyTokenUser($userName, $token, $tenantId) {
		$retVal = false;
		try {
			if ($userName != '' && $token != '') {
				$userInfoDAO = new UserInfoDAO($tenantId);
				$userInfo = array(
						'user_name' => $userName,
						'token' => $token,
            'tenant_id' => $tenantId
				);
				$retVal = $userInfoDAO->verifyToken($userInfo);
			}
		} catch (\Exception $exc) {
      $this->getLogger()->error($exc->getMessage());
		}
		return $retVal;
	}
}