<?php

namespace spec\Gcs\Repository;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class UserRepositorySpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('Gcs\Repository\UserRepository');
    }

    function it_validate_fail_user_criteria()
    {
    	$this->login('admin','admin')->shouldReturn(false);
    }

    function it_validate_sucess_user_criteria()
    {
        $this->login('dungdh','123123')->shouldReturn(true);
    }

    function it_validate_logout_user_criteria()
    {
    	$this->logout()->shouldReturn(true);
    }
}
