<?php

/**
 * Description: Interface for User service
 * 
 * Modify History:
 *  September 10, 2015: cuongnd initial version
 */

namespace Gcs\Contract;

interface UserRepositoryInterface {

    public function validateUser($username, $password);

    public function getUser();

    public function setUser($username = '');

    public function clearUser();

    public function getList();
}
