<?php

/**
 * enroll actions.
 *
 * @package    mdm-server
 * @subpackage enroll
 * @author     Dung Huynh
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
use Gcs\Decorator\UserDecorator;
use Gcs\Repository\UserRepository;
use Gcs\Repository\ConfigRepository;

class enrollActions extends sfActions {
    const MAIL_ENROLL_WP    = 'enroll_wp';
    const MAIL_ENROLL_ANDROID = "enroll_android";
    const MAIL_ENROLL_IOS   = "enroll_ios";
    const WP_PLATFORM       = 3;
    const ANDROID_PLATFORM  = 1;

    private $notificationMsg, $errorMsg;

    public function __construct($context, $moduleName, $actionName)   {
        parent::__construct($context, $moduleName, $actionName);
        $configRep = new ConfigRepository();
        $this->notificationMsg = $configRep->getNotificationMsg();
        $this->errorMsg        = $configRep->getErrorMsg();
    }
    
    /**
     * Executes index action
     *
     * @param sfRequest $request A request object
     */
    public function executeIndex(sfWebRequest $request) {

        if (!$this->getUser()->isAuthenticated()) {
            $this->redirect('login');
        }
        /* Save the data */
        //Remove unuse
        
        $user = new UserRepository;
        $this->userData = $user->getList();
        $this->roles = $user->getRoleList();
        $profile = new Profile();
        $this->platformNames = $profile->platformNameDefault();
        $this->userTooltip = $this->notificationMsg['tooltip'];
    }

    public function executeAjaxEnrollDevice(sfWebRequest $request) {
        $this->getResponse()->setContentType('application/json');
        $result = array("error" => array("status" => 1, "msg" => ""), "data" => array());
        if ($request->isXmlHttpRequest()) {
            $users = $request->getParameter('data');
            
            // Get mail enroll for Android
            $templateRep = new \Gcs\Repository\TemplateRepository();
            $template = $templateRep->getMail(self::MAIL_ENROLL_ANDROID);
            if (!is_object($template)) {
                $result['error']['msg'] = $this->errorMsg[$template];
                return $this->renderText(json_encode($result));
            }
            $html_android       = $template[0]->getContent();
            $subject_android    = $template[0]->getSubject();
            
            // Get mail enroll for iOS
            $template = $templateRep->getMail(self::MAIL_ENROLL_IOS);
            if (!is_object($template)) {
                $result['error']['msg'] = $this->errorMsg[$template];
                return $this->renderText(json_encode($result));
            }
            $html_ios       = $template[0]->getContent();
            $subject_ios    = $template[0]->getSubject();
            
            // Get mail enroll for Windows phone
            $template = $templateRep->getMail(self::MAIL_ENROLL_WP);
            if (!is_object($template)) {
                $result['error']['msg'] = $this->errorMsg[$template];
                return $this->renderText(json_encode($result));
            }
            $html_wp    = $template[0]->getContent();
            $subject_wp = $template[0]->getSubject();
            
            // Send mail to each user
            foreach ($users as $user_id => $platform) {
                if ($platform == self::WP_PLATFORM) {
                    $sendMail = $this->__sendEmail($user_id, $platform, $this->getRequest()->getHost(), $html_wp, $subject_wp);
                } else if ($platform == self::ANDROID_PLATFORM){
                    $sendMail = $this->__sendEmail($user_id, $platform, $this->getRequest()->getHost(), $html_android, $subject_android);
                } else {
                    $sendMail = $this->__sendEmail($user_id, $platform, $this->getRequest()->getHost(), $html_ios, $subject_ios);
                }
                if (!$sendMail) {
                    $result['error']['msg'] = $this->errorMsg['E3002'];
                    $result['error']['status'] = 1;
                } else {
                    $result['error']['msg'] = $this->notificationMsg['N3001'];
                    $result['error']['status'] = 0;
                }
            }
        }
        return $this->renderText(json_encode($result));
    }

    /**
     * Send email after user submit an enroll
     *
     * @param $user_id
     * @param $device_id
     */
    protected function __sendEmail($user_id, $platform, $host, $html, $subject) {
        $user_model = new UserRepository;
        $user = $user_model->getUserById($user_id);
        $decor = new UserDecorator;
        $decor->setUser($user);
        $email = $decor->displayEmail();
        $name = $decor->fullName();
        $username = $decor->username();
        $from = \sfConfig::get("app_mail_from");

        /* Send the email */
        $appEnrollLink = \sfConfig::get("app_enroll_app_url_data");
        $link = public_path($appEnrollLink[$platform], true);
        
        //convert from https to http then download file.
        $link = preg_replace("/^https/i", "http", $link);

        sfProjectConfiguration::getActive()->loadHelpers('Partial');
        $html = str_replace('${name}', $name, $html);
        $html = str_replace('${username}', $username, $html);
        $html = str_replace('${domain}', $host, $html);
        
        if ($platform == self::WP_PLATFORM) {
            $html = str_replace('${link}', $appEnrollLink[self::WP_PLATFORM], $html);
        } else {
            $html = str_replace('${link}', $link, $html);
        }
        
        try {
            $message = \Swift_Message::newInstance()
                ->setSubject($subject)
                ->setFrom(array($from['email'] => $from['name']))
                ->setTo($email)
                ->setBody($html, 'text/html');
            $this->getMailer()->send($message);
        } catch (Exception $e) {
            return false;
        }
        return true;
    }

}
