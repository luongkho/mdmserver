<?php

/**
 * DeviceProfile
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @package    mdm-server
 * @subpackage model
 * @author     Dung Huynh
 * @version    SVN: $Id: Builder.php 7490 2010-03-29 19:53:27Z jwage $
 */
class DeviceProfile extends BaseDeviceProfile
{

  public function getConfigTypeName()
  {
    $name = \sfConfig::get("app_configuration_type_data");
    $status = $this->getConfigurationType();
    return empty($name[$status]) ? 'Undefined' : $name[$status];
  }

}