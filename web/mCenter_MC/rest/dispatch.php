<?php
if (@include(__DIR__ . '/../vendor/autoload.php')) {
    Logger::configure(__DIR__.'/../src/Mcenter/Config/log/log4php.xml');
} else {
    die('Could not find autoloader!');
}

require_once __DIR__.'/../src/Mcenter/Resource/LoginResource.php';
require_once __DIR__.'/../src/Mcenter/Resource/AddUserResource.php';
require_once __DIR__.'/../src/Mcenter/Resource/LogoutResource.php';
require_once __DIR__.'/../src/Mcenter/Resource/UpdatePasswordResource.php';
require_once __DIR__.'/../src/Mcenter/Resource/UpdateEmailResource.php';
require_once __DIR__.'/../src/Mcenter/Resource/UpdatePhoneResource.php';
require_once __DIR__.'/../src/Mcenter/Resource/ForgotPasswordResource.php';
require_once __DIR__.'/../src/Mcenter/Resource/RequestOtpResource.php';
require_once __DIR__.'/../src/Mcenter/Resource/RequestSecretKeyResource.php';
require_once __DIR__.'/../src/Mcenter/Resource/SaveOtpResource.php';
require_once __DIR__.'/../src/Mcenter/Resource/GetOtpSettingResource.php';
require_once __DIR__.'/../src/Mcenter/Resource/ListUserResource.php';
require_once __DIR__.'/../src/Mcenter/Resource/VerifyOtpResource.php';
require_once __DIR__.'/../src/Mcenter/Resource/IndexResource.php';

$config = array(
    'load' => array(
    //    __DIR__.'/../src/Tyrell/*.php', // load example resources
    //    __DIR__.'/../vendor/peej/tonic/src/Tyrell/*.php' // load examples from composer's vendor directory
    ),
    #'mount' => array('Tyrell' => '/nexus'), // mount in example resources at URL /nexus
    #'cache' => new Tonic\MetadataCacheFile('/tmp/tonic.cache') // use the metadata cache
    #'cache' => new Tonic\MetadataCacheAPC // use the metadata cache
);

$app = new Tonic\Application($config);


#echo $app; die;

$request = new Mcenter\Lib\MCRequest();

#echo $request; die;


try {

    $resource = $app->getResource($request);

    #echo $resource; die;

    $response = $resource->exec();

} catch (Tonic\NotFoundException $e) {
    $response = new Tonic\Response(404, $e->getMessage());

} catch (Tonic\UnauthorizedException $e) {
    $response = new Tonic\Response(401, $e->getMessage());
    $response->wwwAuthenticate = 'Basic realm="My Realm"';

} catch (Tonic\MethodNotAllowedException $e) {
    $response = new Tonic\Response($e->getCode(), $e->getMessage());
    $response->allow = implode(', ', $resource->allowedMethods());

} catch (Tonic\Exception $e) {
    $response = new Tonic\Response($e->getCode(), $e->getMessage());
}

#echo $response;

$response->output();
