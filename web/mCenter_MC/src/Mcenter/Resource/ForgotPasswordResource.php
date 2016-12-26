<?php

namespace Mcenter\Resource;

use Tonic;
use Mcenter\DAO\UserInfoDAO;
use Mcenter\Service\Mail\EmailAction;
use Mcenter\Service\Mail\MailUtils;
use Mcenter\Exception\SystemException;
use Mcenter\Util\RandomPasswordUtil;
use Mcenter\Lib\MCBaseResource;

/**
 * forgot password resource
 * @uri /user/retrivePassword
 * @author danhtn
 */
class ForgotPasswordResource extends MCBaseResource
{

  /**
   * Process retrieve password request
   * @method POST
   * @return Array Return value
   */
  public function forgotPassword()
  {
    $retVal = false;
    $message = "";
    $request = $this->request;
    $params = $request->getParamsPost();
    $username = isset($params['username']) ? $params['username'] : null;
    $tenantId = isset($params['tenantId']) ? $params['tenantId'] : null;
    try {
      $dao = new UserInfoDAO($tenantId);
      $userInfo = $dao->getUserInfoByUsername($username);
      if ($userInfo != null) {
        // String password = userInfo.getPassword();
        //send email, got password to user;

        $array = explode(":", $userInfo['password']);
        if (count($array) > 2) {
          $newPassword = $array[0] . ":" . $array[1] . ":" . RandomPasswordUtil::randomPassword();
          $updateInfo = array(
              'user_name' => $username,
              'value' => $newPassword
          );
          $isOk = $dao->updateUserByCol("password", $updateInfo);
          if ($isOk) {
            $retVal = true;
          } else {
            $message = "Can't not update password. DB  Error";
          }
          $retVal = $this->sendPassword($username, $tenantId);
        } else {
          $newPassword = $userInfo["password"] . ":" . RandomPasswordUtil::randomPassword();
          $updateInfo = array(
              'user_name' => $username,
              'value' => $newPassword
          );
          $isOk = $dao->updateUserByCol("password", $updateInfo);
          if ($isOk) {
            $retVal = true;
          } else {
            $message = "Can't not update password. DB  Error";
          }
          $retVal = $this->sendPassword($username, $tenantId);
        }
      } else {
        $message = "Username or tenantId is not correct.";
      }
    } catch (\Exception $e) {
      $message = "Error has occured. Please contact Administration";
      $this->getLogger()->error($e->getMessage());
    }

    $returnVal = $this->preReturn($retVal, $message);
    return new Tonic\Response(Tonic\Response::OK, json_encode($returnVal));
  }

  public function sendPassword($username, $tenantId) {
    $retVal = false;
    try {
      $userInfoDao = new UserInfoDAO($tenantId);
      $userInfo = $userInfoDao->getUserInfoByUsername($username);
      //if ($userInfo != null) {
      if ($userInfo["email"]) {
        try {
          $password = explode(":", $userInfo["password"]);
          $modelMap = array(
              '{username}' => $username,
              '{password}' => $password[2]
          );

          $toList = array($userInfo["email"]);

          $mailUtils = MailUtils::getInstance();
          $mailUtils->sendEmail(EmailAction::REQUEST_PASSWORD, $modelMap, NULL, $toList, array(), $tenantId);
          $retVal = true;
        } catch (\Exception $e) {
          // logger.error("Error in sending email", e);
          throw new SystemException($e);
        }
      } else {
        throw new SystemException("User '" . $username . " 'does not have email.");
      }
      //} else {
      // logger.error("User {} does not exist", userName);
      //throw new SystemException("User '" + $userName + " 'does not exist. Please contact administrator");
      //}
    } catch (\Exception $e) {
      $this->getLogger()->error($e->getMessage());
    }
    return $retVal;
  }

  public function preReturn($success, $message)
  {
    return array(
        'message' => $message,
        'success' => $success ? "true" : "false"
    );
  }

}
