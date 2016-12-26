<?php

namespace Mcenter\Resource;

use Tonic;
use Mcenter\DAO\UserInfoDAO;
use Mcenter\Lib\MCBaseResource;
use Mcenter\Config\MCenterConfig;

/**
 * Login resource
 * @uri /user/login
 * @author danhtn
 */
class LoginResource extends MCBaseResource {

  /**
   * Process login request
   * @method POST
   * @return Array Return value
   */
  public function getUserInfo() {
    $retVal = false;
    $message = "";
    $savePass = "";
    $request = $this->request;
    $params = $request->getParamsPost();
    $username = isset($params['username']) ? $params['username'] : null;
    $password = isset($params['password']) ? $params['password'] : null;
    $tenantId = isset($params['tenantId']) ? $params['tenantId'] : null;
    $clientVersion = isset($params['clientVersion']) ? $params['clientVersion'] : null;
    $platform = isset($params['platform']) ? $params['platform'] : null;
    try {
      $platformVersionConfig = MCenterConfig::getPlatformVersion($platform);
      if (!is_array($platformVersionConfig)) {
        $messageLog = 'Your platform is not supported. We support platforms such as Android, iOS, OSX, WP, Windows.';
        $message = "Your app version is not compatible. Please upgrade your version.";
        $this->getLogger()->info($messageLog);
      } else {
        $currentclientVersion = $platformVersionConfig['config_val'];
        if (version_compare($currentclientVersion, $clientVersion) <= 0) {
          $dao = new UserInfoDAO($tenantId);
          $userInfo = $dao->getUserInfoByUsername($username);
          if ($userInfo != null) {
            $array = explode(":", $userInfo['password']);
            if (count($array) > 2) {
              if (sha1($password . $array[1]) == $array[0]) {
                $savePass = $array[0] . ":" . $array[1];
                $updateInfo = array(
                    'user_name' => $username,
                    'value' => $savePass
                );
                $isOk = $dao->updateUserByCol("password", $updateInfo);
                if ($isOk) {
                  $retVal = true;
                } else {
                  $message = "Can't not update password. DB  Error";
                }
              } else {
                if ($password == $array[2]) {
                  $savePass = sha1($array[2] . $array[1]) . ":" . $array[1];
                  $updateInfo = array(
                      'user_name' => $username,
                      'value' => $savePass
                  );
                  $isOk = $dao->updateUserByCol("password", $updateInfo);
                  if ($isOk) {
                    $retVal = true;
                  } else {
                    $message = "Can't not update password. DB  Error";
                  }
                } else {
                  $message = "Please check your credentials and try again. If you are still unable to login on, contact your administrator.";
                }
              }
            } else {
              if (sha1($password . $array[1]) == $array[0]) {
                $retVal = true;
              } else {
                $message = "Please check your credentials and try again. If you are still unable to login on, contact your administrator.";
              }
            }
          } else {
            $message = "Please check your credentials and try again. If you are still unable to login on, contact your administrator.";
          }
        } else {
          $message = "Your app version does not compatible. Please upgrade your version.";
        }
      }
    } catch (\Exception $e) {
      $message = "Error has occured. Please contact Administration";
      $this->getLogger()->error($e->getMessage());
    }
    // Set session in case login successfully
    if (true === $retVal) {
      $this->setUserInfo($username, $tenantId);
      $this->setTotalTimeVerifyFail(0);
    }
    $returnVal = $this->preReturn($retVal, $message);
    return new Tonic\Response(Tonic\Response::OK, json_encode($returnVal));
  }

  public function preReturn($success, $message) {
    return array(
        'message' => $message,
        'success' => $success ? "true" : "false",
        'token' => $success ? \Mcenter\Lib\MCSession::getInstance()->getSessionId() : NULL
    );
  }

}
