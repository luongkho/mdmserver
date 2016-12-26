<?php

/**
 * Description: class for Windows Phone MDM service
 *  Process enroll for Windows Phone MDM platform
 *  Loading pakaged openssl then general Self Signed from CSR
 *  Return provisioning to device then enrollment
 * 
 * Modify History:
 *  September 10, 2015: cuongnd initial version
 */

namespace Gcs\Services;

use Gcs\Services\CheckinAbstract;
use Gcs\Repository\UserRepository;

class CheckinWindowsPhone extends CheckinAbstract
{

    /**
     * Loading pakaged openssl then general Self Signed from CSR
     * @return String
     */
    public function enroll($controller, $content, $log)
    {
        $dataArr = $this->parseXmlToArrayWP($content);
        $step = "";
        $uuid = $dataArr['sHeader']['aMessageID'];
        \MDMLogger::getInstance()->debug('', "\n\nUUID\n\n" . $log . $uuid . "\n\n", array());
        $bodyContent = $dataArr['sBody'];
        $headerContent = $dataArr['sHeader'];
        $response = '';
        switch (true) {
            case isset($bodyContent['Discover']):
                $response = $this->getDiscoveryResponse($uuid);
                $step = "1";
                break;
            case isset($bodyContent['GetPolicies']):
                $response = $this->getPoliciesResponse($uuid, $headerContent);
                $step = "2";
                break;
            case isset($bodyContent['wstRequestSecurityToken']):
                $securityResponse = $bodyContent['wstRequestSecurityToken'];
                $response = $this->getSecurityTokenResponse($uuid, $securityResponse, $headerContent);
                $step = "3";
                break;
            default:
                break;
        }
        \MDMLogger::getInstance()->debug('', "\n\nRESPONSE\n\n" . $response . "\n\n=====ENDING STEP " . $step . "=====\n\n", array());
        \MDMLogger::getInstance()->debug('', "\n\n\t\t===============================================================\n\n", array());

        $controller->getResponse()->setHttpHeader('Content-length', strlen($response));
        $controller->getResponse()->setHttpHeader('Content-type', 'application/soap+xml');
        return $controller->renderText($response);
    }

    /**
     * Loading pakaged openssl then general Self Signed from CSR
     * @return String
     */
    private function loadOpenssl()
    {
        $libDir = \sfConfig::get("sf_lib_dir");
        return $libDir . "/Gcs/Services/openssl/openssl.cnf";
    }

    /**
     * Return Certificate of server
     * @param String $crt
     * @return String
     */
    private function createCrt($crt)
    {
        $pem = chunk_split($crt, 64, "\n");
        $pem = "-----BEGIN CERTIFICATE-----\n" . $pem . "-----END CERTIFICATE-----\n";
        return $pem;
    }

    /**
     * Create Certificate Security Reuqest
     * @param type $csr
     * @return string
     */
    private function createCSR($csr)
    {

        $pem = chunk_split($csr, 64, "\n");
        $pem = "-----BEGIN CERTIFICATE REQUEST-----\n" . $pem . "-----END CERTIFICATE REQUEST-----\n";
        return $pem;
    }

    /**
     * receive request and enroll device.
     * @param  [object] $request
     * @return [array] $response
     */
    private function getXmlResponse($xmlFile)
    {
        $libDir = \sfConfig::get("sf_lib_dir");
        return file_get_contents($libDir . "/Gcs/Services/xml/" . $xmlFile);
    }

    private function getCAKeyAndCert($fileName)
    {
        $dataDir = \sfConfig::get("sf_data_dir");
        return file_get_contents($dataDir . "/certificates/" . $fileName);
    }

    /**
     * receive request and enroll device.
     * @param  [object] $request
     * @return [array] $response
     */
    private function parseXmlToArrayWP($xml)
    {
        $xml = preg_replace("/(<\/?)(\w+):([^>]*>)/", "$1$2$3", $xml);
        $xml = simplexml_load_string($xml);
        $json = json_encode($xml);
        return json_decode($json, true);
    }

    /**
     * get Discovery service response to device.
     * @param  [string] $uuid
     * @return [string] $xmlResponse
     */
    private function getDiscoveryResponse($uuid)
    {
        $xmlRes = $this->getXmlResponse("Discovery.xml");
        $xmlResponse = preg_replace("/MDMUUID/", $uuid, $xmlRes);

        $discoveryEnroll = \sfConfig::get("app_windows_phone_enroll_url_data");
        $xmlResponse = preg_replace("/URLPOLICYSERVICE/", public_path($discoveryEnroll['PolicyUrl'], true), $xmlResponse);
        $xmlResponse = preg_replace("/URLSERVICE/", public_path($discoveryEnroll['ServiceUrl'], true), $xmlResponse);
        return $xmlResponse;
    }

