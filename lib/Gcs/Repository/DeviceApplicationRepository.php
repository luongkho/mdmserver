<?php

/**
 * Description: Device application service
 *  insert, update, delete applicaitons of each device
 *  
 * Modify History:
 *  September 10, 2015: cuong.xt initial version
 */

namespace Gcs\Repository;
use Gcs\Repository\DeviceInventoryRepository;

class DeviceApplicationRepository
{
    const IOS_PLATFORM = 2;

  /**
   * get all application of device.
   *
   * @param [object] $device, [array] $queryData
   *
   * @return [type] [description]
   */
  public function updateInstalledApp($device, $queryData)
  {
    $deviceId = $device->getId();
    $installedApps = \DeviceApplicationTable::getInstance()->findByDeviceId($deviceId);
    $flagInstallApp = $this->compareInstallApps($installedApps, $queryData, $device);
    if(!$flagInstallApp){
        $isDelete = true;
        if ($installedApps) {
          $isDelete = $installedApps->delete();
        }
        if ($isDelete) {
          $this->saveInstalledApp($device, $queryData);
        }
    }
  }
  /**
   * build response apps then using compare app.
   *
   * @param [array] $responseApps, [String] $platform
   *
   * @return [array] [New format array response apps]
   */
    private function buildResponseApps($responseApps, $platform){
        $newResponseApps = array();
        foreach ($responseApps as $app) {
            $Name = "";
            $BundleSize = 0;
            $Identifier = "";
            $Version = "";
            if (isset($app['BundleSize'])) {
                if ($platform == self::IOS_PLATFORM) {
                    $app['BundleSize'] = $app['BundleSize'] / pow(1024, 2);
                    $app['BundleSize'] = number_format((float) $app['BundleSize'], 2, '.', '');
                }
                $BundleSize = $app['BundleSize'];
            }
            if (isset($app['Name'])) {
                $Name = $app['Name'];
            }
            $newResponseApps[$Name] = array(
                "Name"          => $Name,
                "BundleSize"    => $BundleSize,
                "Identifier"    => isset($app['Identifier']) ? $app['Identifier'] : "",
                "Version"       => isset($app['Version']) ? $app['Version'] : "",
            );
        }
        return $newResponseApps;
    }

  /**
   * Compare install apps before install apps.
   *
   * @param [Object] $installedApps, [Array] $responseApps, [Object] $device
   *
   * @return [Boolean] [Flag boolean install new apps]
   */
    private function compareInstallApps($installedApps, $responseApps, $device) {
        $flag     = true;
        $platform = $device->getPlatform();
        if (count($installedApps) == count($responseApps)) {
            $responseApps = $this->buildResponseApps($responseApps, $platform);
            foreach ($installedApps as $apps) {
                if (isset($responseApps[$apps->getName()])) {
                    $responseAppsName = $responseApps[$apps->getName()];
                    if ($apps->getName() != $responseAppsName["Name"] ||
                            $apps->getSize() != $responseAppsName["BundleSize"] ||
                            $apps->getIdentifier() != $responseAppsName["Identifier"] ||
                            $apps->getVersion() != $responseAppsName["Version"]) {
                        $flag = false;
                        break;
                    } else {
                        unset($responseApps[$apps->getName()]);
                    }
                } else {
                    $flag = false;
                    break;
                }
            }
        } else {
            $flag = false;
        }
        return $flag;
    }

    /**
   * insert installed app list into DeviceApplication table
   *
   * @param [object] $device, [array] $queryData
   *
   * @return [type] [description]
   */
  public function saveInstalledApp($device, $queryData)
  {
      $deviceId = $device->getId();
      $platform = $device->getPlatform();
//    echo json_encode($queryData);
//    exit;
//    if (isset($queryData['Array'])) {
//      $appList = $queryData['Array'];
      foreach ($queryData as $app) {
        $deviceApp = new \DeviceApplication();
        $deviceApp->setDeviceId($deviceId);
        if (isset($app['BundleSize'])) {
          if ($platform == self::IOS_PLATFORM)    {
              $app['BundleSize'] = $app['BundleSize'] / pow(1024, 2);
              $app['BundleSize'] = number_format((float)$app['BundleSize'], 2, '.', '');
          }
          $deviceApp->setSize($app['BundleSize']);
        }
        if (isset($app['Identifier'])) {
          $deviceApp->setIdentifier($app['Identifier']);
        }
        if (isset($app['Name'])) {
           $deviceApp->setName($app['Name']);
        }
        if (isset($app['Version'])) {
           $deviceApp->setVersion($app['Version']);
        }
        $deviceApp->save();
      }
      
//      $deviceInventoryRepository = new DeviceInventoryRepository();
//      $deviceInventoryRepository ->setUpdateAtDeviceInventory($deviceId);
//    }
  }
  
  /** 
   * get latest updated date of application list
   * @param [integer] Device Id
   * @return [String] Date format or FALSE if not exist
   */
  public function latestAppUpdated($deviceId)
  {
    $table = \DeviceApplicationTable::getInstance();
    $latestDate = $table->createQuery('d')->where('d.device_id = ?', $deviceId)
            ->orderBy('d.updated_at desc')->fetchOne();
    if (!$latestDate)   {
        return FALSE;
    }
    return date('Y-m-d H:i:s', strtotime($latestDate->getUpdatedAt()));
  }

}
