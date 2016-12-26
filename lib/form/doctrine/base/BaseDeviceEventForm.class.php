<?php

/**
 * DeviceEvent form base class.
 *
 * @method DeviceEvent getObject() Returns the current form's model object
 *
 * @package    mdm-server
 * @subpackage form
 * @author     Dung Huynh
 * @version    SVN: $Id: sfDoctrineFormGeneratedTemplate.php 29553 2010-05-20 14:33:00Z Kris.Wallsmith $
 */
abstract class BaseDeviceEventForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'                => new sfWidgetFormInputHidden(),
      'device_id'         => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('DeviceInventory'), 'add_empty' => false)),
      'model'             => new sfWidgetFormInputText(),
      'event_type'        => new sfWidgetFormInputText(),
      'event_name'        => new sfWidgetFormInputText(),
      'sent_by'           => new sfWidgetFormInputText(),
      'status'            => new sfWidgetFormInputText(),
      'manage_event_flag' => new sfWidgetFormInputText(),
      'command_data'      => new sfWidgetFormInputText(),
      'command_uuid'      => new sfWidgetFormInputText(),
      'sender_email'      => new sfWidgetFormInputText(),
      'owner_name'        => new sfWidgetFormInputText(),
      'note'              => new sfWidgetFormInputText(),
      'created_at'        => new sfWidgetFormDateTime(),
      'updated_at'        => new sfWidgetFormDateTime(),
    ));

    $this->setValidators(array(
      'id'                => new sfValidatorChoice(array('choices' => array($this->getObject()->get('id')), 'empty_value' => $this->getObject()->get('id'), 'required' => false)),
      'device_id'         => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('DeviceInventory'))),
      'model'             => new sfValidatorString(array('max_length' => 45, 'required' => false)),
      'event_type'        => new sfValidatorString(array('max_length' => 45, 'required' => false)),
      'event_name'        => new sfValidatorString(array('max_length' => 45, 'required' => false)),
      'sent_by'           => new sfValidatorInteger(),
      'status'            => new sfValidatorInteger(array('required' => false)),
      'manage_event_flag' => new sfValidatorInteger(array('required' => false)),
      'command_data'      => new sfValidatorString(array('max_length' => 255, 'required' => false)),
      'command_uuid'      => new sfValidatorString(array('max_length' => 32, 'required' => false)),
      'sender_email'      => new sfValidatorString(array('max_length' => 255, 'required' => false)),
      'owner_name'        => new sfValidatorString(array('max_length' => 255, 'required' => false)),
      'note'              => new sfValidatorString(array('max_length' => 255, 'required' => false)),
      'created_at'        => new sfValidatorDateTime(),
      'updated_at'        => new sfValidatorDateTime(),
    ));

    $this->widgetSchema->setNameFormat('device_event[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'DeviceEvent';
  }

}
