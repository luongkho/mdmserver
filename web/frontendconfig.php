<?php
    //Add new file .yml config
    $configCache = new sfConfigCache($configuration);
    require_once($configCache->checkConfig('config/menu.yml'));
    require_once($configCache->checkConfig('config/message.yml'));
?>