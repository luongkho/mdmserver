<?php

/**
 * DeviceApplication form base class.
 *
 * @method DeviceApplication getObject() Returns the current form's model object
 *
 * @package    mdm-server
 * @subpackage form
 * @author     Dung Huynh
 * @version    SVN: $Id: sfDoctrineFormGeneratedTemplate.php 29553 2010-05-20 14:33:00Z Kris.Wallsmith $
 */
abstract class BaseDeviceApplicationForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'         => new sfWidgetFormInputHidden(),
      'device_id'  => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('DeviceInventory'), 'add_empty' => false)),
      'name'       => new sfWidgetFormInputText(),
      'version'    => new sfWidgetFormInputText(),
      'identifier' => new sfWidgetFormInputText(),
      'size'       => new sfWidgetFormInputText(),
      'created_at' => new sfWidgetFormDateTime(),
      'updated_at' => new sfWidgetFormDateTime(),
    ));

    $this->setValidators(array(
      'id'         => new sfValidatorChoice(array('choices' => array($this->getObject()->get('id')), 'empty_value' => $this->getObject()->get('id'), 'required' => false)),
      'device_id'  => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('DeviceInventory'))),
      'name'       => new sfValidatorString(array('max_length' => 250, 'required' => false)),
      'version'    => new sfValidatorString(array('max_length' => 45, 'required' => false)),
      'identifier' => new sfValidatorString(array('max_length' => 45, 'required' => false)),
      'size'       => new sfValidatorString(array('max_length' => 45, 'required' => false)),
      'created_at' => new sfValidatorDateTime(),
      'updated_at' => new sfValidatorDateTime(),
    ));

    $this->widgetSchema->setNameFormat('device_application[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'DeviceApplication';
  }

}
