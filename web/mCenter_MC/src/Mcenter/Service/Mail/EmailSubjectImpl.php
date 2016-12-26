<?php

namespace Mcenter\Service\Mail;

use Mcenter\DAO\MailTemplateDAO;
use Mcenter\Util\Constants;

/**
 * Description of EmailSubjectImpl
 *
 * @author hoanvd
 */
class EmailSubjectImpl implements IEmailSubjectBuilder {

    /**
     * Build subject for email
     *
     * @param string $emailAction
     * @param Array $tenantId
     * @return string Subject of email correspond to email action and model map
     */
    public function buildSubject($emailAction, $tenantId) {
      $usageSystem = Constants::$MCENTER_USAGE_SYSTEM;
      $mailTemplateDAO = new MailTemplateDAO($tenantId);
      $msg = $mailTemplateDAO->getEmailSubject($usageSystem, $emailAction, $tenantId);
      return $msg;
    }

}
