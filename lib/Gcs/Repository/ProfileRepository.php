<?php
/**
 * Description: class for Profiles service
 * Getting profiles, update, delete profile.
 * 
 * Modify History:
 *  September 10, 2015: cuongnd initial version
 */
namespace Gcs\Repository;

class ProfileRepository
{
  const ERR_NOT_FOUND_GROUP = 'E2003';

 /**
  * Get all profile in database
  * @param Array $request
  * @return Array
  */
  public function get_profile($request)
  {
    $columns = array(
        array('db' => 'd.id', 'dt' => 0),
        array('db' => 'd.platform', 'dt' => 1, 'int_search' => array(
                'type' => 'config',
                'name' => 'app_platforms_data'
            )),
        array('db' => 'd.profile_name', 'dt' => 2, 'is_search' => true),
        array('db' => 'd.configuration_type', 'dt' => 3, 'int_search' => array(
                'type' => 'config',
                'name' => 'app_configuration_type_data'
            )),
        array('db' => 'd.configuration_type', 'dt' => 4, 'int_search' => array(
                'type' => 'config',
                'name' => 'app_configuration_type_data'
            )),
        array('db' => 'd.platform', 'dt' => 5, 'int_search' => array(
                'type' => 'config',
                'name' => 'app_platforms_data'
            )),
        array('db' => 'd.description', 'dt' => 6, 'is_search' => true),
        array(
            'db' => 'd.updated_at',
            'dt' => 7,
            'formatter' => function ($d, $row) {
      return date('jS M y', strtotime($d));
    },
        )
    );

    $limit = \SSP::limit($request);
    $order = \SSP::order($request, $columns);
    $where = \SSP::filter($request, $columns);
    $whereInt = \SSP::filter_integer($request, $columns);
    $query = \ProfileTable::getInstance()->createQuery('d');
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
   * Add new profile
   * @param Array $request
   * @param Array $data
   * @return Object
   */
  function add_profile($request, $data)
  {
    $new = !($request->getParameter('id'));
    if ($new) {
      $profile = new \Profile();
      $profile->setConfigurationType($request->getParameter('configuration_type'))
              ->setPlatform($request->getParameter('platform'));
      $profileInfo = new \ProfileInformation();
    } else {
      $profile = $this->getProfileById($request->getParameter('id'));
      $profileInfo = $this->getProfileInforByProfileId($profile->getId());
    }

    $profile->setProfileName($request->getParameter('profile_name'))
            ->setDescription($request->getParameter('description'));
    $profile->save();
    
    $profileInfo->setProfileAttributeGroupId($request->getParameter('configuration_type'))
            ->setProfileId($profile->getId())
            ->setValue(serialize($data));
    $profileInfo->save();
    return $profile;
  }

    /*
     * Delete Profile
     * @param [integer] Profile Id
     */

    public function deleteProfile($profileId)
    {
        // Delete from Profile_information
        $profileInfor = $this->getProfileInforByProfileId($profileId);
        $delete = $profileInfor->delete();

        if ($delete)    {
            // Delete from Profile
            $profile = $this->getProfileById($profileId);
            $delete = $profile->delete();
        } else {
            return FALSE;
        }
        return $delete;
    }

  /*
   * Find profile by Id
   * $param [integer] Profile Id
   * @return [object] Instance if found
   */
  public function getProfileById($profileId)
  {
    $table = \ProfileTable::getInstance();
    return $table->findOneById($profileId);
  }
  
  /*
   * Find profile information by Id
   * $param [integer] Profile Id
   * @return [object] Instance if found
   */
  public function getProfileInforByProfileId($profileId)
  {
    $table = \ProfileInformationTable::getInstance();
    return $table->findOneByProfileId($profileId);
  }
  
  /*
   * Get profile information
   * $param [integer] Profile Id
   * @return [array] Profile information and profile detail
   */
  public function getProfileAllInfo($profileId) {
      $profile = $this->getProfileById($profileId);
      $profileInfo = $this->getProfileInforByProfileId($profileId);
      $result = array();
      $result['profile'] = $profile;
      $result['value'] = unserialize($profileInfo->getValue());
      return $result;
  }
  
 
   /**
    * Get payload indentifier in config file
    * @param String $configurationType
    * @return String
    */
  public function getPayloadIdentifier($configurationType){
     
    $indentifier = \sfConfig::get("app_payload_identifier_data");
    return empty($indentifier[$configurationType]) ? $indentifier[1] : $indentifier[$configurationType];
  }
  
  /**
   * Get passcode policy in config file
   * @param String $policyKey
   * @return String
   */
  public function getPasscodePolicyPayload($policyKey){
     
    $passcodePolicyPayload = \sfConfig::get("app_passcode_policy_payload_data");
    return empty($passcodePolicyPayload[$policyKey]) ? false : $passcodePolicyPayload[$policyKey];
  }

  /**
   * Get profile IOS platform by profile id
   * @param Integer $profileId
   * @return Array
   */
  public function getProfileIOSByProfileId($profileId){
    
        $profile = $this->getProfileById($profileId);
        $profileInfoTable = \ProfileInformationTable::getInstance();
        $profileInfo = $profileInfoTable->findOneByProfileId($profileId);
        $profileData = unserialize($profileInfo->getValue());
        
        $result = array(
            'profile_name'              => $profile->getProfileName(),
            'description'               => $profile->getDescription(),
            'platform'                  => $profile->getPlatform(),
            'configuration_type'        => $profile->getConfigurationType(),
            'profileData'               => $profileData,
            'profileDataId'             => $profileInfo->getId()
        );

        return $result;
    }

  /**
   * Remove profile by device id and profile id
   * @param Integer $deviceId
   * @param Integer $profileId
   * @return boolean
   */

  function removeProfile($deviceId, $profileId)
  {
    // Get Device_Profile
    $deviceProfile = \DeviceProfileTable::getInstance();
    $instance = $deviceProfile->createQuery('p')
            ->where('p.profile_id = ?', $profileId)
            ->andwhere('p.device_id = ?', $deviceId)
            ->execute();
    if (!$instance) {
      return FALSE;
    }

    $delete = $instance->delete();
    if (!$delete) {
      return FALSE;
    }
    return true;
  }

  /**
   * Save new instance in table device_profile. Remove unuse
   * @param Array $data
   * @param Object $device
   */

  public function updateDeviceProfile($data, $device)
  {
    $profileId = $data[0];
    $deviceProfile = \DeviceProfileTable::getInstance()
            ->findOneByDeviceIdAndConfigurationType($device->getId(), end($data));
    if (!$deviceProfile) {
      $deviceProfile = new \DeviceProfile();
    }
    $deviceProfile->setDeviceId($device->getId());
    $deviceProfile->setProfileId($data[0]);
    $deviceProfile->setProfileName($data[1]);
    $deviceProfile->setPlatform($data[2]);
    $deviceProfile->setConfigurationType($data[3]);
    $time = date('Y-m-d H:i:s', time());
    $deviceProfile->setUpdatedAt($time);
    $deviceProfile->save();
  }
  
  /*
   * get all attributes of a configuration type
   * @param $configType: type Id of configuration type
   * @return attributes of group
   */
  public function getAttribute($configType)    {
      $attributes = \ProfileAttributeTable::getInstance()
              ->findBy('profile_attribute_group_id', intval($configType));
      if (!$attributes) {
          return self::ERR_NOT_FOUND_GROUP;
      }
      return $attributes;
  }

}
