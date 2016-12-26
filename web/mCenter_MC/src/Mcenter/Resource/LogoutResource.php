<?php

namespace Mcenter\Resource;

use Tonic;
use Mcenter\DAO\UserInfoDAO;
use Mcenter\Lib\MCBaseResource;

/**
 * Logout resource
 * @uri /user/logout
 * @author danhtn
 */
class LogoutResource extends MCBaseResource {

	/**
	 * Process logout request
	 * @method POST
	 * @return Array Return value
	 */
	public function logOut() {
		$retVal = false;
		$message = "";
		$request = $this->request;
		$params = $request->getParamsPost();
		$username = $this->getUsername();
		$tenantId = $this->getTenantId();
		$token = isset($params['token']) ? $params['token'] : null;
		try {
// validate token
			$isVerified = $this->verifyTokenForUser($username, $token, $tenantId);

			if ($isVerified) {
        $dao = new UserInfoDAO($tenantId);
				$isOk = $this->logoutUser($username, $tenantId);
				if ($isOk) {
					$retVal = true;
				} else {
					$message = "Logout error. Please contact Administration";
				}
			} else {
				$message = "Token does not match. Please try again";
			}
		} catch (\Exception $e) {
// TODO Auto-generated catch block
      $this->getLogger()->error($e->getMessage());
		}
    if (true === $retVal) {
      $this->unsetUserInfo();
      \Mcenter\Lib\MCSession::getInstance()->destroyAll();
    }
		$returnVal = $this->preReturn($retVal, $message);
		return new Tonic\Response(Tonic\Response::OK, json_encode($returnVal));
	}

	public function verifyTokenUser($username, $token, $tenantId) {
		$retVal = false;
		try {
			if (isset($username) && isset($token)) {
				$userInfoDao = new UserInfoDAO($tenantId);
				$userInfo = array(
					'user_name' => $username,
          'tenant_id' => $tenantId
				);
        $currentToken = $userInfoDao->getTokenByUserName($username);
        $authorizeToken = \Mcenter\Lib\MCSession::getInstance()->getSessionId();
        $tokenArray = json_decode($currentToken, true);
        $tokenExist = array_key_exists($authorizeToken, $tokenArray);
        if ($tokenExist && ($token === $tokenArray[$authorizeToken])) {
          $validToken = true;
        } else {
          $validToken = false;
        }
				$retVal = $userInfoDao->verifyToken($userInfo) && $validToken;
			}
		} catch (\Exception $e) {
      $this->getLogger()->error($e->getMessage());
		}
		return $retVal;
	}

	public function preReturn($success, $message) {
		return array(
			'message' => $message,
			'success' => $success ? "true" : "false"
		);
	}
  /**
   * Log out the user
   * @param type $username
   * @param type $tenantId
   * @return type
   */
  private function logoutUser($username, $tenantId)
  {
    $result = false;
    try {
      $userInfoDao = new UserInfoDAO($tenantId);
      $currentToken = $userInfoDao->getTokenByUserName($username);
      $tokenArray = json_decode($currentToken, true);
      $authorizeToken = \Mcenter\Lib\MCSession::getInstance()->getSessionId();
      if (array_key_exists($authorizeToken, $tokenArray)) {
        unset($tokenArray[$authorizeToken]);
      }
      if (empty($tokenArray)) {
        $status = '0';
      } else {
        $status = '1';
      }
      $newTokenString = json_encode($tokenArray);
      $result = $userInfoDao->logout($username, $newTokenString, $status);
    } catch (\Exception $exc) {
      $this->getLogger()->error($exc->getMessage());
    }

    return $result;
  }
}
