<?php

namespace Mcenter\Config;

use Mcenter\DAO\ConfigurationDAO;
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of MCenterConfig
 *
 * @author danhtn
 */
class MCenterConfig
{

    public static function getDbConfig()
    {
        return array(
            'dbname' => "mcenter",
            'host' => "localhost",
            'dbUsername' => "postgres",
            'dbPassword' => "gcsvn123"
        );
    }

    public static function getMailConfig()
    {
        return array(
            'from_email' => "mcenteradmin@gmail.com",
            'host' => "smtp.gmail.com",
            'port' => "465",
            'authenticate' => true,
            'user_name' => "freedibletesting@gmail.com",
            'password' => "chutieu008",
            'secure' => "ssl"
        );
    }

    public static function getMailTemplates()
    {
        $tplRootDir = __DIR__ . DIRECTORY_SEPARATOR . "mail_tpl" . DIRECTORY_SEPARATOR;
        return array(
            'email_otp_request' => $tplRootDir . "email_otp_request.txt",
            'request_otp_password' => $tplRootDir . "request_otp_password.txt",
            'request_totp_secret_token' => $tplRootDir . "request_totp_secret_token.txt",
            'request_password' => $tplRootDir . "request_password.txt",
            'send_deactivate_user_role_response' => $tplRootDir . "send_deactivate_user_role_response.txt",
            'send_exception_email' => $tplRootDir . "send_exception_email.txt",
            'send_updated_user_role_response' => $tplRootDir . "send_updated_user_role_response.txt",
            'send_user_register_request' => $tplRootDir . "send_user_register_request.txt",
            'send_user_registered_response' => $tplRootDir . "send_user_registered_response.txt",
            'sms_otp_request' => $tplRootDir . "sms_otp_request.txt"
        );
    }

    public static function getClientVersion()
    {
      return '20150708r111';
    }
    /**
     * Get platform version
     * @param type $platform
     * @return type
     */
    public static function getPlatformVersion($platform)
    {
      $configurationDAO = new ConfigurationDAO();
      $platformVersion = $configurationDAO->getContigurationValue($platform);
      return $platformVersion;
    }

}
