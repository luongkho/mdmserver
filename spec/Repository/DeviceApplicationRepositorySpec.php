<?php

namespace spec\Gcs\Repository;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class DeviceApplicationRepositorySpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('Gcs\Repository\DeviceApplicationRepository');
    }

    function it_get_all_application_of_device($device_id = '')
    {
    	$this->getApplications($device_id)->shouldReturn(true);
    }
}
