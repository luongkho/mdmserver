<?php

namespace Mcenter\Util;

use PDO;
use PDOStatement;
use Mcenter\Config\MCenterConfig;

/**
 * User resource
 *
 *
 * @uri /user/
 * @author hoanvd
 */
class DatabaseUtil {

	private $_dbconnection = null;

	protected function __construct() {
		$DbConfig = MCenterConfig::getDbConfig();
    $dbname = $DbConfig["dbname"];
    $host = $DbConfig["host"];
    $dbUsername = $DbConfig["dbUsername"];
    $dbPassword = $DbConfig["dbPassword"];
    $this->_dbconnection = new PDO("pgsql:dbname=$dbname;host=$host", "$dbUsername", "$dbPassword");
  }

	public static function getInstance() {
		static $instance = null;
		if (null === $instance) {
			$instance = new static();
		}

		return $instance;
	}

	public function fetchAll($query, $params = array()) {
		$sqlStat = $this->_dbconnection->prepare($query);
		if ($sqlStat->execute($params)) {
			return $sqlStat->fetchAll(PDO::FETCH_ASSOC);
		} else {
			return false;
		}
	}

	public function fetchOne($query, $params = array()) {
    $sqlStat = $this->_dbconnection->prepare($query);
    if ($sqlStat->execute($params)) {
      return $sqlStat->fetch(PDO::FETCH_ASSOC);
    } else {
      return false;
    }
  }

	public function executeQuery($query, $params = array()) {
		$sqlStat = $this->_dbconnection->prepare($query);
		return $sqlStat->execute($params);
	}

}
