<?php

/**
 * Description: Get attribute groups of device.
 *  Get all attribute of each group.
 *  Get all attribute of device.
 * 
 * Modify History:
 *  August 20, 2015: dungdh initial version
 */

namespace Gcs\Repository;

use Gcs\Contract\GroupAttributeInterface;

class AttributeGroupRepository implements GroupAttributeInterface
{

    /**
     * Get all group attributes.
     *
     * @return [type] [description]
     */
    public function getAllGroups()
    {
        $groupTable = \InventoryAttributeGroupTable::getInstance();
        $query = $groupTable
                ->createQuery('g')
                ->orderBy('g.updated_at ASC');

        return $query->execute()->toArray();
    }

    /**
     * get all attributes of group.
     *
     * @param int $group_id [description]
     *
     * @return array [description]
     */
    public function getAttributeOfGroup($group_id)
    {
        $attributesTable = \InventoryAttributeTable::getInstance();
        $query = $attributesTable
                ->createQuery('t')
                ->where('t.inventory_group_id = ?', $group_id)
                ->orderBy('t.updated_at ASC');

        return $query->execute()->toArray();
    }

    /**
     * get all inventory attributes.
     *
     * @return [type] [description]
     */
    public function getAllAttributes()
    {
        $result = array();
        $groupArray = $this->getAllGroups();

        foreach ($groupArray as $key => $group) {
            $result[$group['name']] = $this->getAttributeOfGroup($group['id']);
        }

        return $result;
    }

}
