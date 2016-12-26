<?php

namespace Mcenter\Resource;

use Tonic;
use Mcenter\DAO\UserInfoDAO;
use Mcenter\Util\RandomPasswordUtil;
use Mcenter\Lib\MCBaseResource;

/**
 * Update password resource
 *
 * @uri /user/updatePassword
 * @author danhtn
 */
class UpdatePasswordResource extends MCBaseResource
{

  const DIGIT_PATTERN = "/^((?=.*\\d).{0,200})/";
  const UPPERCASE_PATTERN = "/^((?=.*[A-Z]).{0,200})/";
  const LOWERCASE_PATTERN = "/^((?=.*[a-z]).{0,200})/";

  /**
   * Process update password request
   *
   * @method POST
   * @return Array Return value
   */
  public function updatePassword()
  {
    $retVal = false;
    $message = "";
    $request = $this->request;
    $params = $request->getParamsPost();
    $username = $this->getUsername();
    $token = isset($params['token']) ? $params['token'] : null;
    $tenantId = $this->getTenantId();
    $currentPassword = isset($params['currentPassword']) ? $params['currentPassword'] : null;
    $newPassword = isset($params['newPassword']) ? $params['newPassword'] : null;
    try {
      //validate token
      $isVerified = $this->verifyTokenForUser($username, $token, $tenantId);
      if ($isVerified) {
        //validate old password
        $dao = new UserInfoDAO($tenantId);
        $userInfo = $dao->getUserInfoByUsername($username);
        if ($userInfo != null) {
          $array = explode(":", $userInfo['password']);
          if (sha1($currentPassword . $array[1]) == $array[0]) {
            // validate new password
            $error = $this->validatePassword($newPassword);

            if (strcasecmp(trim($error), "") != 0) {
              $message = "New password is not valid." . $error;
            } else {
              // changepassword
              $randomPass = RandomPasswordUtil::randomPassword();
              $tmp = sha1($newPassword . $randomPass);
              $newPassword = $tmp . ":" . $randomPass;
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
            }
          } else {
            $message = "Current password is not correct.";
          }
        } else {
          $message = "Username or tenantId is not correct.";
        }
      } else {
        $message = "Token does not match. Please try again";
      }
    } catch (\Exception $e) {
      // TODO Auto-generated catch block
      $message = "Exeption has occured.Please try again or contact Administrator";
      $this->getLogger()->error($e->getMessage());
    }
    $returnVal = $this->preReturn($retVal, $message);
    return new Tonic\Response(Tonic\Response::OK, json_encode($returnVal));
  }

  /**
   * Check password is valid or not
   *
   * @param type $password Password need to check valid
   * @return string Error message
   */
  private function validatePassword($password)
  {
    $error = "";
    if (preg_match('/\s/', $password)) {
      $error .= " Password contain space.";
    }
    if (strlen($password) < 8) {
      $error .= " Password length is shorter than 8 characters.";
    }

    $passMatCount = 0;
    if (preg_match(self::DIGIT_PATTERN, $password)) {
      $passMatCount++;
    }
    if (preg_match(self::UPPERCASE_PATTERN, $password)) {
      $passMatCount++;
    }
    if (preg_match(self::LOWERCASE_PATTERN, $password)) {
      $passMatCount++;
    }

    if ($passMatCount < 2) {
      $error .= "Password must contain at least 2 of following : Uppercase, Lowercase, Numeric.";
    }

    return $error;
  }

  /**
   * Verify token user
   *
   * @param type $username Username need to verify
   * @param type $token Token to verify
   * @param type $tenantId Tenant Id
   * @return true on success, false on failure
   */
  public function verifyTokenUser($username, $token, $tenantId)
  {
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
  public function preReturn($success, $message)
  {
    return array(
        'message' => $message,
        'success' => $success ? "true" : "false"
    );
  }

}
