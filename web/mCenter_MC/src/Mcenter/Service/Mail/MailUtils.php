<?php

namespace Mcenter\Service\Mail;

use Logger;
use PHPMailer;
use Mcenter\DAO\AppCrossDAO;
use Mcenter\Config\MCenterConfig;

/**
 * Description of MailUtils
 * Send email
 *
 * @author hoanvd
 */
final class MailUtils {



    private static $logger;

    public function __construct() {
        if (!(self::$logger instanceof Logger)) {
            self::$logger = Logger::getLogger(__CLASS__);
        }
    }

    public static function getInstance() {
        static $instance = null;
        if (null === $instance) {
            $instance = new static();
        }

        return $instance;
    }

    /**
     *
     * @param string $emailAction
     * @param Array $modelMap
     * @param string $from
     * @param Array $tos
     * @param Array $ccs
     * @param string $tenantId
     * @return void
     */
    public static function sendEmail($emailAction, $modelMap , $from, $tos, $ccs, $tenantId) {
        self::$logger->debug("sendEmail[ emailAction = $emailAction, modelMap = ".  implode("|", $modelMap).", from = $from, tos = ".  implode("|", $tos).", ccs = ".  implode("|", $ccs)."]<---------------BEGIN");
        $emailBuilder = new EmailBuilderImpl();
        $isHtml = false;
        if (array_key_exists($emailAction, EmailContentType::getContentTypeConfig())) {
            $contentTypeConfig = EmailContentType::getContentTypeConfig();
            $isHtml = $contentTypeConfig[$emailAction] == EmailContentType::HTML;
        }
        $email = $emailBuilder->createEmail($emailAction, $isHtml, $modelMap, $tenantId);
        if (trim($from)) {
            $email->SetFrom($from);
        }
        if ($tos) {
            foreach ($tos as $to) {
                $email->addAddress($to);
            }
        }
        if ($ccs) {
            foreach ($ccs as $cc) {
                $email->addCC($cc);
            }
        }
        self::$logger->debug("sendEmail[]--------------->END");
        return self::sendMail($email);
    }

    /**
     *
     * @param \PHPMailer $email
     * @return void
     */
    public static function sendMail($email) {
        self::$logger->debug("sendMail[ ]<---------------BEGIN");
        try {
            if($email){
                $mailConfig = MCenterConfig::getMailConfig();
                $fromEmail = $mailConfig['from_email'];
                $email->setFrom($fromEmail);
                // Change Config Send mail

                if($mailConfig){
                    $email->isSMTP();
                    $email->Host = $mailConfig['host'];
                    $email->SMTPAuth = $mailConfig['authenticate'];         ;
                    $email->Username = $mailConfig['user_name'];
                    $email->Password = $mailConfig['password'];
                    $email->SMTPSecure = $mailConfig['secure'];
                    $email->Port = $mailConfig['port'];
                }
                return $email->send();

            }else{
                throw new EmailException("Email must not be null");
            }
        } catch (\Exception $exc) {
            throw new EmailException("Error in sending email",$exc);
        }

        self::$logger->debug("sendMail[ ]<---------------END");
    }

}
