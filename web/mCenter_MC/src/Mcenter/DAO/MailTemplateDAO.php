<?php

namespace Mcenter\DAO;

use Mcenter\Exception\DatabaseException;
use Logger;

/**
 * Description of MailTemplateDAO
 *
 * @author danhtn
 */
class MailTemplateDAO extends TenantSupportDAO
{

    private static $logger;

    public function __construct($_tenantId)
    {
        parent::__construct($_tenantId);
        if (!(MailTemplateDAO::$logger instanceof Logger)) {
            MailTemplateDAO::$logger = Logger::getLogger(__CLASS__);
        }
    }

    /**
     * Get email template
     *
     * @param string $usageSystem usage system
     * @param integer $templateCode template code
     * @param integer $tenantId tenantID
     * @throws DatabaseException
     * @return type email subject
     */
    public function getEmailSubject($usageSystem, $templateCode, $tenantId) {
      try {
        $dbUtil = \Mcenter\Util\DatabaseUtil::getInstance();
        $query = "SELECT subject FROM template WHERE usage_system = :usageSystem AND template_code = :templateCode AND tenant_id = :tenantId";
        $token = $dbUtil->fetchOne($query, array(
            ':usageSystem' => $usageSystem,
            ':templateCode' => $templateCode,
            ':tenantId' => $tenantId
        ));
        return $token['subject'];
      } catch (\Exception $exc) {
        MailTemplateDAO::$logger->error("Error when get mail subject", $exc);
        throw new DatabaseException("Error when get mail subject");
      }
    }

    /**
     * Get email template
     *
     * @param string $usageSystem usage system
     * @param integer $templateCode template code
     * @param integer $tenantId tenantID
     * @throws DatabaseException
     * @return type email template
     */
    public function getEmailTemplate($usageSystem, $templateCode, $tenantId) {
      try {
        $dbUtil = \Mcenter\Util\DatabaseUtil::getInstance();
        $query = "SELECT content FROM template WHERE usage_system = :usageSystem AND template_code = :templateCode AND tenant_id = :tenantId";
        $token = $dbUtil->fetchOne($query, array(
            ':usageSystem' => $usageSystem,
            ':templateCode' => $templateCode,
            ':tenantId' => $tenantId
        ));
        return $token['content'];
      } catch (\Exception $exc) {
        MailTemplateDAO::$logger->error("Error when get mail template", $exc);
        throw new DatabaseException("Error when get mail template");
      }
    }
}
