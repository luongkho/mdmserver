<?php


require_once(dirname(__FILE__).'/../../config/ProjectConfiguration.class.php');

$configuration = ProjectConfiguration::getApplicationConfiguration('backend', 'dev', true);
$configuration->loadHelpers('Url');
require_once dirname(dirname(__FILE__)).'/globalconfig.php';
require_once dirname(__FILE__).'/backendconfig.php';
sfContext::createInstance($configuration)->dispatch();
