<?php

namespace Mcenter\DAO;

use Mcenter\DAO\TenantSupportDAO;
use Logger;
use Mcenter\Exception\DatabaseException;
use Mcenter\Util\Constants;
use DateTime;

/**
 * Description of UserInfoDAO
 *
 * @author hoanvd
 */
class UserInfoDAO extends TenantSupportDAO
{

    private static $logger;

    public function __construct($_tenantId)
    {
        parent::__construct($_tenantId);
        if (!(UserInfoDAO::$logger instanceof Logger)) {
            UserInfoDAO::$logger = Logger::getLogger(__CLASS__);
        }
    }

    /**
     * @author danhtn
     * Get user info when user login
     * @param type $userName Username login
     * @param type $password Password login
     * @throws DatabaseException
     * @return Array An user match with Username login and Password login
     */
    public function getUserInfo($userName, $password)
    {
        $user = null;
        try {
            $dbUtil = \Mcenter\Util\DatabaseUtil::getInstance();
            $query = "SELECT * FROM user_info WHERE user_name = :userName AND password = :password AND tenant_id = :tenantId";
            $user = $dbUtil->fetchOne($query, array(
                ':userName' => $userName,
                ':password' => $password,
                ':tenantId' => $this->tenantId,
            ));
        } catch (\Exception $exc) {
            UserInfoDAO::$logger->error("Error when get user info", $exc);
            throw new DatabaseException("Error when get user info");
        }
        return $user;
    }

    /**
     * Delete an user
     *
     * @param Array $user User need to delete
     * @throws DatabaseException
     * @return bool True on success or False on failure
     */
    public function deleteUser($user)
    {

    }

    /**
     * Update an user
     *
     * @param Array $user An user need to update
     * @throws DatabaseException
     * @return bool True on success or False on failure
     */
    public function updateUserInfo($user)
    {

    }

    /**
     * Insert an user to Database
     *
     * @param Array $user User info to insert
     * @throws DatabaseException
     * @return bool True on success or False on failure
     */
    public function insertUserInfo($user)
    {
        try {
            $dbUtil = \Mcenter\Util\DatabaseUtil::getInstance();
            $query = "INSERT INTO user_info(user_name, password, first_name, last_name, email, role_id, tenant_id, phone_number, status)
            VALUES (:user_name, :password, :first_name, :last_name,:email, :role_id, :tenant_id, :phone_number,:status);";
            $params = array();
            foreach ($user as $key => $value) {
                $params[":$key"] = $value;
            }
            return $dbUtil->executeQuery($query, $params);
        } catch (\Exception $exc) {
            UserInfoDAO::$logger->error("Error when get user info", $exc);
            throw new DatabaseException("Error when get user info");
        }
    }

    /**
     * Get user by username
     *
     * @param string $userName Username
     * @throws DatabaseException
     * @return Array user info
     */
    public function getUserInfoByUsername($userName)
    {
        $userInfo = null;
        try {
            $dbUtil = \Mcenter\Util\DatabaseUtil::getInstance();
            $query = "SELECT * FROM user_info WHERE user_name = :userName AND tenant_id = :tenantId";
            $userInfo = $dbUtil->fetchOne($query, array(
                ':userName' => $userName,
                ':tenantId' => $this->tenantId
            ));
        } catch (\Exception $exc) {
            UserInfoDAO::$logger->error("Error when get user info", $exc);
            throw new DatabaseException("Error when get user info");
        }
        return $userInfo;
    }

    /**
     * Get user by email
     *
     * @param string $userName Username
     * @throws DatabaseException
     * @return Array user info
     */
    public function getUserInfoByEmail($email)
    {
        $userInfo = null;
        try {
            $dbUtil = \Mcenter\Util\DatabaseUtil::getInstance();
            $query = "SELECT * FROM user_info WHERE email = :email AND tenant_id = :tenantId";
            $userInfo = $dbUtil->fetchOne($query, array(
                ':email' => $email,
                ':tenantId' => $this->tenantId
            ));
        } catch (\Exception $exc) {
            UserInfoDAO::$logger->error("Error when get user info", $exc);
            throw new DatabaseException("Error when get user info");
        }
        return $userInfo;
    }

