<?php
namespace Mcenter\Service\Mail;

use PHPMailer;

/**
 * Description of EmailBuilderImpl
 *
 * @author hoanvd
 */
class EmailBuilderImpl implements IEmailBuilder {

    private $emailContentBuilder;
    private $emailSubjectBuilder;

    public function __construct() {
        $this->emailContentBuilder = new EmailContentBuilderImpl();
        $this->emailSubjectBuilder = new EmailSubjectImpl();
    }

    /**
     * Create an email
     *
     * @param string $emailAction
     * @param Array $modelMap
     * @param string $tenantId
     * @return \PHPMailer
     */
    public function createEmail($emailAction, $isHtml, $modelMap, $tenantId) {
        $email = new \PHPMailer();
        $email->isHTML($isHtml);
        if($emailAction && $modelMap){
            $subject = $this->emailSubjectBuilder->buildSubject($emailAction, $tenantId);
            $message = $this->emailContentBuilder->buildContent($emailAction, $modelMap, $tenantId);
            $email->Subject = $subject;
            $email->Body = $message;
        }
        return $email;
    }

}
