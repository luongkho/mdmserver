<?php

/**
 * Description: List device Service 
 * Get, set, sort , group device list
 * Modify History:
 *  September 10, 2015: tannc initial version
 */

namespace Gcs\Repository;

use Gcs\Contract\DeviceRepositoryInterface;

class DeviceRepository implements DeviceRepositoryInterface
{

    const ANDROID_PLATFORM = 1;
    const IOS_PLATFORM = 2;
    const WP_PLATFORM = 3;
    const ACTION_WIPE = 'wipe_device';
    const ACTION_GET_LOG = 'get_log_information';
    const ACTION_GET_LATEST_LOCATION = 'get_latest_location';
    const ANDROID_NAME = 'android';
    const IOS_NAME = 'ios';
    const IOS_APP_NAME = 'iosapp';
    const WP_NAME = 'wp';
    const WP_APP_NAME = 'wpapp';
    const LOCATION_PROFILE_TYPE = 3;
    const WP_EXPIRE_OTP = 0;
    const REENROLL_BEFORE = "E4006";
    const REENROLL_SUCCEED = "N4002";

    /**
     * All device information.
     *
     * @var array
     */
    private $attributes = array();

    /**
     * update time for each group (by group name)
     *
     * @var array
     */
    private $updatesTime = array();

    /**
     * re enroll of device.
     *
     * @param int $device_id [description]
     *
     * @return [type] [description]
     */
    public function re_enroll($device_id)
    {
        $result = array('error' => array('status' => 1, 'msg' => ''), 'data' => array());

        $device_ins = \DeviceInventoryTable::getInstance();
        $device = $device_ins->find($device_id);
        if ($device->getEnrollStatus() == 2) {
            $result['error']['msg'] = self::REENROLL_BEFORE;
        } else {
            $device->setEnrollStatus(2);
            $device->save();

            $deviceEvents = \DeviceEventTable::getInstance()->findByDeviceIdAndStatus($device_id, 0);

            $deviceEventRes = new DeviceEventRepository();
            foreach ($deviceEvents as $DeviceEvent) {
                $deviceEventRes->updateStatus($DeviceEvent, 3);
            }

//      $OtpRes = new OTPRepository();
//      $OtpRes->updateOTPAndExpired($device->getUdid());

            $result['error']['status'] = 0;
            $result['msg'] = self::REENROLL_SUCCEED;
        }

        return $result;
    }

    /**
     * set attribute information of device
     *
     * @param int $device_id [description]
     */
    public function setDeviveInformation($device_id)
    {
        $inventoryInformationTable = \InventoryInformationTable::getInstance();
        $inventoryInformations = $inventoryInformationTable->findBy('device_id', $device_id);
        foreach ($inventoryInformations as $key => $item) {
            $this->updatesTime[$item->getInventoryAttributeGroup()->getName()] = $item->updated_at;
            $this->attributes = array_merge($this->attributes, unserialize($item->getValue()));
        }
        if (!empty($this->attributes)) {
            $this->attributes['storage_free_total'] = (!empty($this->attributes['available_device_capacity']) ? $this->attributes['available_device_capacity'] : 0) . "/" . (!empty($this->attributes['device_capacity']) ? $this->attributes['device_capacity'] : 0);
        }
    }

    /**
     * get Owner of device.
     *
     * @param string $device_id [description]
     *
     * @return [type] [description]
     */
    public function getOwner($device_id = '')
    {
        $ownerTable = \DeviceInventoryTable::getInstance();
        $owner = $ownerTable->findOneBy('id', $device_id);
        if (!$owner) {
            return 'N/A';
        } else {
            return $owner->getOwnerName();
        }
    }

