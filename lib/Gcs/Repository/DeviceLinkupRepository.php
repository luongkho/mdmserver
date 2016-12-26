<?php

/**
 * Description: Device Linkup service
 * Get, Update software version of each platform
 * Get, Convert name of each platform 
 * Modify History:
 *  September 10, 2015: cuongnd.xt initial version
 */

namespace Gcs\Repository;

class DeviceLinkupRepository
{

    /**
     * get platform of device based on keyword
     * @param [string] $keyword
     * @return [int] $platform
     */
    public function getPlatformByKeyword($keyword)
    {
        $platforms = \sfConfig::get("app_platform_keyword_id_mcenter_data");
        return empty($platforms[$keyword]) ? 1 : $platforms[$keyword];
    }

    /**
     * get name of platform based on id
     * @param type $platformId
     * @return type
     */
    public function getPlatformNameById($platformId)
    {
        $name = \sfConfig::get("app_platforms_data");
        return empty($name[$platformId]) ? 'Undefined' : $name[$platformId];
    }

    /**
     * get platform extension by id
     * @param type $platformId
     * @return type
     */
    public function getPlatformExtensionById($platformId)
    {
        $name = \sfConfig::get("app_platform_extensions_data");
        return empty($name[$platformId]) ? 'Undefined' : $name[$platformId];
    }

    /**
     * get application folder
     * @return type
     */
    public function getApplicationFolder()
    {
        return \sfConfig::get("app_application_folder_upload_dir");
    }

    /**
     * get file name of application
     * @return type
     */
    public function getApplicationFileName()
    {
        return \sfConfig::get("app_application_file_name_data");
    }

    /**
     * get software version from file name
     * @param type $fileName
     * @return type
     */
    public function getSoftwareVersionByFileName($fileName)
    {
        $structure = $this->getApplicationFileName();
        $structure = $structure['structure']['name'];
        return preg_replace($structure, "", $fileName);
    }

    /**
     * check structure of file name
     * @param type $fileName
     * @return boolean
     */
    public function invalidStructure($fileName)
    {
        $structure = $this->getApplicationFileName();
        $structure = $structure['structure']['full'];
        if (preg_match($structure, $fileName)) {
            return false;
        }
        return true;
    }

    /**
     * check extension of file
     * @param type $platformId
     * @param type $extension
     * @return boolean
     */
    public function invalidFileExtension($platformId, $extension)
    {
        $platformExtension = $this->getPlatformExtensionById($platformId);
        if ($platformExtension != $extension) {
            return true;
        }
        return false;
    }

    /**
     * get software version of keyword
     * @param type $keyword
     * @return type
     */
    public function getSoftwareVersionByKeyword($keyword)
    {
        return \ConfigurationTable::getInstance()->findOneBy("config_key", $keyword);
    }

    /** 
     * get root of directory
     * @return type
     */
    public function getRootDir()
    {
        return \sfConfig::get('sf_web_dir');
    }

    /**
     * get list version of each platform
     * @param type $request
     * @return type
     */
    public function listVersion($request)
    {
        $columns = array(
            array('db' => 'd.config_key', 'dt' => 0),
            array('db' => 'd.config_key', 'dt' => 1, 'int_search' => array(
                    'type' => 'config',
                    'name' => 'app_platform_keyword_name_mcenter_data'
                )),
            array('db' => 'd.config_val', 'dt' => 2, 'is_search' => true),
        );
        $limit = \SSP::limit($request);
        $order = \SSP::order($request, $columns);
        $where = \SSP::filter($request, $columns);
        $whereInt = \SSP::filter_integer($request, $columns);

        $query = \ConfigurationTable::getInstance()->createQuery('d');

        $query = $query->limit($limit['limit'])->offset($limit['offset']);

        foreach ($order as $orderBy) {
            $query = $query->orderBy($orderBy);
        }

        foreach ($where as $key => $val) {
            $query = $query->orWhere($key . ' ILIKE ?', '%' . $val . '%');
        }
        foreach ($whereInt as $key => $value) {
            $query = $query->orWhereIn($key, $value);
        }
        return array('result' => $query->execute(), 'count' => $query->count());
    }

    /**
     * update software version
     * @param type $keyword
     * @param type $version
     */
    public function updateSoftwareVersion($keyword, $version)
    {
        $software = $this->getSoftwareVersionByKeyword($keyword);
        $software->setConfigVal($version);
        $software->save();
    }

}
