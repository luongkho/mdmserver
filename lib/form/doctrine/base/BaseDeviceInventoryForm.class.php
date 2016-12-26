<?php

/**
 * DeviceInventory form base class.
 *
 * @method DeviceInventory getObject() Returns the current form's model object
 *
 * @package    mdm-server
 * @subpackage form
 * @author     Dung Huynh
 * @version    SVN: $Id: sfDoctrineFormGeneratedTemplate.php 29553 2010-05-20 14:33:00Z Kris.Wallsmith $
 */
abstract class BaseDeviceInventoryForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'               => new sfWidgetFormInputHidden(),
      'device_name'      => new sfWidgetFormInputText(),
      'manufacturer'     => new sfWidgetFormInputText(),
      'product_name'     => new sfWidgetFormInputText(),
      'device_type'      => new sfWidgetFormInputText(),
      'registration_id'  => new sfWidgetFormInputText(),
      'enroll_status'    => new sfWidgetFormInputText(),
      'version'          => new sfWidgetFormInputText(),
      'organization'     => new sfWidgetFormInputText(),
      'location'         => new sfWidgetFormInputText(),
      'platform'         => new sfWidgetFormInputText(),
      'purchase_date'    => new sfWidgetFormDateTime(),
      'warranty_end'     => new sfWidgetFormDateTime(),
      'user_id'          => new sfWidgetFormInputText(),
      'owner_name'       => new sfWidgetFormInputText(),
      'owner_email'      => new sfWidgetFormInputText(),
      'udid'             => new sfWidgetFormInputText(),
      'imei'             => new sfWidgetFormInputText(),
      'wifi_mac_address' => new sfWidgetFormInputText(),
      'passcode'         => new sfWidgetFormInputText(),
      'model'            => new sfWidgetFormInputText(),
      'created_at'       => new sfWidgetFormDateTime(),
      'updated_at'       => new sfWidgetFormDateTime(),
    ));

    $this->setValidators(array(
      'id'               => new sfValidatorChoice(array('choices' => array($this->getObject()->get('id')), 'empty_value' => $this->getObject()->get('id'), 'required' => false)),
      'device_name'      => new sfValidatorString(array('max_length' => 255, 'required' => false)),
      'manufacturer'     => new sfValidatorString(array('max_length' => 255, 'required' => false)),
      'product_name'     => new sfValidatorString(array('max_length' => 255, 'required' => false)),
      'device_type'      => new sfValidatorString(array('max_length' => 255, 'required' => false)),
      'registration_id'  => new sfValidatorString(array('max_length' => 255, 'required' => false)),
      'enroll_status'    => new sfValidatorInteger(array('required' => false)),
      'version'          => new sfValidatorString(array('max_length' => 45, 'required' => false)),
      'organization'     => new sfValidatorString(array('max_length' => 250, 'required' => false)),
      'location'         => new sfValidatorString(array('max_length' => 250, 'required' => false)),
      'platform'         => new sfValidatorInteger(array('required' => false)),
      'purchase_date'    => new sfValidatorDateTime(array('required' => false)),
      'warranty_end'     => new sfValidatorDateTime(array('required' => false)),
      'user_id'          => new sfValidatorInteger(),
      'owner_name'       => new sfValidatorString(array('max_length' => 250, 'required' => false)),
      'owner_email'      => new sfValidatorString(array('max_length' => 250, 'required' => false)),
      'udid'             => new sfValidatorString(array('max_length' => 64, 'required' => false)),
      'imei'             => new sfValidatorString(array('max_length' => 64, 'required' => false)),
      'wifi_mac_address' => new sfValidatorString(array('max_length' => 64, 'required' => false)),
      'passcode'         => new sfValidatorString(array('max_length' => 32, 'required' => false)),
      'model'            => new sfValidatorString(array('max_length' => 50, 'required' => false)),
      'created_at'       => new sfValidatorDateTime(),
      'updated_at'       => new sfValidatorDateTime(),
    ));

    $this->widgetSchema->setNameFormat('device_inventory[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'DeviceInventory';
  }

}
