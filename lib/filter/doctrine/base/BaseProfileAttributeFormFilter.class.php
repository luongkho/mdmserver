<?php

/**
 * ProfileAttribute filter form base class.
 *
 * @package    mdm-server
 * @subpackage filter
 * @author     Dung Huynh
 * @version    SVN: $Id: sfDoctrineFormFilterGeneratedTemplate.php 29570 2010-05-21 14:49:47Z Kris.Wallsmith $
 */
abstract class BaseProfileAttributeFormFilter extends BaseFormFilterDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'profile_attribute_name'     => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'profile_attribute_key'      => new sfWidgetFormFilterInput(),
      'profile_attribute_group_id' => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('ProfileAttributeGroup'), 'add_empty' => true)),
      'created_at'                 => new sfWidgetFormFilterDate(array('from_date' => new sfWidgetFormDate(), 'to_date' => new sfWidgetFormDate(), 'with_empty' => false)),
      'updated_at'                 => new sfWidgetFormFilterDate(array('from_date' => new sfWidgetFormDate(), 'to_date' => new sfWidgetFormDate(), 'with_empty' => false)),
    ));

    $this->setValidators(array(
      'profile_attribute_name'     => new sfValidatorPass(array('required' => false)),
      'profile_attribute_key'      => new sfValidatorPass(array('required' => false)),
      'profile_attribute_group_id' => new sfValidatorDoctrineChoice(array('required' => false, 'model' => $this->getRelatedModelName('ProfileAttributeGroup'), 'column' => 'id')),
      'created_at'                 => new sfValidatorDateRange(array('required' => false, 'from_date' => new sfValidatorDateTime(array('required' => false, 'datetime_output' => 'Y-m-d 00:00:00')), 'to_date' => new sfValidatorDateTime(array('required' => false, 'datetime_output' => 'Y-m-d 23:59:59')))),
      'updated_at'                 => new sfValidatorDateRange(array('required' => false, 'from_date' => new sfValidatorDateTime(array('required' => false, 'datetime_output' => 'Y-m-d 00:00:00')), 'to_date' => new sfValidatorDateTime(array('required' => false, 'datetime_output' => 'Y-m-d 23:59:59')))),
    ));

    $this->widgetSchema->setNameFormat('profile_attribute_filters[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'ProfileAttribute';
  }

  public function getFields()
  {
    return array(
      'id'                         => 'Number',
      'profile_attribute_name'     => 'Text',
      'profile_attribute_key'      => 'Text',
      'profile_attribute_group_id' => 'ForeignKey',
      'created_at'                 => 'Date',
      'updated_at'                 => 'Date',
    );
  }
}
