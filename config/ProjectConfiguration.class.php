<?php
require __DIR__ .'/../lib/vendor/autoload.php';

class ProjectConfiguration extends sfProjectConfiguration
{
  public function setup()
  {
    $this->enablePlugins('sfDoctrinePlugin');
    $this->_initLog4PHP();
  }
  
 /**
   * Configure include path for autoloading log4php library
   */
  private function _initLog4PHP()
  {
    set_include_path(sfConfig::get('sf_lib_dir')
      . '/vendor/log4php/src/main/php'
      . PATH_SEPARATOR
      . get_include_path());
    require_once 'Logger.php';
  }
}
