<?php

/**
 * Description: Interface for Group Attribute service
 * 
 * Modify History:
 *  September 10, 2015: cuongnd initial version
 */

namespace Gcs\Contract;

interface GroupAttributeInterface {

    public function getAllGroups();

    public function getAttributeOfGroup($group_id);

    public function getAllAttributes();
}
