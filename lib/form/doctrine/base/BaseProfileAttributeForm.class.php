<?php

/**
 * ProfileAttribute form base class.
 *
 * @method ProfileAttribute getObject() Returns the current form's model object
 *
 * @package    mdm-server
 * @subpackage form
 * @author     Dung Huynh
 * @version    SVN: $Id: sfDoctrineFormGeneratedTemplate.php 29553 2010-05-20 14:33:00Z Kris.Wallsmith $
 */
abstract class BaseProfileAttributeForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'                         => new sfWidgetFormInputHidden(),
      'profile_attribute_name'     => new sfWidgetFormInputText(),
      'profile_attribute_key'      => new sfWidgetFormInputText(),
      'profile_attribute_group_id' => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('ProfileAttributeGroup'), 'add_empty' => false)),
      'created_at'                 => new sfWidgetFormDateTime(),
      'updated_at'                 => new sfWidgetFormDateTime(),
    ));

    $this->setValidators(array(
      'id'                         => new sfValidatorChoice(array('choices' => array($this->getObject()->get('id')), 'empty_value' => $this->getObject()->get('id'), 'required' => false)),
      'profile_attribute_name'     => new sfValidatorString(array('max_length' => 250)),
      'profile_attribute_key'      => new sfValidatorString(array('max_length' => 250, 'required' => false)),
      'profile_attribute_group_id' => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('ProfileAttributeGroup'))),
      'created_at'                 => new sfValidatorDateTime(),
      'updated_at'                 => new sfValidatorDateTime(),
    ));

    $this->widgetSchema->setNameFormat('profile_attribute[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'ProfileAttribute';
  }

}
