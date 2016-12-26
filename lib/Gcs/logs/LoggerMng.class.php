<?php
/**
 * Base class to get logger in MDM System
 * 
 * @category   ESF
 * @package    Core
 * @subpackage Logs
 * @author     ESF-GCS Dev Team <vfs-dev@emis.screen.co.jp>
 * @version    $Id$
 */
final class LoggerMng
{

  const KEY_OPERATOR = "operator";
  const KEY_MESSAGE = "message";
  const KEY_PARAM = "param";
  const LOGGER_MDM = "mdm";
  const LOGGER_CONFIG_FILE = "logs.yml";
  const LOGTYPE_NORMAL = "NORMAL";
  const LOGTYPE_DEBUG = "DEBUG";

  private static $isInitialized = false;

  private static function getLog($name)
  {
    if (!self::$isInitialized) {
      self::initialize();
      self::$isInitialized = true;
    }
    return Logger::getLogger($name);
  }

  /**
   * Function used to initialize Logger and and config of esf log
   */
  private static function initialize()
  {
//    $defaulLogDir = sfConfig::get('sf_log_dir');
//    $esfConfig = esfConfig::getInstance();
//    if (esfConfig::getInstance()->writableLog()) {
//      $esfLogDir = $esfConfig->getLogFolderPath();
//    } else {
//      $esfLogDir = $defaulLogDir;
//    }
//    sfConfig::set("esf_log_dir", $esfLogDir);
//    //Initial log level
//    $logLevel = strtoupper($esfConfig->getLogConfigType());
//    if ($logLevel != self::LOGTYPE_DEBUG) {
//      $logLevel = LoggerLevel::getLevelInfo()->toString();
//    }
//    sfConfig::set("esf_log_level", $logLevel);
//    //max log filesize
//    $logMaxFileSize = $esfConfig->getLogConfigFilesize();
//    sfConfig::set("esf_log_filesize", $logMaxFileSize);
//
//    //retention day
//    $logRetention = $esfConfig->getLogConfigRetentiondays();
//    sfConfig::set("esf_log_retention", $logRetention);
    $configs = sfSimpleYamlConfigHandler::replaceConstants(
                    sfSimpleYamlConfigHandler::getConfiguration(array(sfConfig::get('sf_config_dir')
                        . DIRECTORY_SEPARATOR . self::LOGGER_CONFIG_FILE)));
    Logger::configure($configs);
  }

  /**
   * @return Logger
   */
  public static function getMDMLogger()
  {
    return self::getLog(self::LOGGER_MDM);
  }

}