<?php
namespace Mcenter\Service;

use Mcenter\DAO\OTPConfigurationDAO;
use \Logger;
use Mcenter\Exception;
/**
 * Description of CommonService
 *
 * @author hoanvd
 */
class CommonService {
	private static $logger;
	public function getUserInfo($userName, $passwordl, $tenantId) {}
	public function getApplicationGroups($roleId, $tenantId) {}
	public function __construct() {
		if (!(CommonService::$logger instanceof Logger)) {
			CommonService::$logger = Logger::getLogger(__CLASS__);
		}
	}
	/**
     * Request new OTP code
	 *
     * @param userName user name whom is logged
     * @param isForce if isForce is false, only generate new OTP code when OTP code is null and always generate new OTP code when isForce is true
     * @throws SystemException
     */
	public function requestOTP($userName, $isForce, $tenantId) {}
	/**
	 *
	 * @param int $roleId
	 * @param int $groupId
	 * @param string $tenantId
	 * @return Array
	 */
	public function getApplications($roleId, $groupId, $tenantId) {}
	/**
	 * Get an OTP Configuration by Tenant Id
	 * @param string $tenantId
	 */
	public function getOTPConfig($tenantId) {
    $otpConfigDao = new OTPConfigurationDAO($tenantId);
    return $otpConfigDao->getOTPConfig($tenantId);
  }
	/**
	 * Save OTP Configuration
	 *
	 * @param int $OTPLength
	 * @param int $timeExpire
	 * @param string $OTPType
	 * @param string $tenantId
	 * @return bool True on success or False on failure
	 */
	public function saveOTPConfig($OTPLength, $timeExpire, $OTPType, $tenantId) {
		try{
    		$otpConfigDao = new OTPConfigurationDAO($tenantId);
    		return $otpConfigDao->saveOTPConfig($OTPLength, $timeExpire, $OTPType);
    	}catch(Exception\DatabaseException $exc){
    		CommonService::$logger->error("Error when save OTP config.", $exc);
    		throw $exc;
    	}
	}
}
