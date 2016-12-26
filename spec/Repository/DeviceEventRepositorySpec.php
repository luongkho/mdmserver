<?php

namespace spec\Gcs\Repository;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class DeviceEventRepositorySpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('Gcs\Repository\DeviceEventRepository');
    }

    function it_get_all_events_of_device($device_id='')
    {
    	$this->getEvents($device_id)->shouldReturn(true);
    }

    function it_lock_device($device_id= '')
    {
    	$this->lock($device_id)->shouldReturn(true);
    }

    function it_clear_passcode_of_device($device_id = '')
    {
    	$this->clearPasscode($device_id)->shouldReturn(true);    	
    }

    function it_reset_passcode_for_device($device_id = '')
    {
    	$this->resetPasscode($device_id)->shouldReturn(true);    	
    }

    function it_wipe_device($device_id = '')
    {
    	$this->wipe($device_id)->shouldReturn(true);    	
    }

}
