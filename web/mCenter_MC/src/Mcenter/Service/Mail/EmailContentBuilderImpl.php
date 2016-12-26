<?php

namespace Mcenter\Service\Mail;

use Mcenter\DAO\MailTemplateDAO;
use Mcenter\Util\Constants;
use Logger;

/**
 * Description of EmailContentBuilderImpl
 *
 * @author hoanvd
 */
class EmailContentBuilderImpl implements IEmailContentBuilder {

  /**
   * Build email message
   *
   * @param string $emailAction
   * @param Array $modelMap
   * @param string $tenantId
   * @return string Email message
   */
  public function buildContent($emailAction, $modelMap, $tenantId) {
    $usageSystem = Constants::$MCENTER_USAGE_SYSTEM;
    $mailTemplateDAO = new MailTemplateDAO($tenantId);
    $msg = $mailTemplateDAO->getEmailTemplate($usageSystem, $emailAction, $tenantId);
    if ($msg) {
      $keyList = array_keys($modelMap);
      $valueList = array_values($modelMap);
      $msg = str_replace($keyList,$valueList,$msg);
    } else {
      Logger::getLogger(__CLASS__)->debug("Email template do not exist.", null);
    }
    return $msg;
  }

}
