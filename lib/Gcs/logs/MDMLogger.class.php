<?php

/**
 * Base class is wrapped for esf operation logger
 * 
 * @category   ESF
 * @package    Core
 * @subpackage Logs
 * @author     ESF-GCS Dev Team <vfs-dev@emis.screen.co.jp>
 * @version    $Id$
 */
class MDMLogger
{

  const KEY_OPERATOR = "operator";
  const KEY_MESSAGE = "message";
  const KEY_PARAM = "param";

  /** @var esfOperationLogger Singleton instance of esfOperationLogger */
  private static $instance = null;

  /** @var Logger  */
  public $logger;

  /**
   * Get singleton instance of esfOperationLogger
   * @return MDMLogger
   */
  public static function getInstance($name = null)
  {
    if (null === self::$instance) {
      self::$instance = new MDMLogger($name);
    }
    return self::$instance;
  }

  /**
   * Constructor
   * Protected accessibility to prevent generating its instance from outside
   * @return void
   */
  protected function __construct()
  {
    $this->logger = LoggerMng::getMDMLogger();
  }

  /**
   * Log message with debug level
   *
   * @param string $operation operation name
   * @param string $message message to log
   * @return void
   */
  public function debug($operation, $message, $param = array())
  {
    $this->log(LoggerLevel::getLevelDebug(), $operation, $message, $param);
  }

  /**
   * Log message with error level
   *
   * @param string $operation operation name
   * @param string $message message to log
   * @return void
   */
  public function error($operation, $message, $param = array())
  {
    $this->log(LoggerLevel::getLevelError(), $operation, $message, $param);
  }

  /**
   * Log message with fatal level
   *
   * @param string $operation operation name
   * @param string $message message to log
   * @return void
   */
  public function fatal($operation, $message, $param = array())
  {
    $this->log(LoggerLevel::getLevelFatal(), $operation, $message, $param);
  }

  /**
   * Log message with info level
   *
   * @param string $operation operation name
   * @param string $message message to log.
   * @param string[] $param Additional param for log message
   * @return void
   */
  public function info($operation, $message, $param = array())
  {
    $this->log(LoggerLevel::getLevelInfo(), $operation, $message, $param);
  }

  /**
   * Log message with warn level
   *
   * @param string $operation operation name
   * @param string $message message to log.
   * @return void
   */
  public function warn($operation, $message, $param = array())
  {
    $this->log(LoggerLevel::getLevelWarn(), $operation, $message, $param);
  }

  private function log($level, $operation, $message, $param = array())
  {
//    date_default_timezone_set('Asia/Saigon');
    $date = date('m/d/Y h:i:s a', time());
    $message =   $date ."   " . $message;
    if ($this->logger instanceof Logger) {
      $this->logger->log($level, $message);
    }
  }
}