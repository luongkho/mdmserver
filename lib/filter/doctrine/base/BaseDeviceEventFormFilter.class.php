<?php

/**
 * DeviceEvent filter form base class.
 *
 * @package    mdm-server
 * @subpackage filter
 * @author     Dung Huynh
 * @version    SVN: $Id: sfDoctrineFormFilterGeneratedTemplate.php 29570 2010-05-21 14:49:47Z Kris.Wallsmith $
 */
abstract class BaseDeviceEventFormFilter extends BaseFormFilterDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'device_id'         => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('DeviceInventory'), 'add_empty' => true)),
      'model'             => new sfWidgetFormFilterInput(),
      'event_type'        => new sfWidgetFormFilterInput(),
      'event_name'        => new sfWidgetFormFilterInput(),
      'sent_by'           => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'status'            => new sfWidgetFormFilterInput(),
      'manage_event_flag' => new sfWidgetFormFilterInput(),
      'command_data'      => new sfWidgetFormFilterInput(),
      'command_uuid'      => new sfWidgetFormFilterInput(),
      'sender_email'      => new sfWidgetFormFilterInput(),
      'owner_name'        => new sfWidgetFormFilterInput(),
      'note'              => new sfWidgetFormFilterInput(),
      'created_at'        => new sfWidgetFormFilterDate(array('from_date' => new sfWidgetFormDate(), 'to_date' => new sfWidgetFormDate(), 'with_empty' => false)),
      'updated_at'        => new sfWidgetFormFilterDate(array('from_date' => new sfWidgetFormDate(), 'to_date' => new sfWidgetFormDate(), 'with_empty' => false)),
    ));

    $this->setValidators(array(
      'device_id'         => new sfValidatorDoctrineChoice(array('required' => false, 'model' => $this->getRelatedModelName('DeviceInventory'), 'column' => 'id')),
      'model'             => new sfValidatorPass(array('required' => false)),
      'event_type'        => new sfValidatorPass(array('required' => false)),
      'event_name'        => new sfValidatorPass(array('required' => false)),
      'sent_by'           => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'status'            => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'manage_event_flag' => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'command_data'      => new sfValidatorPass(array('required' => false)),
      'command_uuid'      => new sfValidatorPass(array('required' => false)),
      'sender_email'      => new sfValidatorPass(array('required' => false)),
      'owner_name'        => new sfValidatorPass(array('required' => false)),
      'note'              => new sfValidatorPass(array('required' => false)),
      'created_at'        => new sfValidatorDateRange(array('required' => false, 'from_date' => new sfValidatorDateTime(array('required' => false, 'datetime_output' => 'Y-m-d 00:00:00')), 'to_date' => new sfValidatorDateTime(array('required' => false, 'datetime_output' => 'Y-m-d 23:59:59')))),
      'updated_at'        => new sfValidatorDateRange(array('required' => false, 'from_date' => new sfValidatorDateTime(array('required' => false, 'datetime_output' => 'Y-m-d 00:00:00')), 'to_date' => new sfValidatorDateTime(array('required' => false, 'datetime_output' => 'Y-m-d 23:59:59')))),
    ));

    $this->widgetSchema->setNameFormat('device_event_filters[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'DeviceEvent';
  }

  public function getFields()
  {
    return array(
      'id'                => 'Number',
      'device_id'         => 'ForeignKey',
      'model'             => 'Text',
      'event_type'        => 'Text',
      'event_name'        => 'Text',
      'sent_by'           => 'Number',
      'status'            => 'Number',
      'manage_event_flag' => 'Number',
      'command_data'      => 'Text',
      'command_uuid'      => 'Text',
      'sender_email'      => 'Text',
      'owner_name'        => 'Text',
      'note'              => 'Text',
      'created_at'        => 'Date',
      'updated_at'        => 'Date',
    );
  }
}