    /**
     * List all devices of user.
     *
     * @return array [description]
     */
    public function list_all_device($request, $platform, $deviceStatus)
    {
        $columns = array(
            array('db' => 'd.id', 'dt' => 0),
            array('db' => 'd.id', 'dt' => 1),
            array('db' => 'd.enroll_status', 'dt' => 2, 'int_search' => array(
                    'type' => 'config',
                    'name' => 'app_enroll_statuses_data'
                )),
            array('db' => 'd.device_name', 'dt' => 3, 'is_search' => true),
            array('db' => 'd.version', 'dt' => 4, 'is_search' => true),
            array('db' => 'd.owner_name', 'dt' => 5, 'is_search' => true),
            array(
                'db' => 'd.updated_at',
                'dt' => 6,
                'formatter' => function ($d, $row) {
            return date('jS M y', strtotime($d));
        },
            ),
            array(
                'db' => 'd.imei',
                'dt' => 7,
            ),
            array(
                'db' => 'd.wifi_mac_address',
                'dt' => 8,
            ),
            array(
                'db' => 'd.platform', 'dt' => 9
            )
        );

        // Get platform and enroll_status in app.yml
        $platformConfig = \sfConfig::get("app_platforms_data");
        $enrollStatus = \sfConfig::get("app_enroll_statuses_data");
        if (!array_key_exists($platform, $platformConfig)) {
            $platform = NULL;
        }
        if (!array_key_exists($deviceStatus, $enrollStatus)) {
            $deviceStatus = NULL;
        }

        $limit = \SSP::limit($request);
        $order = \SSP::order($request, $columns);
        $where = \SSP::filter($request, $columns);
        $whereInt = \SSP::filter_integer($request, $columns);
        $query = \DeviceInventoryTable::getInstance()->createQuery('d');

        if (!$where) {
            if ($platform !== NULL) {
                $query = $query->andwhere('d.platform = ?', intval($platform));
            }
            if ($deviceStatus !== NULL) {
                $query = $query->andwhere('d.enroll_status = ?', intval($deviceStatus));
            }
        }

        $query = $query->limit($limit['limit'])->offset($limit['offset']);

        foreach ($order as $orderBy) {
            $query = $query->orderBy($orderBy);
        }

        foreach ($where as $key => $val) {
            $query = $query->orWhere($key . ' ILIKE ?', '%' . $val . '%');
            if ($platform !== NULL) {
                $query->andWhere('d.platform = ?', intval($platform));
            }
            if ($deviceStatus !== NULL) {
                $query->andWhere('d.enroll_status = ?', intval($deviceStatus));
            }
        }
        foreach ($whereInt as $key => $value) {
            $query = $query->orWhereIn($key, $value);
            if ($platform !== NULL) {
                $query->andWhere('d.platform = ?', intval($platform));
            }
            if ($deviceStatus !== NULL) {
                $query->andWhere('d.enroll_status = ?', intval($deviceStatus));
            }
        }

        return array('result' => $query->execute(), 'count' => $query->count());
    }

    /**
     * List all devices of user.
     *
     * @param int $user_id [description]
     *
     * @return array [description]
     */
    public function list_device($user_id)
    {
        return $this->_searchByUser($user_id);
    }

    /**
     * search all device of user.
     *
     * @param [type] $user_id [description]
     *
     * @return [type] [description]
     */
    public function _searchByUser($user_id)
    {
        $deviceInventoryTable = \DeviceInventoryTable::getInstance();

        return $deviceInventoryTable->findBy('user_id', $user_id);
    }

    /**
     * get Value of device by slug.
     *
     * @param [type] $slug [description]
     *
     * @return [type] [description]
     */
    public function getValueByAttributeName($slug)
    {
        if (isset($this->attributes[$slug])) {
            $attr = strval($this->attributes[$slug]);
            switch (true) {
                case $attr === '1':
                    return 'Enable';
                case $attr === '0':
                    return 'Disable';
                default:
                    return $this->attributes[$slug];
            }
        } else {
            return '-';
        }
    }

    /**
     * [getByteValue description]
     * @param  [type]  $bytes      [description]
     * @param  string  $unit       [description]
     * @param  integer $decimals   [description]
     * @param  boolean $has_format [description]
     * @return [type]              [description]
     */
    public function getByteValue($bytes, $unit = "", $decimals = 2, $has_format = false)
    {
        $units = array('B' => 0, 'KB' => 1, 'MB' => 2, 'GB' => 3, 'TB' => 4,
            'PB' => 5, 'EB' => 6, 'ZB' => 7, 'YB' => 8);
        $unit = strtoupper($unit);
        $value = 0;
        if ($bytes > 0) {
            // Generate automatic prefix by bytes 
            // If wrong prefix given
            if (!array_key_exists($unit, $units)) {
                $pow = floor(log($bytes) / log(1024));
                $unit = array_search($pow, $units);
            }

            // Calculate byte value by prefix
            $value = ($bytes * pow(1024, floor($units[$unit])));
        }

        // If decimals is not numeric or decimals is less than 0 
        // then set default value
        if (!is_numeric($decimals) || $decimals < 0) {
            $decimals = 2;
        }

        // Format output
        if ($has_format)
            return sprintf('%.' . $decimals . 'f ' . $unit, $value);
        else
            return sprintf('%.' . $decimals . 'f ', $value);
    }

