<?php
namespace spec\Gcs\Repository;
require __DIR__ .'/../../lib/symfony_autoload.php';

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;


class AttributeGroupRepositorySpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('Gcs\Repository\AttributeGroupRepository');
    }

    function it_get_all_group()
    {
    	$this->getAllGroups()->shouldBeCall(true);
    }

    function it_get_attributes_of_group($group_id = '')
    {
    	$this->getAttributeOfGroup($group_id)->shouldBeCall(true);
    }

    function it_get_all_attributes_of_groups()
    {
    	$this->getAllAttributes()->shouldBeCall(true);
    }
}
