<?php

namespace Mcenter\Resource;

use Tonic;
use Mcenter\DAO\UserInfoDAO;
use Mcenter\Util\RandomPasswordUtil;
use Mcenter\Lib\MCBaseResource;

/**
 * User resource
 *
 *
 * @uri /user/addUser
 * @author hoanvd
 */
class AddUserResource extends MCBaseResource
{

    const PASSWORD_PATTERN = "/^((?=.*\\d)(?=.*[a-z])(?=.*[A-Z])(?=.*[@#$%]).{6,20})/";
    const DIGIT_PATTERN = "/^((?=.*\\d).{0,200})/";
    const UPPERCASE_PATTERN = "/^((?=.*[A-Z]).{0,200})/";
    const LOWERCASE_PATTERN = "/^((?=.*[a-z]).{0,200})/";
    const USERNAME_PATTERN = "/^^\\d*[a-zA-Z][a-zA-Z\\d]*$/";
    const PHONE_PATTERN = "/^\\d{3}-\\d{7}/";
    const EMAIL_PATTERN = "/^^[_A-Za-z0-9-\\+]+(\\.[_A-Za-z0-9-]+)*@[A-Za-z0-9-]+(\\.[A-Za-z0-9]+)*(\\.[A-Za-z]{2,})$/";

    /**
     * Add new user to Database
     *
     * @method POST
     * @return Tonic\Response
     */
    public function addUser()
    {
        $success = false;
        $message = "";
        $request = $this->request;
        $params = $request->getParamsPost();
        try {
            $tenantId = isset($params['tenantId']) ? $params['tenantId'] : null;
            $userInfoDAO = new UserInfoDAO($tenantId);
            $userName = isset($params['username']) ? $params['username'] : null;
            $status = isset($params['status']) ? $params['status'] : 0;
            $userInfo = $userInfoDAO->getUserInfoByUsername($userName);
            if (null == $userInfo) {
                $email = isset($params['email']) ? $params['email'] : null;
                $userInfo = $userInfoDAO->getUserInfoByEmail($email);
                if ($userInfo === null || $userInfo === false) {
                    $password = isset($params['password']) ? $params['password'] : null;
                    $phone = isset($params['phone']) ? $params['phone'] : null;
                    $message = $this->_validateUser($password, $userName, $phone, $email);
                    if (!$message) {
                        $firstName = isset($params['firstname']) ? $params['firstname'] : null;
                        $lastName = isset($params['lastname']) ? $params['lastname'] : null;
                        $randomPass = RandomPasswordUtil::randomPassword();
                        $tmp = sha1($password . $randomPass);
                        $password = $tmp . ":" . $randomPass;
                        $newUser = array(
                            'email' => $email,
                            'user_name' => $userName,
                            'password' => $password,
                            'first_name' => $firstName,
                            'last_name' => $lastName,
                            'tenant_id' => $tenantId,
                            'phone_number' => $phone,
                            'role_id' => 1,
                            'status' => $status,
                        );

                        $insertResult = $userInfoDAO->insertUserInfo($newUser);
                        if ($insertResult) {
                            $success = true;
                        } else {
                            $message = "Save Userinfo error.";
                        }
                    }
                } else {
                    $message = "Email has been used.Please change and try again.";
                }
            } else {
                $message = "Email has been used.Please change and try again.";
            }
        } catch (\Exception $exc) {
            $message = "Error has occured. Please contact Administration";
            $this->getLogger()->error($exc->getMessage());
        }

        $result = array(
            "message" => $message,
            "success" => $success ? "true" : "false"
        );
        return new Tonic\Response(Tonic\Response::OK, json_encode($result));
    }

    /**
     * Check user info is valid or not
     *
     * @param string $password Password need to check valid
     * @param string $userName Username need to check valid
     * @param string $phone Phone number need to check valid
     * @param string $email Email need to check valid
     */
    private function _validateUser($password, $userName, $phone, $email)
    {
        $error = '';
        if (!preg_match(self::EMAIL_PATTERN, $email)) {
            $error .= "Email is not right.";
        }
        if (preg_match('/\s/', $userName)) {
            $error .= " Username contain space.";
        }

        if (strlen($userName) < 6) {
            $error .= " Username length is shorter than 6 characters.";
        }
        if (!preg_match(self::USERNAME_PATTERN, $userName)) {
            $error .= " Username is not valid. Contain character and numberic only.";
        }

        $error .= $this->_validatePassword($password);

        return $error;
    }

    /**
     * Validate password
     *
     * @param string $password Password to validate
     */
    private function _validatePassword($password)
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

}
