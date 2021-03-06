<?php

/**
 * DeviceApplication filter form base class.
 *
 * @package    mdm-server
 * @subpackage filter
 * @author     Dung Huynh
 * @version    SVN: $Id: sfDoctrineFormFilterGeneratedTemplate.php 29570 2010-05-21 14:49:47Z Kris.Wallsmith $
 */
abstract class BaseDeviceApplicationFormFilter extends BaseFormFilterDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'device_id'  => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('DeviceInventory'), 'add_empty' => true)),
      'name'       => new sfWidgetFormFilterInput(),
      'version'    => new sfWidgetFormFilterInput(),
      'identifier' => new sfWidgetFormFilterInput(),
      'size'       => new sfWidgetFormFilterInput(),
      'created_at' => new sfWidgetFormFilterDate(array('from_date' => new sfWidgetFormDate(), 'to_date' => new sfWidgetFormDate(), 'with_empty' => false)),
      'updated_at' => new sfWidgetFormFilterDate(array('from_date' => new sfWidgetFormDate(), 'to_date' => new sfWidgetFormDate(), 'with_empty' => false)),
    ));

    $this->setValidators(array(
      'device_id'  => new sfValidatorDoctrineChoice(array('required' => false, 'model' => $this->getRelatedModelName('DeviceInventory'), 'column' => 'id')),
      'name'       => new sfValidatorPass(array('required' => false)),
      'version'    => new sfValidatorPass(array('required' => false)),
      'identifier' => new sfValidatorPass(array('required' => false)),
      'size'       => new sfValidatorPass(array('required' => false)),
      'created_at' => new sfValidatorDateRange(array('required' => false, 'from_date' => new sfValidatorDateTime(array('required' => false, 'datetime_output' => 'Y-m-d 00:00:00')), 'to_date' => new sfValidatorDateTime(array('required' => false, 'datetime_output' => 'Y-m-d 23:59:59')))),
      'updated_at' => new sfValidatorDateRange(array('required' => false, 'from_date' => new sfValidatorDateTime(array('required' => false, 'datetime_output' => 'Y-m-d 00:00:00')), 'to_date' => new sfValidatorDateTime(array('required' => false, 'datetime_output' => 'Y-m-d 23:59:59')))),
    ));

    $this->widgetSchema->setNameFormat('device_application_filters[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'DeviceApplication';
  }

  public function getFields()
  {
    return array(
      'id'         => 'Number',
      'device_id'  => 'ForeignKey',
      'name'       => 'Text',
      'version'    => 'Text',
      'identifier' => 'Text',
      'size'       => 'Text',
      'created_at' => 'Date',
      'updated_at' => 'Date',
    );
  }
}
