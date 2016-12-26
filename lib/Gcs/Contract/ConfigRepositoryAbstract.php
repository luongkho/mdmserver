<?php

/**
 * Description: Anstract class for getting config file
 * Getting list config or config by key.
 * 
 * Modify History:
 *  September 10, 2015: cuongnd initial version
 */

namespace Gcs\Contract;

/**
 * Description of ConfigRepositoryAbstract
 *
 * @author cuongnd.xt
 */
abstract class ConfigRepositoryAbstract {

    /**
     * Get config data in all .yml files
     * @param String $configName Name of config file like "app_platforms_data"
     * @param type $defaultValue Default value if file config not exists.
     * @return Object Data in config file.
     */
    public function getConfigFile($configName, $defaultValue = null) {
        $configDatas = \sfConfig::get($configName, $defaultValue);
        return $configDatas;
    }

    /**
     * Get value in config data in all .yml files
     * @param String $configName Name of config file like "app_platforms_data"
     * @param String $key Key in config data.
     * @param type $defaultValue Default value if file config not exists.
     * @return Object Data by key in config file.
     */
    public function getValueConfigFile($configName, $key, $defaultValue = null) {
        $configDatas = \sfConfig::get($configName, $defaultValue);
        return isset($configDatas[$key]) ? $configDatas[$key] : $defaultValue;
    }

}