    /**
     * Update OTP code of user
     *
     * @author hoanvd
     * @param type $userName Username need to update OTP code
     * @param type $otp OTP OTP code to update
     * @throws DatabaseException
     * @return bool True on success or False on failure
     */
    public function updateOTP($userName, $otp)
    {
        $retVal = false;
        try {
            $dbUtil = \Mcenter\Util\DatabaseUtil::getInstance();
            $query = "UPDATE user_info SET otp = :otp, otp_upd_dt = :otpUpdateDate WHERE user_name = :userName";
            $params = array(
                ':otp' => $otp,
                ':otpUpdateDate' => date(Constants::$DATE_TIME_FORMAT),
                ':userName' => $userName
            );
            $retVal = $dbUtil->executeQuery($query, $params);
        } catch (\Exception $exc) {
            // logger.error("Error when update OTP code", e);
            throw new DatabaseException("Error when update OTP code");
        }
        return $retVal;
    }

    /**
     * Verify OTP code
     *
     * @author hoanvd
     * @param string $userName Username need to verify
     * @param string $otp OTP code to verify
     * @throws DatabaseException
     * @return int 1 true; 0 : not match ; -1 : expired
     */
    public function verifyOTP($userName, $otp)
    {
        try {
            $dbUtil = \Mcenter\Util\DatabaseUtil::getInstance();
            $query = "SELECT u.otp_upd_dt, oc.time_expire FROM user_info u, otp_configuration oc"
                    . " WHERE u.user_name = :userName AND CAST(u.tenant_id AS text) = CAST(oc.tenant_id AS text) AND u.otp = :otp";
            $result = $dbUtil->fetchOne($query, array(
                ':userName' => $userName,
                ':otp' => $otp
            ));
            if (is_array($result)) {
                $latestUpdateTimeStamp = DateTime::createFromFormat(Constants::$DATE_TIME_FORMAT, $result['otp_upd_dt'])->getTimestamp();
                $currDate = new DateTime();
                $currentDateTimeStamp = $currDate->getTimestamp();
                $timeExpire = intval($result['time_expire']);
                // if time expire = 0, It's mean OTP code never expire
                if ($timeExpire > 0) {
                    $diffTime = $currentDateTimeStamp - $latestUpdateTimeStamp;
                    // $diffTime is seconds
                    if ($diffTime <= $timeExpire) {
                        $retVal = 1;
                    } else {
                        $retVal = -1;
                    }
                } else {
                    $retVal = 1;
                }
            } else {
                // result is null . OTP and username does not match.
                $retVal = 0;
            }
        } catch (Exception $exc) {
            throw new DatabaseException("Error when verify OTP code");
        }
        return $retVal;
    }

    /**
     * Get list of user
     *
     * @throws DatabaseException
     * @return Array User list
     */
    public function getListUser()
    {

    }

    /**
     * Update token of User
     *
     * @author hoanvd
     * @param string $userName USername
     * @param string $token Token
     * @throws DatabaseException
     * @return bool True on success or False on failure
     *
     */
    public function updateToken($userName, $token)
    {
        $retVal = false;
        try {
            $dbUtil = \Mcenter\Util\DatabaseUtil::getInstance();
            $query = "UPDATE user_info SET token = :token WHERE user_name = :userName";
            $retVal = $dbUtil->executeQuery($query, array(
                ':token' => $token,
                ':userName' => $userName
            ));
        } catch (Exception $exc) {
            // logger.error("Error when update OTP code", e);
            throw new DatabaseException("Error when update token code");
        }
        return $retVal;
    }
    /**
     * Get current token of user
     * @param type $userName
     * @return type
     * @throws DatabaseException
     */
    public function getTokenByUserName($userName)
    {
      try {
          $dbUtil = \Mcenter\Util\DatabaseUtil::getInstance();
          $query = "SELECT token FROM user_info WHERE user_name = :userName";
          $token = $dbUtil->fetchOne($query, array(':userName' => $userName));
          return $token['token'];
      } catch (\Exception $exc) {
          ConfigurationDAO::$logger->error($exc->getMessage());
          throw new DatabaseException("Error when get configuration");
      }
    }

