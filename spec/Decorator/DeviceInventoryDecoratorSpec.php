<?php

namespace spec\Gcs\Decorator;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class DeviceInventoryDecoratorSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('Gcs\Decorator\DeviceInventoryDecorator');
    }

    function it_display_device_section($group_id)
    {
    	$this->displaySection($group_id)->shoudReturn('string');
    }
}
