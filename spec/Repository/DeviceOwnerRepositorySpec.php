<?php

namespace spec\Gcs\Repository;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class DeviceOwnerRepositorySpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('Gcs\Repository\DeviceOwnerRepository');
    }

    
    function it_get_owner_username($device_id='')
    {
    	$this->getOwnerUsername($device_id)->shouldReturn('string');
    }

    function it_get_owner_name($device_id='')
    {
    	$this->getOwnerName($device_id)->shouldReturn('string');
    }
}
