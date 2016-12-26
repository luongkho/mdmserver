<?php
/**
 * Description: Interface for Authenticate service
 * 
 * Modify History:
 *  September 10, 2015: cuongnd initial version
 */
namespace Gcs\Contract;

interface AuthRepositoryInterface
{
    public function login($username, $password);
    public function logout();
}
