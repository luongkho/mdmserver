<?php

namespace spec\Gcs\Repository;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class DeviceInventoryRepositorySpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('Gcs\Repository\DeviceInventoryRepository');
    }

    function it_get_all_attribute_group()
    {
    	$this->getAttributeGroups()->shouldReturn(true);
    }

    function it_get_all_attribute_of_group($group_id = '')
    {
    	$this->getAttributeOfGroup($group_id)->shouldReturn(true);
    }

    function it_get_all_attributes_of_device($devide_id = '')
    {
    	$this->getAttributeOfDevice($devide_id)->shouldReturn(true);
    }
}
