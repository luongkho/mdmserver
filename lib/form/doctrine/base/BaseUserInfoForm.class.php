<?php

/**
 * UserInfo form base class.
 *
 * @method UserInfo getObject() Returns the current form's model object
 *
 * @package    mdm-server
 * @subpackage form
 * @author     Dung Huynh
 * @version    SVN: $Id: sfDoctrineFormGeneratedTemplate.php 29553 2010-05-20 14:33:00Z Kris.Wallsmith $
 */
abstract class BaseUserInfoForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'           => new sfWidgetFormInputHidden(),
      'tenant_id'    => new sfWidgetFormInputText(),
      'user_name'    => new sfWidgetFormInputText(),
      'password'     => new sfWidgetFormInputText(),
      'first_name'   => new sfWidgetFormInputText(),
      'last_name'    => new sfWidgetFormInputText(),
      'email'        => new sfWidgetFormInputText(),
      'role_id'      => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('Role'), 'add_empty' => false)),
      'status'       => new sfWidgetFormInputText(),
      'otp'          => new sfWidgetFormInputText(),
      'otp_upd_dt'   => new sfWidgetFormDateTime(),
      'last_login'   => new sfWidgetFormDateTime(),
      'phone_number' => new sfWidgetFormInputText(),
      'birthday'     => new sfWidgetFormDateTime(),
      'secret'       => new sfWidgetFormInputText(),
      'token'        => new sfWidgetFormTextarea(),
    ));

    $this->setValidators(array(
      'id'           => new sfValidatorChoice(array('choices' => array($this->getObject()->get('id')), 'empty_value' => $this->getObject()->get('id'), 'required' => false)),
      'tenant_id'    => new sfValidatorInteger(array('required' => false)),
      'user_name'    => new sfValidatorString(array('max_length' => 50)),
      'password'     => new sfValidatorString(array('max_length' => 90)),
      'first_name'   => new sfValidatorString(array('max_length' => 255, 'required' => false)),
      'last_name'    => new sfValidatorString(array('max_length' => 255, 'required' => false)),
      'email'        => new sfValidatorString(array('max_length' => 255)),
      'role_id'      => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('Role'))),
      'status'       => new sfValidatorInteger(array('required' => false)),
      'otp'          => new sfValidatorString(array('max_length' => 6, 'required' => false)),
      'otp_upd_dt'   => new sfValidatorDateTime(array('required' => false)),
      'last_login'   => new sfValidatorDateTime(array('required' => false)),
      'phone_number' => new sfValidatorString(array('max_length' => 50, 'required' => false)),
      'birthday'     => new sfValidatorDateTime(array('required' => false)),
      'secret'       => new sfValidatorString(array('max_length' => 16, 'required' => false)),
      'token'        => new sfValidatorString(array('max_length' => 500, 'required' => false)),
    ));

    $this->widgetSchema->setNameFormat('user_info[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'UserInfo';
  }

}
