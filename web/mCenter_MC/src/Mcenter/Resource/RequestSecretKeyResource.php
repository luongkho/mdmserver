<?php

namespace Mcenter\Resource;

use Tonic;
use Mcenter\DAO\UserInfoDAO;
use Mcenter\Lib\GoogleAuthenticator;
use Mcenter\Service\Mail\EmailAction;
use Mcenter\Service\Mail\MailUtils;
use Mcenter\Exception\SystemException;
use Mcenter\Exception\NotSupportException;
use Mcenter\Exception\InvalidParamException;
use Mcenter\Util\Constants;
use Tonic\Application;
use Tonic\Request;
use Logger;
use Mcenter\Lib\MCBaseResource;

/**
 * Description of RequestSecretKeyResource
 * RequestSecretKey Resource
 * @uri /user/requestSecretKey
 * @author danhtn
 */
class RequestSecretKeyResource extends MCBaseResource {

    private static $logger;

    /**
     * Send secret token by email or sms
     */

    const
		EMAIL_REQUEST = 1,
		SMS_REQUEST = 2;

    public function __construct(Application $app, Request $request)
    {
        parent::__construct($app, $request);
        if (!(self::$logger instanceof Logger)) {
            self::$logger = Logger::getLogger(__CLASS__);
        }
    }

    /**
	 * @method POST
     * @provides application/json
	 * @return Tonic\Response
	 */
	public function requestSecretKey() {
        $retVal = false;
        $message = "";
        $request = $this->request;
        $params = $request->getParamsPost();
        $username = $this->getUsername();
        $tenantId = $this->getTenantId();
        $sendMethod = isset($params['sendViaMethod']) ? $params['sendViaMethod'] : null;

        try {
            $dao = new UserInfoDAO($tenantId);
            $userInfo = $dao->getUserInfoByUsername($username);
            if ($userInfo != null) {
                $retVal = $this->generateAndSendSecretKey($username, $userInfo["tenant_id"], $sendMethod);
                if (!$retVal) {
                    $message = "Can't not generate secret key. Please contact administration";
                }
            } else {
                $message = "User not found";
            }
        } catch (InvalidParamException $e) {
            $message = $e->getMessage();
        } catch (NotSupportException $e) {
            $message = $e->getMessage();
        } catch (\Exception $e) {
            $message = "Error has occured. Please contact Administration";
        }

        return json_encode(array(
			'message' => $message,
			'success' => $retVal ? "true" : "false"
		));
    }

	/**
     * Generate secret key and send secret key to Client via email or SMS
     *
     * @param username Current user is logged in
     * @param isForce
     * @param tenantId Tenant Id of user
     * @return boolean True on success or false on failure
     * @throws SystemException
     */
    public function generateAndSendSecretKey($username, $tenantId, $sendMethod)
    {
        $retVal = false;
        $userInfoDao = new UserInfoDAO($tenantId);
        $userInfo = $userInfoDao->getUserInfoByUsername($username);

        if ($userInfo != null) {
            if (isset($userInfo["email"])) {
                // generate secret key
                $googleAuthenticator = new GoogleAuthenticator();
                $secretKey = $googleAuthenticator->createSecret();
                // create auth URL
                $otpAuthURL = $googleAuthenticator->getQRCodeGoogleUrl(rawurlencode('iWebgate Device Connect LP'), $secretKey);
                if ($secretKey != '') {
                    // update secret key for user
                    $isUpdateSecretOk = $userInfoDao->updateSecretKey($username, $secretKey);
                    if ($isUpdateSecretOk) {
                        // send secret key to client via Email or SMS depend on method that user choose
                        // Default is mail
                        switch ($sendMethod) {
                            case self::EMAIL_REQUEST:
                                $this->sendSecretKeyViaEmail($secretKey, $otpAuthURL, $username, $tenantId, $userInfo);
                                break;
                            case self::SMS_REQUEST:
                                $this->sendSecretKeyViaSMS($secretKey, $otpAuthURL, $username, $tenantId, $userInfo);
                                break;
                            default:
                                throw new InvalidParamException("Invalid sendViaMethod param.");
                                break;
                        }
                        $retVal = true;
                    }
                } else {
                    throw new SystemException("Error in generating secret key. Please contact administrator");
                }
            } else {
                throw new SystemException("User '" + $username + " 'has no email. Please contact administrator");
            }
        } else {
            throw new SystemException("User '" + $username + " 'does not exist. Please contact administrator");
        }
        return $retVal;
    }
	/**
	 * Send secret key via Email
	 *
	 * @param secretKey
	 * @param authURL
	 * @param username
	 * @param tenantId
	 * @param userInfo
	 * @return
	 */
	public function sendSecretKeyViaEmail($secretKey, $authURL, $username, $tenantId, $userInfo) {
		try {
			$modelMap = array(
                '${userName}' => $username,
                '${systemName}' => Constants::$SYSTEM_NAME,
                '${authURL}' => $authURL,
                '${secretKey}' => $secretKey
            );
            $toList = array($userInfo["email"]);
			$mailUtils = MailUtils::getInstance();
			$success = $mailUtils->sendEmail(EmailAction::REQUEST_TOTP_SECRET_TOKEN, $modelMap, NULL, $toList, array(), $tenantId);
            if (!$success) {
                self::$logger->error("Error when try send secret key through email. User: ".$username);
            }
		} catch (\Exception $e) {
            self::$logger->error("Error when try send secret key through email. User: ".$username);
			throw new SystemException($e);
		}
    }
    /**
	 * Send secret key via SMS
	 *
	 * @param secretKey
	 * @param authURL
	 * @param username
	 * @param tenantId
	 * @param userInfo
	 * @return
	 */
	public function sendSecretKeyViaSMS($secretKey, $authURL, $username, $tenantId, $userInfo) {
		try {
			$modelMap = array(
                '${userName}' => $username,
                '${systemName}' => Constants::$SYSTEM_NAME,
                '${authURL}' => $authURL,
                '${secretKey}' => $secretKey
            );
            $toList = array($userInfo["phone_number"]);
			$mailUtils = MailUtils::getInstance();
			$success = $mailUtils->sendEmail(EmailAction::REQUEST_TOTP_SECRET_TOKEN, $modelMap, NULL, $toList, array(), $tenantId);
            if (!$success) {
                self::$logger->error("Error when try send secret key through email. User: ".$username);
            }
		} catch (\Exception $e) {
            self::$logger->error("Error when try send secret key through email. User: ".$username);
			throw new SystemException($e);
		}
    }

}
