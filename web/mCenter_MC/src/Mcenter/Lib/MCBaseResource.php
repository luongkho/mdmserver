<?php
namespace Mcenter\Lib;

use Tonic;
use Mcenter\Lib\MCSession;
/**
 * Description of MCBaseResource
 *
 * @author hoanvd
 */
class MCBaseResource extends Tonic\Resource{
  protected function getUsername()
  {
    return MCSession::getInstance()->get('username');
  }
  protected function getTenantId()
  {
    return MCSession::getInstance()->get('tenantId');
  }
  protected function setUserInfo($username, $tenantId)
  {
    MCSession::getInstance()->set('username', $username);
    MCSession::getInstance()->set('tenantId', $tenantId);
  }
  protected  function getTotalTimeVerifyFail()
  {
    return MCSession::getInstance()->get('totalLoginFail');
  }
  protected function setTotalTimeVerifyFail($number)
  {
    MCSession::getInstance()->set('totalLoginFail', $number);
  }
  protected function getLogger() {
    return \Logger::getLogger('psapLogger');
  }
  protected function unsetUserInfo()
  {
    MCSession::getInstance()->destroy('username');
    MCSession::getInstance()->destroy('tenantId');
    MCSession::getInstance()->destroy('totalLoginFail');
  }
  /**
   * Verify token
   * @param type $username
   * @param type $token
   * @param type $tenantId
   * @return type
   */
  protected function verifyTokenForUser($username, $token, $tenantId) {
		$retVal = false;
		try {
			if (isset($username) && isset($token)) {
				$userInfoDao = new \Mcenter\DAO\UserInfoDAO($tenantId);
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
}
