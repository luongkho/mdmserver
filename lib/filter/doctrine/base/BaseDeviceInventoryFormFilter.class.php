<?php

/**
 * DeviceInventory filter form base class.
 *
 * @package    mdm-server
 * @subpackage filter
 * @author     Dung Huynh
 * @version    SVN: $Id: sfDoctrineFormFilterGeneratedTemplate.php 29570 2010-05-21 14:49:47Z Kris.Wallsmith $
 */
abstract class BaseDeviceInventoryFormFilter extends BaseFormFilterDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'device_name'      => new sfWidgetFormFilterInput(),
      'manufacturer'     => new sfWidgetFormFilterInput(),
      'product_name'     => new sfWidgetFormFilterInput(),
      'device_type'      => new sfWidgetFormFilterInput(),
      'registration_id'  => new sfWidgetFormFilterInput(),
      'enroll_status'    => new sfWidgetFormFilterInput(),
      'version'          => new sfWidgetFormFilterInput(),
      'organization'     => new sfWidgetFormFilterInput(),
      'location'         => new sfWidgetFormFilterInput(),
      'platform'         => new sfWidgetFormFilterInput(),
      'purchase_date'    => new sfWidgetFormFilterDate(array('from_date' => new sfWidgetFormDate(), 'to_date' => new sfWidgetFormDate())),
      'warranty_end'     => new sfWidgetFormFilterDate(array('from_date' => new sfWidgetFormDate(), 'to_date' => new sfWidgetFormDate())),
      'user_id'          => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'owner_name'       => new sfWidgetFormFilterInput(),
      'owner_email'      => new sfWidgetFormFilterInput(),
      'udid'             => new sfWidgetFormFilterInput(),
      'imei'             => new sfWidgetFormFilterInput(),
      'wifi_mac_address' => new sfWidgetFormFilterInput(),
      'passcode'         => new sfWidgetFormFilterInput(),
      'model'            => new sfWidgetFormFilterInput(),
      'created_at'       => new sfWidgetFormFilterDate(array('from_date' => new sfWidgetFormDate(), 'to_date' => new sfWidgetFormDate(), 'with_empty' => false)),
      'updated_at'       => new sfWidgetFormFilterDate(array('from_date' => new sfWidgetFormDate(), 'to_date' => new sfWidgetFormDate(), 'with_empty' => false)),
    ));

    $this->setValidators(array(
      'device_name'      => new sfValidatorPass(array('required' => false)),
      'manufacturer'     => new sfValidatorPass(array('required' => false)),
      'product_name'     => new sfValidatorPass(array('required' => false)),
      'device_type'      => new sfValidatorPass(array('required' => false)),
      'registration_id'  => new sfValidatorPass(array('required' => false)),
      'enroll_status'    => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'version'          => new sfValidatorPass(array('required' => false)),
      'organization'     => new sfValidatorPass(array('required' => false)),
      'location'         => new sfValidatorPass(array('required' => false)),
      'platform'         => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'purchase_date'    => new sfValidatorDateRange(array('required' => false, 'from_date' => new sfValidatorDateTime(array('required' => false, 'datetime_output' => 'Y-m-d 00:00:00')), 'to_date' => new sfValidatorDateTime(array('required' => false, 'datetime_output' => 'Y-m-d 23:59:59')))),
      'warranty_end'     => new sfValidatorDateRange(array('required' => false, 'from_date' => new sfValidatorDateTime(array('required' => false, 'datetime_output' => 'Y-m-d 00:00:00')), 'to_date' => new sfValidatorDateTime(array('required' => false, 'datetime_output' => 'Y-m-d 23:59:59')))),
      'user_id'          => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'owner_name'       => new sfValidatorPass(array('required' => false)),
      'owner_email'      => new sfValidatorPass(array('required' => false)),
      'udid'             => new sfValidatorPass(array('required' => false)),
      'imei'             => new sfValidatorPass(array('required' => false)),
      'wifi_mac_address' => new sfValidatorPass(array('required' => false)),
      'passcode'         => new sfValidatorPass(array('required' => false)),
      'model'            => new sfValidatorPass(array('required' => false)),
      'created_at'       => new sfValidatorDateRange(array('required' => false, 'from_date' => new sfValidatorDateTime(array('required' => false, 'datetime_output' => 'Y-m-d 00:00:00')), 'to_date' => new sfValidatorDateTime(array('required' => false, 'datetime_output' => 'Y-m-d 23:59:59')))),
      'updated_at'       => new sfValidatorDateRange(array('required' => false, 'from_date' => new sfValidatorDateTime(array('required' => false, 'datetime_output' => 'Y-m-d 00:00:00')), 'to_date' => new sfValidatorDateTime(array('required' => false, 'datetime_output' => 'Y-m-d 23:59:59')))),
    ));

    $this->widgetSchema->setNameFormat('device_inventory_filters[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'DeviceInventory';
  }

  public function getFields()
  {
    return array(
      'id'               => 'Number',
      'device_name'      => 'Text',
      'manufacturer'     => 'Text',
      'product_name'     => 'Text',
      'device_type'      => 'Text',
      'registration_id'  => 'Text',
      'enroll_status'    => 'Number',
      'version'          => 'Text',
      'organization'     => 'Text',
      'location'         => 'Text',
      'platform'         => 'Number',
      'purchase_date'    => 'Date',
      'warranty_end'     => 'Date',
      'user_id'          => 'Number',
      'owner_name'       => 'Text',
      'owner_email'      => 'Text',
      'udid'             => 'Text',
      'imei'             => 'Text',
      'wifi_mac_address' => 'Text',
      'passcode'         => 'Text',
      'model'            => 'Text',
      'created_at'       => 'Date',
      'updated_at'       => 'Date',
    );
  }
}
