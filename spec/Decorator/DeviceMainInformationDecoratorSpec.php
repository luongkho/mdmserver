<?php

namespace spec\Gcs\Decorator;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class DeviceMainInformationDecoratorSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('Gcs\Decorator\DevideMainInformationDecorator');
    }

    function it_display_free_storage($deviceObject = NULL)
    {
    	$this->freeStorage($deviceObject)->shouldHaveType('string');
    }

    function it_display_storage_name($deviceObject = NULL)
    {
    	$this->nameStorage($deviceObject)->shouldHaveType('string');
    }

    function it_display_total_storage($deviceObject = NULL)
    {
    	$this->totalStorage($deviceObject)->shouldHaveType('string');
    }

    function it_has_encrytion_storage($deviceObject = NULL)
    {
    	$this->isEncrytion($deviceObject)->shouldHaveType('string');
    }

    function it_display_warranty_start($deviceObject = NULL)
    {
    	$this->warrantyStart($deviceObject)->shouldHaveType('string');
    }

    function it_display_warranty_end($deviceObject = NULL)
    {
    	$this->warrantyEnd($deviceObject)->shouldHaveType('string');
    }

    function it_calculate_warranty_day_left($deviceObject = NULL)
    {
    	$this->warrantyRemain($deviceObject)->shouldHaveType('string');
    }

    function it_calculate_warranty_status($deviceObject = NULL)
    {
    	$this->warrantyStatus($deviceObject)->shouldHaveType('string');
    }

    function it_display_security_status($deviceObject = NULL)
    {
    	$this->securityStatus($deviceObject)->shouldHaveType('string');
    }

    function it_display_configuration_profile($deviceObject = NULL)
    {
    	$this->configurationProfile($deviceObject)->shouldHaveType('string');
    }

    function it_display_operation_network($deviceObject = NULL)
    {
    	$this->openrationNetwork($deviceObject)->shouldHaveType('string');
    }
}
