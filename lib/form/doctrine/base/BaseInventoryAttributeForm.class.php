<?php

/**
 * InventoryAttribute form base class.
 *
 * @method InventoryAttribute getObject() Returns the current form's model object
 *
 * @package    mdm-server
 * @subpackage form
 * @author     Dung Huynh
 * @version    SVN: $Id: sfDoctrineFormGeneratedTemplate.php 29553 2010-05-20 14:33:00Z Kris.Wallsmith $
 */
abstract class BaseInventoryAttributeForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'                 => new sfWidgetFormInputHidden(),
      'name'               => new sfWidgetFormInputText(),
      'slug'               => new sfWidgetFormInputText(),
      'inventory_group_id' => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('InventoryAttributeGroup'), 'add_empty' => false)),
      'created_at'         => new sfWidgetFormDateTime(),
      'updated_at'         => new sfWidgetFormDateTime(),
    ));

    $this->setValidators(array(
      'id'                 => new sfValidatorChoice(array('choices' => array($this->getObject()->get('id')), 'empty_value' => $this->getObject()->get('id'), 'required' => false)),
      'name'               => new sfValidatorString(array('max_length' => 250)),
      'slug'               => new sfValidatorString(array('max_length' => 250, 'required' => false)),
      'inventory_group_id' => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('InventoryAttributeGroup'))),
      'created_at'         => new sfValidatorDateTime(),
      'updated_at'         => new sfValidatorDateTime(),
    ));

    $this->widgetSchema->setNameFormat('inventory_attribute[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'InventoryAttribute';
  }

}
