<?php

namespace spec\Gcs\Decorator;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class UserDecoratorSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('Gcs\Decorator\UserDecorator');
    }

    function it_display_email(){
    	$this->displayEmail()->shouldHaveType('string');
    }

    function it_get_userid(){
    	$this->getUserId()->shouldHaveType('integer');
    }
}
