<?php

/**
 * Description: class for Templates service
 * Getting templates, update, delete template.
 * 
 * Modify History:
 *  September 10, 2015: cuongnd initial version
 */

namespace Gcs\Repository;

/**
 * Description of TemplateRepository
 *
 * @author cuongnd.xt
 */
use Gcs\Contract\TemplateRepositoryInterface;
use Gcs\Repository\ConfigRepository;

class TemplateRepository implements TemplateRepositoryInterface {
    
    const TENANT_ID = 100456;
    const MDM_SYSTEM = 3;

    const ERR_NOT_EXIST_TEMPLATE        = 'E3003';
    const ERR_TEMPLATE_SUBJECT_WRONG    = 'E3004';
    const ERR_TEMPLATE_CONTENT_WRONG    = 'E3005';
    const ERR_TEMPLATE_NAME_WRONG       = 'E3006';
    
    /**
     * Get all template
     * @param Array $request
     * @return Array
     */
    public function listAllTemplate($request) {
        $columns = array(
            array('db' => 'd.id', 'dt' => 0),
            array('db' => 'd.name', 'dt' => 1, 'is_search' => true),
            array('db' => 'd.usage_system', 'dt' => 2, 'int_search' => array(
                'type'  => 'config',
                'name'  => 'app_template_usage_data'
            )),
            array(
                'db' => 'd.updated_at',
                'dt' => 3,
                'formatter' => function ($d, $row) {
            return date('jS M y', strtotime($d));
        },
            )
        );
        $limit = \SSP::limit($request);
        $order = \SSP::order($request, $columns);
        $where = \SSP::filter($request, $columns);
        $whereInt = \SSP::filter_integer($request, $columns);
        // first load 
        if (!$where)    {
            $query = \TemplateTable::getInstance()->createQuery('d')
                ->where('d.tenant_id = ?', self::TENANT_ID);
        } else {
            $query = \TemplateTable::getInstance()->createQuery('d');
        }
        $query = $query->limit($limit['limit'])->offset($limit['offset']);
        
        foreach ($where as $key => $val) {
            $query = $query->orWhere($key . ' ILIKE ?', '%' . $val . '%')
                ->andWhere('d.tenant_id = ?', self::TENANT_ID);
        }
        foreach ($whereInt as $key => $value) {
            $query = $query->orWhereIn($key, $value)
                ->andWhere('d.tenant_id = ?', self::TENANT_ID);;
        }
        foreach ($order as $orderBy) {
            $query = $query->addOrderBy($orderBy);
        }
        return array('result' => $query->execute(), 'count' => $query->count());
    }

    /**
     * get Template information by template id.
     *
     * @param int $template_id [description]
     *
     * @return array [description]
     */
    public function getTemplateById($template_id) {
        $templateTable = \TemplateTable::getInstance();
        return $templateTable->find($template_id);
    }

    /**
     * Validate template valid or not
     * @param Array $data
     * @return boolean
     */
    private function validateTemplate($data) {
        $template = $this->getTemplateById($data['id']);
        $templateId = $template->getId();
        if (empty($templateId)) {
            return self::ERR_NOT_EXIST_TEMPLATE;
        } else if (empty($data['subject'])) {
            return self::ERR_TEMPLATE_SUBJECT_WRONG;
        } else if (empty($data['content'])) {
            return self::ERR_TEMPLATE_CONTENT_WRONG;
        } else if (empty ($data['name']))   {
            return self::ERR_TEMPLATE_NAME_WRONG;
        }
        return true;
    }

    /**
     * Update info template
     * @param Array $data
     * @return boolean
     */
    public function updateTemplate($data) {
        $valid = $this->validateTemplate($data);
        if ($valid === true) {
            $templateModel = \TemplateTable::getInstance();
            $templateInfo = $templateModel->find($data['id']);
            $templateInfo->setSubject($data['subject']);
            $templateInfo->setContent($data['content']);
            $templateInfo->setName($data['name']);
            $templateInfo->save();
            $templateId = $templateInfo->getId();
            if ($templateId) {
                return true;
            }
            return false;
        }
        return $valid;
    }

    /*
     * Get mail template
     * $param [string] purpose
     * $return [object] | string if not found
     */

    function getMail($func) {
        $result = array();
        $configRep = new ConfigRepository();
        $mailTemplate = $configRep->getEmailTemplate();
        $templateCode = array_search($func, $mailTemplate);
        
        $table = \TemplateTable::getInstance();
        $query = $table->createQuery('a')
                ->where('a.tenant_id = ?', self::TENANT_ID)
                ->andWhere('a.usage_system = ?', self::MDM_SYSTEM)
                ->andWhere('template_code = ?', $templateCode)->limit(1)
                ->execute();
        
        if (!$query)    {
            return self::ERR_NOT_EXIST_TEMPLATE;
        }
        return $query;
    }

}
