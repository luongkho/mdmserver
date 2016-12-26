<?php

namespace Mcenter\Lib;

use \Tonic\Request;

/**
 * User resource
 *
 *
 * @uri /user/
 * @author hoanvd
 */
class MCSession
{
  static private $instance;
  /**
   * The singleton logic http://en.wikipedia.org/wiki/Singleton_pattern#PHP_5
   * @return MCSession
   */
  static public function getInstance()
  {
    if (!self::$instance instanceof self) {
      self::$instance = new self;
    }
    return self::$instance;
  }

  public function get($att, $default = null)
  {
    $session = $_SESSION;
    if (isset($session[$att])) {
      $returnVal = $session[$att];
    } else {
      $returnVal = $default;
    }

    return $returnVal;
  }
  public function getSessionId()
  {
    return session_id();
  }
  public function  set($att, $val)
  {
    $_SESSION[$att] = $val;
  }

  public function destroy($att)
  {
    if (isset($_SESSION[$att])) {
      unset($_SESSION[$att]);
    }
  }

  public function destroyAll()
  {
    return session_destroy();
  }

  protected function __construct()
  {
    $option =array(
      'session_name'            => 'mcenter',
      'session_cache_limiter'   => null,
      'session_lifetime' => 0,
      'token_name' => 'Authorization'
    );

    //Disable use cookie to store session id
    ini_set("session.use_cookies",0);
    ini_set("session.use_only_cookies",0);
    ini_set("session.use_trans_sid",0);

    //get session id from the header
    $tokenName = $option['token_name'];
    $header = apache_request_headers();
    if(isset($header[$tokenName]) && $header[$tokenName] != '') {
      $sessionid = $header[$tokenName];
      @session_id($sessionid);
    }

    //set other session options
    session_name($option['session_name']);
    session_cache_limiter($option['session_cache_limiter']);
    ini_set('session.gc_maxlifetime', $option['session_lifetime']);

    //Start the session
    session_start();
  }
}