    /**
     * [getByteFormatValue description]
     * @param  [type]  $bytes      [description]
     * @param  string  $unit       [description]
     * @param  integer $decimals   [description]
     * @param  boolean $has_format [description]
     * @return [type]              [description]
     */
    public function getByteFormatValue($bytes, $unit = "", $decimals = 2, $has_format = false)
    {
        $units = array('B' => 0, 'KB' => 1, 'MB' => 2, 'GB' => 3, 'TB' => 4,
            'PB' => 5, 'EB' => 6, 'ZB' => 7, 'YB' => 8);
        $unit = strtoupper($unit);
        $value = 0;
        if ($bytes > 0) {
            // Generate automatic prefix by bytes 
            // If wrong prefix given
            if (!array_key_exists($unit, $units)) {
                $pow = floor(log($bytes) / log(1024));
                $unit = array_search($pow, $units);
            }

            // Calculate byte value by prefix
            $value = ($bytes / pow(1024, floor($units[$unit])));
        }

        // If decimals is not numeric or decimals is less than 0 
        // then set default value
        if (!is_numeric($decimals) || $decimals < 0) {
            $decimals = 2;
        }

        // Format output
        if ($has_format)
            return sprintf('%.' . $decimals . 'f ' . $unit, $value);
        else
            return sprintf('%.' . $decimals . 'f ', $value);
    }

    /**
     * get update time by group id.
     *
     * @param int $group_id [description]
     *
     * @return [type] [description]
     */
    public function getUpdateTimeOfGroup($groupName)
    {
        if (isset($this->updatesTime[$groupName])) {
            return $this->updatesTime[$groupName];
        } else {
            return 'N/A';
        }
    }

    /**
     * get list device to dashboard page.
     * @param type $platform
     * @param type $enroll_status
     * @return [array] $message
     */
    public function getDeviceForDashBoard($platform = null, $enroll_status = null)
    {
        $query = \DeviceInventoryTable::getInstance()->createQuery('d');
        // $query = $query->where(1);
        if ($platform !== null) {
            $query = $query->andWhere('platform = ?', $platform);
        }
        if ($enroll_status !== null) {
            $query = $query->andWhere('enroll_status = ?', $enroll_status);
        }
        return array('result' => $query->execute(), 'count' => $query->count());
    }

    /**
     * get device events.
     *
     * @param int $group_id [description]
     *
     * @return [type] [description]
     */
    public function device_info()
    {
        $userModel = \InventoryInformationTable::getInstance();

        return $userModel->findAll();
    }

    /**
     * get list event of all each device
     * @param type $request
     * @param type $deviceId
     * @return [array] $events
     */
    public function list_device_events($request, $deviceId)
    {
        if ($deviceId != NULL) {
            $query = \DeviceEventTable::getInstance()->createQuery('d')->where('d.manage_event_flag = ?', 1)
                            ->andWhere('d.device_id = ?', $deviceId)->orderBy('d.updated_at DESC');
            return $query->execute();
        }
        $columns = array(
            array('db' => 'd.status', 'dt' => 0, 'int_search' => array(
                    'type' => 'config',
                    'name' => 'app_event_status_data'
                )),
            array('db' => 'd.model', 'dt' => 1),
            array('db' => 'd.event_type', 'dt' => 2, 'is_search' => true),
            array('db' => 'd.event_name', 'dt' => 3, 'int_search' => array(
                    'type' => 'config',
                    'name' => 'app_event_name_data'
                )),
            array('db' => 'd.owner_name', 'dt' => 4, 'is_search' => true),
            array('db' => 'd.sender_email', 'dt' => 5, 'is_search' => true),
            array(
                'db' => 'd.updated_at',
                'dt' => 6,
                'formatter' => function ($d, $row) {
            return date('jS M y', strtotime($d));
        },
            ),
            array('db' => 'd.device_id', 'dt' => 7, 'int_search' => array(
                    'type' => 'foreign',
                    'name' => 'DeviceInventoryTable',
                    'select' => 'id',
                    'where' => 'product_name'
                )),
            array('db' => 'd.device_id', 'dt' => 8)
        );

        $limit = \SSP::limit($request);
        $order = \SSP::order($request, $columns);
        $where = \SSP::filter($request, $columns);
        $whereInt = \SSP::filter_integer($request, $columns);
        if (!$where) {
            $query = \DeviceEventTable::getInstance()->createQuery('d')->where('d.manage_event_flag = ?', 1);
        } else {
            $query = \DeviceEventTable::getInstance()->createQuery('d');
        }
        $query = $query->limit($limit['limit'])->offset($limit['offset']);
        foreach ($order as $orderBy) {
            $query = $query->orderBy($orderBy);
        }
        foreach ($where as $key => $val) {
            $query = $query->orWhere($key . ' ILIKE ?', '%' . $val . '%')->andWhere('d.manage_event_flag = ?', 1);
        }
        foreach ($whereInt as $key => $value) {
            $query = $query->orWhereIn($key, $value)->andWhere('d.manage_event_flag = ?', 1);
        }

        return array('result' => $query->execute(), 'count' => $query->count());
    }