    /**
     * get Policies service response to device
     * @param  [string] $uuid, [array]$headerContent
     * @return [string] $xmlResponse
     */
    private function getPoliciesResponse($uuid, $headerContent)
    {
        if (isset($headerContent['wsseSecurity']['wsseUsernameToken'])) {
            $wsseUsername = $headerContent['wsseSecurity']['wsseUsernameToken']['wsseUsername'];
            $arr = explode("@", $wsseUsername);
            // get username and password.
            $username = $arr[0];
            $password = $headerContent['wsseSecurity']['wsseUsernameToken']['wssePassword'];
            $userRespository = new UserRepository();
            $check = $userRespository->validateUserEnroll($username, $password);
            if ($check['status']) {
                $xmlRes = $this->getXmlResponse("GetPolicies.xml");
                $xmlResponse = preg_replace("/MDMUUID/", $uuid, $xmlRes);
                return $xmlResponse;
            }
        }
        return null;
    }

    /**
     * get SecurityToken response to device
     * @param  [string] $uuid, [array] $securityResponse
     * @return [string] $response
     */
    private function getSecurityTokenResponse($uuid, $securityResponse, $headerContent)
    {
        $provisioningXML = $this->getXmlResponse("Provisioning.xml");
        $xmlRes = $this->getXmlResponse("BinarySecurityToken.xml");
        $serverCertCA = $this->getCAKeyAndCert("certificateCA.crt");
        $privkeyCA = $this->getCAKeyAndCert("rootCA.key");
        $config = array("config" => $this->loadOpenssl());
        $clientCSR = $securityResponse['wsseBinarySecurityToken'];
        $clientCSR = $this->createCSR($clientCSR);

        $clientCertSign = openssl_csr_sign($clientCSR, $serverCertCA, $privkeyCA, 365, $config);
        $clientCertParse = openssl_x509_parse($clientCertSign);
        $subject = trim($clientCertParse['subject']['CN']);
        openssl_x509_export($clientCertSign, $clientCert);

        $clientCertResponse = $this->getCertResponse($clientCert);
        $clientThumbprint = $this->getThumbprintCert($clientCertResponse);

        $serverCertResponse = $this->getCertResponse($serverCertCA);
        $serverThumbprint = $this->getThumbprintCert($serverCertResponse);

        // get username and password enroll this device
        $wsseUsername = $headerContent['wsseSecurity']['wsseUsernameToken']['wsseUsername'];
        $arr = explode("@", $wsseUsername);
        // get username and password.
        $username = $arr[0];
        $password = $headerContent['wsseSecurity']['wsseUsernameToken']['wssePassword'];
        // replace some field for provisioning to enroll device.
        $provisioningXML = preg_replace("/ROOTSYSTEMTHUMBPRINT/", $serverThumbprint, $provisioningXML);
        $provisioningXML = preg_replace("/ROOTSYSTEMCERT/", $serverCertResponse, $provisioningXML);

        $provisioningXML = preg_replace("/MYUSERTHUMPRINT/", $clientThumbprint, $provisioningXML);
        $provisioningXML = preg_replace("/MYUSERCERT/", $clientCertResponse, $provisioningXML);
        $provisioningXML = preg_replace("/SSLCLIENTCERTSSL/", $subject, $provisioningXML);
        $discoveryEnroll = \sfConfig::get("app_windows_phone_enroll_url_data");
        $provisioningXML = preg_replace("/MDMSERVERURL/", public_path($discoveryEnroll['AppAddress'], true), $provisioningXML);
        $provisioningXML = preg_replace("/AAUTHNAME_VALUE/", $username, $provisioningXML);
        $provisioningXML = preg_replace("/AAUTHSECRET_VALUE/", $password, $provisioningXML);

        \MDMLogger::getInstance()->debug('', "\n\nRESPONSE\n\n" . $provisioningXML . "\n\n=====MIDDLE STEP 3=====\n\n", array());
        $provisioningXML = base64_encode($provisioningXML);

        $xmlRes = preg_replace("/B64EncodedSampleBinarySecurityToken/", $provisioningXML, $xmlRes);
        $xmlResponse = preg_replace("/MDMUUID/", $uuid, $xmlRes);
        return $xmlResponse;
    }

    /**
     * Get Thumbprint of certificate
     * @param String $certResponse
     * @return String 
     */
    private function getThumbprintCert($certResponse)
    {
        $file = trim($certResponse);
        $bin = base64_decode($file);
        return sha1($bin);
    }

    /**
     * Get Certificate reponse, it has been removed ---BEGIN ...--- tag & --- END ...--- 
     * @param String $certificate
     * @return type
     */
    private function getCertResponse($certificate)
    {
        $certificateIgnoredHeader = str_replace('-----BEGIN CERTIFICATE-----', '', $certificate);
        $certificateIgnoredFooter = str_replace('-----END CERTIFICATE-----', '', $certificateIgnoredHeader);
        $certificate = preg_replace("/[\n\r]/", "", $certificateIgnoredFooter);
        return $certificate;
    }

}
