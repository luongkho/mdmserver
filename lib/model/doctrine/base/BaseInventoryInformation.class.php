<?php
// Connection Component Binding
Doctrine_Manager::getInstance()->bindComponent('InventoryInformation', 'mdmserver');

/**
 * BaseInventoryInformation
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @property integer $device_id
 * @property text $value
 * @property integer $inventory_group_id
 * @property DeviceInventory $DeviceInventory
 * @property InventoryAttributeGroup $InventoryAttributeGroup
 * 
 * @method integer                 getDeviceId()                Returns the current record's "device_id" value
 * @method text                    getValue()                   Returns the current record's "value" value
 * @method integer                 getInventoryGroupId()        Returns the current record's "inventory_group_id" value
 * @method DeviceInventory         getDeviceInventory()         Returns the current record's "DeviceInventory" value
 * @method InventoryAttributeGroup getInventoryAttributeGroup() Returns the current record's "InventoryAttributeGroup" value
 * @method InventoryInformation    setDeviceId()                Sets the current record's "device_id" value
 * @method InventoryInformation    setValue()                   Sets the current record's "value" value
 * @method InventoryInformation    setInventoryGroupId()        Sets the current record's "inventory_group_id" value
 * @method InventoryInformation    setDeviceInventory()         Sets the current record's "DeviceInventory" value
 * @method InventoryInformation    setInventoryAttributeGroup() Sets the current record's "InventoryAttributeGroup" value
 * 
 * @package    mdm-server
 * @subpackage model
 * @author     Dung Huynh
 * @version    SVN: $Id: Builder.php 7490 2010-03-29 19:53:27Z jwage $
 */
abstract class BaseInventoryInformation extends sfDoctrineRecord
{
    public function setTableDefinition()
    {
        $this->setTableName('inventory_information');
        $this->hasColumn('device_id', 'integer', null, array(
             'type' => 'integer',
             'notnull' => true,
             ));
        $this->hasColumn('value', 'text', null, array(
             'type' => 'text',
             ));
        $this->hasColumn('inventory_group_id', 'integer', null, array(
             'type' => 'integer',
             'notnull' => true,
             ));
    }

    public function setUp()
    {
        parent::setUp();
        $this->hasOne('DeviceInventory', array(
             'local' => 'device_id',
             'foreign' => 'id',
             'onDelete' => 'CASCADE'));

        $this->hasOne('InventoryAttributeGroup', array(
             'local' => 'inventory_group_id',
             'foreign' => 'id',
             'onDelete' => 'CASCADE'));

        $timestampable0 = new Doctrine_Template_Timestampable();
        $this->actAs($timestampable0);
    }
}