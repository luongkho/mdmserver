<?php
// Connection Component Binding
Doctrine_Manager::getInstance()->bindComponent('ProfileAttribute', 'mdmserver');

/**
 * BaseProfileAttribute
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @property string $profile_attribute_name
 * @property string $profile_attribute_key
 * @property integer $profile_attribute_group_id
 * @property ProfileAttributeGroup $ProfileAttributeGroup
 * 
 * @method string                getProfileAttributeName()       Returns the current record's "profile_attribute_name" value
 * @method string                getProfileAttributeKey()        Returns the current record's "profile_attribute_key" value
 * @method integer               getProfileAttributeGroupId()    Returns the current record's "profile_attribute_group_id" value
 * @method ProfileAttributeGroup getProfileAttributeGroup()      Returns the current record's "ProfileAttributeGroup" value
 * @method ProfileAttribute      setProfileAttributeName()       Sets the current record's "profile_attribute_name" value
 * @method ProfileAttribute      setProfileAttributeKey()        Sets the current record's "profile_attribute_key" value
 * @method ProfileAttribute      setProfileAttributeGroupId()    Sets the current record's "profile_attribute_group_id" value
 * @method ProfileAttribute      setProfileAttributeGroup()      Sets the current record's "ProfileAttributeGroup" value
 * 
 * @package    mdm-server
 * @subpackage model
 * @author     Dung Huynh
 * @version    SVN: $Id: Builder.php 7490 2010-03-29 19:53:27Z jwage $
 */
abstract class BaseProfileAttribute extends sfDoctrineRecord
{
    public function setTableDefinition()
    {
        $this->setTableName('profile_attribute');
        $this->hasColumn('profile_attribute_name', 'string', 250, array(
             'type' => 'string',
             'notnull' => true,
             'length' => 250,
             ));
        $this->hasColumn('profile_attribute_key', 'string', 250, array(
             'type' => 'string',
             'length' => 250,
             ));
        $this->hasColumn('profile_attribute_group_id', 'integer', null, array(
             'type' => 'integer',
             'notnull' => true,
             ));
    }

    public function setUp()
    {
        parent::setUp();
        $this->hasOne('ProfileAttributeGroup', array(
             'local' => 'profile_attribute_group_id',
             'foreign' => 'id',
             'onDelete' => 'CASCADE'));

        $timestampable0 = new Doctrine_Template_Timestampable();
        $this->actAs($timestampable0);
    }
}