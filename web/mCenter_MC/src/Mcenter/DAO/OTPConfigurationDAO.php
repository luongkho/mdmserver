<?php

namespace Mcenter\DAO;

use Mcenter\Util;
use Mcenter\Exception\DatabaseException;
use \Logger;

/**
 * Description of OTPConfigurationDAO
 *
 * @author hoanvd
 */
class OTPConfigurationDAO extends TenantSupportDAO {

	private static $logger;

	public function __construct($_tenantId) {
		parent::__construct($_tenantId);
		if (!(OTPConfigurationDAO::$logger instanceof Logger)) {
			OTPConfigurationDAO::$logger = Logger::getLogger(__CLASS__);
		}
	}

	/**
	 * Get OTP Configuration info
	 *
	 * @throws DatabaseException
	 * @return Array An OTPConfiguration
	 */
	public function getOTPConfig() {
		$retVal = null;
		try {
			$dbUtils = Util\DatabaseUtil::getInstance();
			$query = "SELECT * FROM otp_configuration where tenant_id = :tenantId";
      $params = array(':tenantId' => $this->tenantId);
			$retVal = $dbUtils->fetchOne($query, $params);
		} catch (Exception $exc) {
			//logger.error("Error when get OTP Configuration", $exc);
			throw new DatabaseException('Error when get OTP Configuration');
		}
		return $retVal;
	}

	/**
	 * Update OTP Configuration
	 *
	 * @param int $otpLength Length of OTP code
	 * @param int $timeExpire Time expire of OTP code
	 * @param void $otpType Type of OTP
	 * @throws DatabaseException
	 * @return bool True on success or False on failure
	 */
	public function saveOTPConfig($otpLength, $timeExpire, $otpType) {
		$retVal = false;
		try {
			$dbUtil = \Mcenter\Util\DatabaseUtil::getInstance();
			$query = "UPDATE otp_configuration SET otp_length = :OTPLength, time_expire = :timeExpire, otp_type = :OTPType where tenant_id = :tenantId";
			$params = array(
				':OTPLength' => $otpLength,
				':timeExpire' => $timeExpire,
				':OTPType' => $otpType,
        ':tenantId' => $this->tenantId
			);
			$retVal = $dbUtil->executeQuery($query, $params);
		} catch (Exception $exe) {
			OTPConfigurationDAO::$logger->error('Error when update OTP Configuration', $exe);
			throw new DatabaseException('Error when update OTP Configuration');
		}
		return $retVal;
	}

}
