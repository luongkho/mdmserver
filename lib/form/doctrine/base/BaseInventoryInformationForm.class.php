<?php

/**
 * InventoryInformation form base class.
 *
 * @method InventoryInformation getObject() Returns the current form's model object
 *
 * @package    mdm-server
 * @subpackage form
 * @author     Dung Huynh
 * @version    SVN: $Id: sfDoctrineFormGeneratedTemplate.php 29553 2010-05-20 14:33:00Z Kris.Wallsmith $
 */
abstract class BaseInventoryInformationForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'                 => new sfWidgetFormInputHidden(),
      'device_id'          => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('DeviceInventory'), 'add_empty' => false)),
      'value'              => new sfWidgetFormInputText(),
      'inventory_group_id' => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('InventoryAttributeGroup'), 'add_empty' => false)),
      'created_at'         => new sfWidgetFormDateTime(),
      'updated_at'         => new sfWidgetFormDateTime(),
    ));

    $this->setValidators(array(
      'id'                 => new sfValidatorChoice(array('choices' => array($this->getObject()->get('id')), 'empty_value' => $this->getObject()->get('id'), 'required' => false)),
      'device_id'          => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('DeviceInventory'))),
      'value'              => new sfValidatorPass(array('required' => false)),
      'inventory_group_id' => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('InventoryAttributeGroup'))),
      'created_at'         => new sfValidatorDateTime(),
      'updated_at'         => new sfValidatorDateTime(),
    ));

    $this->widgetSchema->setNameFormat('inventory_information[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'InventoryInformation';
  }

}