    /**
     * Update secret key for User
     *
     * @author hoanvd
     * @param string $userName USername
     * @param string $secretKey secret key
     * @throws DatabaseException
     * @return bool True on success or False on failure
     *
     */
    public function updateSecretKey($userName, $secretKey)
    {
        $retVal = false;
        try {
            $dbUtil = \Mcenter\Util\DatabaseUtil::getInstance();
            $query = "UPDATE user_info SET secret = :secret WHERE user_name = :userName";
            $retVal = $dbUtil->executeQuery($query, array(
                ':secret' => $secretKey,
                ':userName' => $userName
            ));
        } catch (Exception $exc) {
            // logger.error("Error when update OTP code", e);
            throw new DatabaseException("Error when update token code");
        }
        return $retVal;
    }

    /**
     * @author danhtn
     * Logout the current user
     *
     * @param string $userName Username
     * @param string $token New token
     * @param string $status New status
     * @throws DatabaseException
     * @return bool True on success or False on failure
     */
    public function logout($username, $token, $status)
    {
        $retVal = false;
        try {
            $dbUtil = \Mcenter\Util\DatabaseUtil::getInstance();
            $query = "UPDATE user_info SET token = :token , status = :status WHERE user_name = :userName";
            $retVal = $dbUtil->executeQuery($query, array(
                ':token' => $token,
                ':status' => $status,
                ':userName' => $username
            ));
        } catch (\Exception $exc) {
            throw new DatabaseException("Error when update token code");
        }
        return $retVal;
    }

    /**
     * Update status of user
     *
     * @param string $userName Username
     * @param string $status Status
     * @throws DatabaseException
     * @return bool True on success or False on failure
     */
    public function updateUserStatus($userName, $status)
    {
        $retVal = false;
        try {
            $dbUtil = \Mcenter\Util\DatabaseUtil::getInstance();
            $query = ("UPDATE user_info SET status = :status where user_name = :userName");
            $retVal = $dbUtil->executeQuery($query, array(
                ':status' => $status,
                ':userName' => $userName
            ));
        } catch (Exception $exc) {
            throw new DatabaseException("Error when update status", $exc);
        }
        return $retVal;
    }

    /**
     * @author danhtn
     * Update users by column
     * @param string $column Column to update
     * @param string $value Value to update
     * @throws DatabaseException
     * @return bool True on success or False on failure
     */
    public function updateUserByCol($column, $user)
    {

        $retVal = false;
        try {
            $dbUtil = \Mcenter\Util\DatabaseUtil::getInstance();
            $query = "UPDATE user_info SET " . $column . " = :value where user_name = :user_name";
            $params = array();
            foreach ($user as $key => $value) {
                $params[":$key"] = $value;
            }
            $retVal = $dbUtil->executeQuery($query, $params);
        } catch (\Exception $e) {
            throw new DatabaseException("Error when update status", $e);
        }
        return $retVal;
    }

    /**
     * @author danhtn
     * Verify user token
     * @param string $userName Username
     * @param string $token Token to verify
     * @throws DatabaseException
     * @return bool True on success or False on failure
     */
    public function verifyToken($user)
    {
        $retVal = false;
        try {
            $dbUtil = \Mcenter\Util\DatabaseUtil::getInstance();
            $query = "Select u.otp_upd_dt,u.password From user_info u where u.user_name = :user_name  and u.tenant_id = :tenant_id";
            $params = array();
            foreach ($user as $key => $value) {
                $params[":$key"] = $value;
            }
            $result = $dbUtil->fetchOne($query, $params);
            if ($result != null) {
                $retVal = true;
            }
        } catch (\Exception $e) {
//          logger.error("Error when verify OTP code", e);
            throw new DatabaseException("Error when verify token code", $e);
        }
        return $retVal;
    }

    /**
     * Get List user
     *
     * @param string $userName Username
     * @throws DatabaseException
     * @return Array User list
     */
    public function getListUserInfo($userName)
    {
        $userList = null;
        try {
            $dbUtil = \Mcenter\Util\DatabaseUtil::getInstance();
            $query = "SELECT * FROM user_info WHERE user_name != :userName";
            $userList = $dbUtil->fetchAll($query, array(':userName' => $userName));
        } catch (\Exception $exc) {
            UserInfoDAO::$logger->error("Error when get list user info", $exc);
            throw new DatabaseException("Error when get list user info");
        }
        return $userList;
    }

}
