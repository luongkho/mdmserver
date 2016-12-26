<?php

/**
 * login actions.
 *
 * @package    mdm-server
 * @subpackage login
 * @author     Dung Huynh
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
use Gcs\Repository\UserRepository;
use Gcs\Repository\DeviceRepository;
use Gcs\Repository\TemplateRepository;
use Gcs\Repository\ProfileRepository;
use Gcs\Repository\LocationRepository;
use Gcs\Repository\ConfigRepository;

class authActions extends sfActions {
    
    const PASSCODE_SIMPLE_VALUE = "pc_simple_value";
    const PASSCODE_ALPHABETIC = "pc_alphanum";
    
    const ERR_EMAIL_EXIST               = "E101";
    const ERR_USERNAME_EXIST            = "E102";
    const ERR_OLD_PASSWORD_WRONG        = "E103";
    const ERR_PASSWORD_WRONG            = "E104";
    const ERR_PERMISSION_WRONG          = "E105";
    const ERR_USER_EXIST_WRONG          = "E106";
    
    const ERR_NOT_EXIST_TEMPLATE        = 'E3003';
    const ERR_TEMPLATE_SUBJECT_WRONG    = 'E3004';
    const ERR_TEMPLATE_CONTENT_WRONG    = 'E3005';
    const ERR_TEMPLATE_NAME_WRONG       = 'E3006';
    
    private $notificationMsg, $errorMsg, $confirmMsg;

    public function __construct($context, $moduleName, $actionName)   {
        parent::__construct($context, $moduleName, $actionName);
        $configRep = new ConfigRepository();
        $this->notificationMsg = $configRep->getNotificationMsg();
        $this->errorMsg        = $configRep->getErrorMsg();
        $this->confirmMsg      = $configRep->getConfirmMsg();
    }
    
    /**
     * check user has been logined or not
     * @return [type] [description]
     */
    private function _checkAuth() {
        // check login or not
        if (!$this->getUser()->isAuthenticated()) {
            $this->redirect('login');
        }
    }

    /**
     * Executes login screen
     *
     * @param sfRequest $request A request object
     */
    public function executeIndex(sfWebRequest $request) {
        $this->loggedIn = $this->getUser()->isAuthenticated();
    }

    /**
     * User management
     *
     * @param sfWebRequest $request
     */
    public function executeManagement(sfWebRequest $request) {
        $this->_checkAuth();
        $this->loggedIn = $this->getUser()->isAuthenticated();
        $this->setLayout('layout');
        $user = new UserRepository;
        $this->roles = $user->getRoleList();
        $this->userTooltip = $this->notificationMsg['tooltip'];
        $this->confirm = $this->confirmMsg;
        $this->error = $this->errorMsg;
    }

    /**
     * Executes login screen
     *
     * @param sfRequest $request A request object
     */
    public function executeSignin(sfWebRequest $request) {
        // check user_name and password
        $user = new UserRepository;
        // set authentication
        $checkLogin = $user->login($request->getParameter('username'), $request->getParameter('password'));
        if ($checkLogin['status']) {
            $this->getUser()->setAuthenticated(true);
            // store user information to sesison
            $user->setUser($request->getParameter('username'));
            $this->redirect('device');
        } else {
            //TODO: improve later
            // const ERR_PASSWORD_WRONG = "E104";
            // const ERR_PERMISSION_WRONG = "E105";
            // const ERR_USER_EXIST_WRONG = "E106";
            switch ($checkLogin['code']) {
                case self::ERR_PASSWORD_WRONG:
                case self::ERR_PERMISSION_WRONG:
                case self::ERR_USER_EXIST_WRONG:
                    $error = $this->errorMsg[$checkLogin['code']];
                    if (is_array($error) || is_object($error)) {
                        $this->getUser()->setFlash('error', $error);
                    } else  {
                        $this->getUser()->setFlash('error', sprintf($error));
                    }
                    break;

                default:
                    # code...
                    break;
            }
            $this->redirect('login');
        }
    }

    /**
     * Executes logout screen
     *
     * @param sfRequest $request A request object
     */
    public function executeLogout(sfWebRequest $request) {
        $user = new UserRepository;
        $user->logout();
        $this->redirect('login');
    }
    
    public function executeDashboard(sfWebRequest $request) {
        $this->_checkAuth();
        $deviceInventory = new DeviceRepository;

        $totalDevice = $deviceInventory->getDeviceForDashBoard();

        $this->totalAndroid = $deviceInventory->getDeviceForDashBoard(1);
        $this->totalIOS = $deviceInventory->getDeviceForDashBoard(2);
        $this->totalWP = $deviceInventory->getDeviceForDashBoard(3);

        $this->percentTotalAndroid =    $totalDevice['count'] <= 0 ? 0 : $this->calPercent($this->totalAndroid['count'], $totalDevice['count']);
        $this->percentTotalIOS =        $totalDevice['count'] <= 0 ? 0 : $this->calPercent($this->totalIOS['count'], $totalDevice['count']);
        $this->percentTotalWP =         $totalDevice['count'] <= 0 ? 0 : $this->calPercent($this->totalWP['count'], $totalDevice['count']);

        $this->androidDeviceEnrolled = $deviceInventory->getDeviceForDashBoard(1, 0);
        $this->androidDeviceUnEnroll = $deviceInventory->getDeviceForDashBoard(1, 1);
        $this->androidDeviceReEnroll = $deviceInventory->getDeviceForDashBoard(1, 2);
        
        $this->percentandroidDeviceEnrolled = $this->totalAndroid['count'] <= 0 ? 0 : $this->calPercent($this->androidDeviceEnrolled ['count'], $this->totalAndroid['count']);
        $this->percentandroidDeviceUnEnroll = $this->totalAndroid['count'] <= 0 ? 0 : $this->calPercent($this->androidDeviceUnEnroll ['count'], $this->totalAndroid['count']);
        $this->percentandroidDeviceReEnroll = $this->totalAndroid['count'] <= 0 ? 0 : $this->calPercent($this->androidDeviceReEnroll ['count'], $this->totalAndroid['count']);
        
        $this->iosDeviceEnrolled = $deviceInventory->getDeviceForDashBoard(2, 0);
        $this->iosDeviceUnEnroll = $deviceInventory->getDeviceForDashBoard(2, 1);
        $this->iosDeviceReEnroll = $deviceInventory->getDeviceForDashBoard(2, 2);

        $this->percentiosDeviceEnrolled = $this->totalIOS['count'] <= 0 ? 0 : $this->calPercent($this->iosDeviceEnrolled ['count'], $this->totalIOS['count']);
        $this->percentiosDeviceUnEnroll = $this->totalIOS['count'] <= 0 ? 0 : $this->calPercent($this->iosDeviceUnEnroll ['count'], $this->totalIOS['count']);
        $this->percentiosDeviceReEnroll = $this->totalIOS['count'] <= 0 ? 0 : $this->calPercent($this->iosDeviceReEnroll ['count'], $this->totalIOS['count']);

        $this->wpDeviceEnrolled = $deviceInventory->getDeviceForDashBoard(3, 0);
        $this->wpDeviceUnEnroll = $deviceInventory->getDeviceForDashBoard(3, 1);
        $this->wpDeviceReEnroll = $deviceInventory->getDeviceForDashBoard(3, 2);

        $this->percentwpDeviceEnrolled = $this->totalWP['count'] <= 0 ? 0 : $this->calPercent($this->wpDeviceEnrolled ['count'], $this->totalWP['count']);
        $this->percentwpDeviceUnEnroll = $this->totalWP['count'] <= 0 ? 0 : $this->calPercent($this->wpDeviceUnEnroll ['count'], $this->totalWP['count']);
        $this->percentwpDeviceReEnroll = $this->totalWP['count'] <= 0 ? 0 : $this->calPercent($this->wpDeviceReEnroll ['count'], $this->totalWP['count']);

        $this->setLayout('layout');
    }

    public function executeTemplate(sfWebRequest $request) {
        $this->_checkAuth();
        $this->setLayout('layout');
        $template = new Template();
        $this->usageSystem = $template->usageSystemDefault();
        $this->error = $this->errorMsg;
    }

    public function executeAjaxTemplateList(sfWebRequest $request) {
        $this->getResponse()->setContentType('application/json');

        $tClass = new TemplateRepository;
        $templates = $tClass->listAllTemplate($request);
        $result = array();

        if ($request->isXmlHttpRequest()) {
            foreach ($templates['result'] as $key => $template) {

                new Template;
                $result[] = array(
                    $template->id,
                    $template->getName(),
                    $template->getTemplateUsageSystem(),
                    $template->updated_at
                );
            }
        }

        $data_json = json_encode(array(
            "draw" => intval($request->getParameter('draw')),
            'data' => $result,
            "recordsTotal" => $templates['count'],
            "recordsFiltered" => $templates['count'],
                )
        );
        return $this->renderText($data_json);
    }

    public function executeAjaxGetTemplate(sfWebRequest $request) {
        $this->getResponse()->setContentType('application/json');
        $result = array("error" => array("status" => 1, "msg" => ""), "data" => array());
        $templateRes = new TemplateRepository();

        if ($request->isXmlHttpRequest()) {
            $templateId = $request->getParameter("id");
            $template = $templateRes->getTemplateById($templateId);
            if (!empty($template)) {
                $result['error']['status'] = 0;
                $result['data'] = $template->getData();
                $result['id'] = $templateId;
            } else {
                $result['error']['msg'] = $template;
            }
        }

        return $this->renderText(json_encode($result));
    }

    public function executeAjaxUpdateTemplate(sfWebRequest $request) {
        $templateRep = new TemplateRepository();
        $this->getResponse()->setContentType('application/json');
        $result = array("error" => array("status" => 1, "msg" => ""), "data" => array());
        if ($request->isXmlHttpRequest()) {
            $id = $request->getParameter('id');
            $subject = $request->getParameter('subject');
            $content = $request->getParameter('content');
            $name    = $request->getParameter('name');
            $data = array(
                'id' => $id,
                'subject' => $subject,
                'content' => $content,
                'name'    => $name  
            );
            $templateId = $templateRep->updateTemplate($data);
            if ($templateId === true) {
                $result['error']['status'] = 0;
            } else if ($templateId === false) {
                $result['error']['msg'] = 1;
            } else {
                switch ($templateId) {
                    case self::ERR_NOT_EXIST_TEMPLATE:
                    case self::ERR_TEMPLATE_SUBJECT_WRONG:
                    case self::ERR_TEMPLATE_CONTENT_WRONG:
                    case self::ERR_TEMPLATE_NAME_WRONG:
                        $result['error']['msg'] = $this->errorMsg[$templateId];
                        break;
                    default:
                        $result['error']['msg'] = $templateId;
                        break;
                }
            }
        }
        return $this->renderText(json_encode($result));
    }

    public function executeAjaxAddNewUser(sfWebRequest $request) {
        $userRep = new UserRepository();
        $this->getResponse()->setContentType('application/json');
        $result = array("error" => array("status" => 1, "msg" => ""), "data" => array());
        if ($request->isXmlHttpRequest()) {
            // get request form ajax.
            $id = $request->getParameter('id');
            $user_name = $request->getParameter('user_name');
            $password = $request->getParameter('password');
            $old_password = $request->getParameter('old_password');
            $first_name = $request->getParameter('first_name');
            $last_name = $request->getParameter('last_name');
            $email = $request->getParameter('email');
            $birthday = $request->getParameter('birthday');
            $phone_number = $request->getParameter('phone_number') . $request->getParameter('provider');
            if ($phone_number == '@') {
                $phone_number = '';
            }
            $role = $request->getParameter('role');
            $status = $request->getParameter('status');
            $data = compact('id', 'user_name', 'email', 'first_name', 'last_name', 'role', 'status', 'phone_number', 'birthday', 'password', 'old_password');
            $userId = $userRep->addNewUser($data);
            if (is_object($userId)) {
                $result['data'] = array(
                    'id' => $userId->id,
                    'email' => $userId->getEmail(),
                    'fullname' => $userId->getFullName(),
                    'username' => $userId->getUserName(),
                    'phoneNumber' => $userId->getPhoneNumber(),
                    'birthday' => $userId->getBirthdayInFormat()
                );
                $result['error']['status'] = 0;
            } else {
                switch ($userId) {
                    case self::ERR_EMAIL_EXIST:
                    case self::ERR_USERNAME_EXIST:
                    case self::ERR_OLD_PASSWORD_WRONG:
                        $result['error']['msg'] = $this->errorMsg[$userId];
                        break;
                    default:
                        $result['error']['msg'] = $userId;
                        break;
                }
            }
        }
        return $this->renderText(json_encode($result));
    }

    /*
     * Add new Profile
     * @param sfWebRequest $request
     */
    public function executeAjaxAddProfile(sfWebRequest $request) {
        $this->getResponse()->setContentType('application/json');
        $result = array("error" => array("status" => 1, "msg" => ""), "data" => array());
        if ($request->isXmlHttpRequest()) {
            $configType = $request->getParameter('configuration_type');
            $profile = new ProfileRepository();
            $attributes = $profile->getAttribute($configType);
            if (!is_object($attributes))    {
                $result['error']['msg'] = $this->errorMsg[$attributes];
                return $this->renderText(json_encode($result));
            }
            $data = array();
            foreach ($attributes as $attr) {
                $temp = $attr->getProfileAttributeKey();
                if($request->getParameter($temp) != -1 && $request->getParameter($temp) != ""){
                    $data[$temp] = $request->getParameter($temp);
                }
                unset($data[self::PASSCODE_SIMPLE_VALUE]); // Not manage this option
            }
            if (array_key_exists(self::PASSCODE_ALPHABETIC, $data))   {
                $data[self::PASSCODE_ALPHABETIC] = TRUE;
            }
            // Save to database
            $newProfile = $profile->add_profile($request, $data);
            if (is_object($newProfile)) {
                $result['error']['status'] = 0;
            } else {
                $result['error']['msg'] = $newProfile;
            }
        }
        return $this->renderText(json_encode($result));
    }

    public function executeAjaxUserList(sfWebRequest $request) {
        $userRep = new UserRepository();
        $this->getResponse()->setContentType('application/json');

        $users = $userRep->getListUserDataTable($request);
        $result = array();

        if ($request->isXmlHttpRequest()) {
            foreach ($users['result'] as $key => $user) {
                //$user = new UserInfo();
                $result[] = array($user->id, $user->getStatus(), $user->getUserName(), $user->getFirstName(), $user->getLastName(), $user->getEmail(), $user->getRole()->getRoleName(), $user->getPhoneNumber(), $user->getBirthdayInFormat());
            }
        }

        $data_json = json_encode(array(
            "draw" => intval($request->getParameter('draw')),
            'data' => $result,
            "recordsTotal" => $users['count'],
            "recordsFiltered" => $users['count'],
                )
        );
        return $this->renderText($data_json);
    }

    public function executeAjaxGetUser(sfWebRequest $request) {
        $result = array("error" => array("status" => 1, "msg" => ""), "data" => array());
        $userRep = new UserRepository();
        $this->getResponse()->setContentType('application/json');
        if ($request->isXmlHttpRequest()) {
            $userId = $request->getParameter("id");
            $users = $userRep->getUserById($userId);
            if (!empty($users)) {
                $result['error']['status'] = 0;
                $users->setPassword('');
                $result['data'] = $users->getData();
                $result['id'] = $userId;
            } else {
                $result['error']['msg'] = $users;
            }
        }
        return $this->renderText(json_encode($result));
    }
    
    
    /*
     * Get profile information
     * @param sfWebRequest $request
     */
    public function executeAjaxGetProfile(sfWebRequest $request) {
        $result = array("error" => array("status" => 1, "msg" => ""), "data" => array());
        $this->getResponse()->setContentType('application/json');
        if ($request->isXmlHttpRequest()) {
            $profileId = $request->getParameter('id');
            $profileRep = new ProfileRepository();
            $profile = $profileRep->getProfileAllInfo($profileId);
            if (!empty($profile)) {
                $result["data"] = array_merge($profile['profile']->getData(), $profile['value']);
                $result['id'] = $profileId;
                $result['error']['status'] = 0;
            } else {
                $result['error']['msg'] = $profile;
            }
        }
        return $this->renderText(json_encode($result));
    }

    public function executeAjaxDeleteUser(sfWebRequest $request) {
        $result = array("error" => array("status" => 1, "msg" => ""), "data" => array());
        $userRep = new UserRepository();
        $this->getResponse()->setContentType('application/json');
        if ($request->isXmlHttpRequest()) {
            $userId = $request->getParameter("id");
            if ($userRep->deleteUser($userId)) {
                // Logout if self delete
                $userRep = new UserRepository();
                $redirect = $userRep->checkUserAlive(\sfContext::getInstance()->getUser()->getDecorator()->getUserId());
                $result['error']['status'] = 0;
                $result['error']['msg'] =   $this->errorMsg[$redirect['msg']];
                $result['redirect'] =       $redirect['redirect'];
            } else {
                $result['error']['msg'] = $this->errorMsg['E2001'];
            }
        }
        return $this->renderText(json_encode($result));
    }

    /*
     * Delete Profile
     * @param sfWebRequest $request
     */

    public function executeAjaxDeleteProfile(sfWebRequest $request) {
        $result = array("error" => array("status" => 1, "msg" => ""), "data" => array());
        $this->getResponse()->setContentType('application/json');
        if ($request->isXmlHttpRequest()) {
            $profileId = $request->getParameter("id");
            $profileRep = new ProfileRepository();
            if ($profileRep->deleteProfile($profileId)) {
                $result['error']['status'] = 0;
            } else {
                $result['error']['msg'] = $this->errorMsg['E2002'];
            }
        }
        return $this->renderText(json_encode($result));
    }

    /*
     * Location Management
     */

    public function executeLocationManagement(sfWebRequest $request) {
        $this->_checkAuth();
        $this->setLayout('layout');
        $this->error = $this->errorMsg;
        $this->confirm = $this->confirmMsg;
    }

    /*
     * Ajax get location list
     * @param sfWebRequest $request
     * @return array data
     */

    public function executeAjaxLocationList(sfWebRequest $request) {
        $this->getResponse()->setContentType('application/json');
        $locationRep = new LocationRepository();
        $locations = $locationRep->getLocationList($request);
        $result = array();

        if ($request->isXmlHttpRequest()) {
            foreach ($locations['result'] as $key => $location) {
                $result[] = array($location->id, $location->getOrganization(), $location->getLocation());
            }
        }

        $data_json = json_encode(array(
            "draw" => intval($request->getParameter('draw')),
            'data' => $result,
            "recordsTotal" => $locations['count'],
            "recordsFiltered" => $locations['count'],
                )
        );
        return $this->renderText($data_json);
    }

    /*
     * Ajax add new location
     * @param sfWebRequest $request
     * @return array result
     */

    public function executeAjaxAddNewLocation(sfWebRequest $request) {
        $this->getResponse()->setContentType('application/json');
        $result = array("error" => array("status" => 1, "msg" => ""), "data" => array());

        if ($request->isXmlHttpRequest()) {
            $locationRep = new LocationRepository();
            $addNew = $locationRep->addNewLocation($request);
            if (is_object($addNew)) {
                $result['data'] = array(
                    'organization' => $addNew->getOrganization(),
                    'location' => $addNew->getLocation(),
                );
                $result['error']['status'] = 0;
            } else {
                $result['error']['msg'] = $this->errorMsg[$addNew];
            }
        }
        return $this->renderText(json_encode($result));
    }

    /*
     * Ajax delete location
     * @param sfWebRequest $request
     * @return json result
     */

    public function executeAjaxDeleteLocation(sfWebRequest $request) {
        $this->getResponse()->setContentType('application/json');
        $result = array("error" => array("status" => 1, "msg" => ""), "data" => array());

        if ($request->isXmlHttpRequest()) {
            // get request form ajax.
            $locationId = $request->getParameter('id');
            $locationRep = new LocationRepository();
            $delete = $locationRep->deleteLocation($locationId);
            if ($delete === TRUE || $delete === 1) {
                $result['error']['status'] = 0;
            } else {
                $result['error']['msg'] = $this->errorMsg[$delete];
            }
        }
        return $this->renderText(json_encode($result));
    }

    /*
     * Ajax get location info
     * @param sfWebRequest $request
     * @return json result
     */

    public function executeAjaxGetLocationInfo(sfWebRequest $request) {
        $this->getResponse()->setContentType('application/json');
        $result = array("error" => array("status" => 1, "msg" => ""), "data" => array());

        if ($request->isXmlHttpRequest()) {
            // get request form ajax.
            $locationId = $request->getParameter('id');
            $locationRep = new LocationRepository();
            $location = $locationRep->getLocationById($locationId);
            if (is_object($location)) {
                $result['error']['status'] = 0;
                $result['data'] = $location->getData();
            } else {
                $result['error']['msg'] = $this->errorMsg[$location];
            }
        }
        return $this->renderText(json_encode($result));
    }

    /**
     * Function round and get number format
     * @param $pie A smailer number, $sum sum calculate on
     * @result $percent $pie/$sum %
     */
    private function calPercent($pie, $sum)   {
        $percent = $pie / $sum * 100;
        $percentCut = floor($percent * 10) / 10;
        return number_format($percentCut, 1);
    }
}
