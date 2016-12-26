<?php

namespace Mcenter\DAO;

use Logger;
use Mcenter\Exception\DatabaseException;

/**
 * Description of UserInfoDAO
 *
 * @author hoanvd
 */
class ConfigurationDAO
{

    private static $logger;

    public function __construct()
    {
        if (!(ConfigurationDAO::$logger instanceof Logger)) {
            ConfigurationDAO::$logger = Logger::getLogger(__CLASS__);
        }
    }
    /**
     * Get configuration value by config key
     * @param type $configKey
     * @return string Config value
     * @throws DatabaseException
     */
    public function getContigurationValue($configKey)
    {
      try {
          $dbUtil = \Mcenter\Util\DatabaseUtil::getInstance();
          $query = "SELECT config_val FROM configuration WHERE config_key = :configKey";
          $configValue = $dbUtil->fetchOne($query, array(':configKey' => $configKey));
          return $configValue;
      } catch (\Exception $exc) {
          ConfigurationDAO::$logger->error($exc->getMessage());
          throw new DatabaseException("Error when get configuration");
      }
    }
    public function getContigurationGroup($parentKey)
    {
      try {
          $dbUtil = \Mcenter\Util\DatabaseUtil::getInstance();
          $query = "SELECT config_key, config_val FROM configuration WHERE config_key LIKE :configKey";
          $configGroup = $dbUtil->fetchAll($query, array(':configKey' => "%$parentKey%"));
          return $configGroup;
      } catch (\Exception $exc) {
          ConfigurationDAO::$logger->error("Error when get configuration group", $exc);
          throw new DatabaseException("Error when get configuration group");
      }
    }
}
