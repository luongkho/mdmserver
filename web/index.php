<?php


require_once(dirname(__FILE__).'/../config/ProjectConfiguration.class.php');

$configuration = ProjectConfiguration::getApplicationConfiguration('frontend', 'prod', false);
$configuration->loadHelpers('Url');
require_once dirname(__FILE__).'/globalconfig.php';
require_once dirname(__FILE__).'/frontendconfig.php';
sfContext::createInstance($configuration)->dispatch();