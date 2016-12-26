<?php

namespace spec\Gcs\Repository;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class DeviceRepositorySpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('Gcs\Repository\DeviceRepository');
    }

    function it_get_list_device_of_user_with_order_and_pagination($userid,$order,$page = '1')
    {
    	$this->list_device($userid,$order,$page)->shouldReturn(true);
    }

    function it_find_device_by($query ='')
    {
    	$this->search($query)->shouldReturn(true);
    }

}