    /**
     * get Day left of Waranty
     * if $today > $warrantyDate then $invert = 1 else $invert = 0
     * @param Datetime $warantyStart
     * @param DateTime $warantyEnd
     * @return Integer
     */
    public function getDayLeft($warantyEnd)
    {
        $dayLeft = 0;
        if (!empty($warantyEnd)) {
            $today = date_create(date("Y-m-d", time()));
            $warrantyDate = date_create(date("Y-m-d", strtotime($warantyEnd)));
            $interval = date_diff($today, $warrantyDate);
            $invert = $interval->invert;
            if (!$invert) {
                $dayLeft = $interval->days;
            }
        }
        return $dayLeft;
    }

    /**
     * get warranty status of device owner
     * @param type $daysLeft
     * @return [date]
     */
    public function getWarrantyStatus($daysLeft)
    {
        $warranty_statuses = \sfConfig::get("app_user_statuses_data");
        $warranty_statuse = $daysLeft > 0 ? 1 : 0;
        return empty($warranty_statuses[$warranty_statuse]) ? 'Undefined' : $warranty_statuses[$warranty_statuse];
    }

    /**
     * get Percent of Warranty
     * @param Datetime $PurchaseDate
     * @param DateTime $WarrantyEnd
     * @return float
     */
    public function getPercentWarranty($PurchaseDate, $WarrantyEnd)
    {
        $percentWarranty = strtotime($WarrantyEnd) - strtotime($PurchaseDate);
        if ($percentWarranty != 0) {
            $useWarranty = time() - strtotime($PurchaseDate);
            if ($useWarranty <= 0)
                $percentWarranty = 0;
            elseif ($useWarranty >= $percentWarranty)
                $percentWarranty = 100;
            else
                $percentWarranty = $useWarranty * 100 / $percentWarranty;
        }
        return $percentWarranty;
    }

    /**
     * get device inventory based on device id
     * @param type $deviceId
     * @return [DeviceInventory]$device
     */
    public function getDevice($deviceId)
    {
        $table = \DeviceInventoryTable::getInstance();
        $query = $table->findOneById($deviceId);
        return $query;
    }

    /**
     * Change platform to string
     * @param  [integer] $platform [Platform by int]
     * @return [string] $platform  [Platform by string]
     */
    public function getPlatformString($platform, $actionType)
    {
        switch (intval($platform)) {
            case self::ANDROID_PLATFORM:
                $platform = self::ANDROID_NAME;
                break;
            case self::IOS_PLATFORM:
                if ($actionType == self::LOCATION_PROFILE_TYPE || $actionType == self::ACTION_WIPE || $actionType == self::ACTION_GET_LOG) {
                    $platform = self::IOS_APP_NAME;
                } else {
                    $platform = self::IOS_NAME;
                }
                break;
            case self::WP_PLATFORM:
                if ($actionType == self::LOCATION_PROFILE_TYPE || $actionType == self::ACTION_WIPE ||
                        $actionType == self::ACTION_GET_LOG || $actionType == self::ACTION_GET_LATEST_LOCATION) {
                    $platform = self::WP_APP_NAME;
                } else {
                    $platform = self::WP_NAME;
                }
                break;
            default :
                break;
        }
        return $platform;
    }

    /**
     * Get device form list ID
     * @param  [array] $deviceIds
     * @return [object]
     */
    public function getDeviceList($deviceIds)
    {
        $table = \DeviceInventoryTable::getInstance();
        $query = $table->createQuery('d')->whereIn('d.id', $deviceIds);
        return $query->execute();
    }

}
