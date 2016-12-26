<?php

namespace spec\Gcs\Decorator;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class DeviceDecoratorSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('Gcs\Decorator\DevideDecorator');
    }

    function it_display_device_name($deviceObj = NULL)
    {
    	# 
    	$this->displayName($deviceObj)->shouldHaveType('string');
    }

    function it_display_software($deviceObj = NULL)
    {
    	# 
    	$this->displaySoftware($deviceObj)->shouldHaveType('string');
    }

    function it_display_owner_name($deviceObj = NULL)
    {
    	# 
    	$this->displayOnwerName($deviceObj)->shouldHaveType('string');
    }

    function it_display_last_report_time($deviceObj = NULL)
    {
    	# 
    	$this->displayLastReport($deviceObj)->shouldHaveType('string');
    }

    function it_display_organization($deviceObj = NULL)
    {
    	# 
    	$this->displayOrganization($deviceObj)->shouldHaveType('string');
    }

    function it_display_purchase_date($deviceObj = NULL)
    {
    	# 
    	$this->displayPurchaseDate($deviceObj)->shouldHaveType('string');
    }

    function it_display_warranty_end($deviceObj = NULL)
    {
    	# 
    	$this->displayWarrantyEnd($deviceObj)->shouldHaveType('string');
    }
}
