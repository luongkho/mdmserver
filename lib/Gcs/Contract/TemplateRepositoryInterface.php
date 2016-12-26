<?php

/**
 * Description: Interface for Template service
 * 
 * Modify History:
 *  September 10, 2015: cuongnd initial version
 */

namespace Gcs\Contract;

interface TemplateRepositoryInterface {

    public function listAllTemplate($request);

    public function getTemplateById($template_id);
}
