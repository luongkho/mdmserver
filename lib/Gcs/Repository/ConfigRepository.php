<?php

/**
 * Description: Configuration service
 *  Get all information from file config
 * 
 * Modify History:
 *  September 10, 2015: cuong.xt initial version
 */

namespace Gcs\Repository;

use \Gcs\Contract\ConfigRepositoryInterface;
use \Gcs\Contract\ConfigRepositoryAbstract;

class ConfigRepository extends ConfigRepositoryAbstract {

    /**
     * Get request type by platform
     * @param string $platform
     * @return Integer 
     */
    public function getRequestTypeByPlatform($platform) {
        return $this->getValueConfigFile("app_command_request_type_data", $platform, "default");
    }

    /**
     * Get information server address
     * @return Array 
     */
    public function getServerAddress() {
        return $this->getConfigFile("app_command_request_server_add_data");
    }

    /**
     * Get command url
     * @return String
     */
    public function getCommandUrl() {
        return \sfConfig::get("app_command_url_data");
    }
    
    /**
     * Get profile type based on platform
     * @return array
     */
    public function getProfileByPlatform() {
        return \sfConfig::get("app_profileByPlatform_data");
    }
    
    /**
     * Get event status
     * @return array
     */
    public function getEventStatus() {
        return \sfConfig::get("app_event_status_data");
    }
    
    /**
     * Get location profile type from backend
     * @return array
     */
    public function getLocationType() {
        return \sfConfig::get("app_location_profile_data");
    }
    
    /**
     * Get email templates from backend
     * @return array
     */
    public function getEmailTemplate() {
        return \sfConfig::get("app_email_template_data");
    }
    
    /**
     * Get mail from
     * @return array
     */
    public function getMailFrom() {
        return \sfConfig::get("app_mail_from");
    }
    
    /**
     * Not check user authentication in these requests (frontend)
     * @return array request
     */
    public function getIgnoreRequest()  {
        return \sfConfig::get("app_ignore_request_data");
    }
    
    /**
     * Get iOS passcode setting
     * @return array setting
     */
    public function getiOSPasscodeSetting()  {
        return \sfConfig::get("app_passcode_setting_data");
    }
    
    /**
     * Get notification messages
     * @return array messages
     */
    public function getNotificationMsg()    {
        return \sfConfig::get('msg_notificationMessages_data');
    }
    
    /**
     * Get error messages
     * @return array messages
     */
    public function getErrorMsg()    {
        return \sfConfig::get('msg_errorMessages_data');
    }
    
    /**
     * Get confirm messages
     * @return array messages
     */
    public function getConfirmMsg()    {
        return \sfConfig::get('msg_confirmMessages_data');
    }
}
