<?php

namespace Gcs\Decorator;

class UserDecorator
{
    private $user;

    public function setUser($obj)
    {
        $this->user = $obj;
    }

    /**
     * display email.
     *
     * @return string [description]
     */
    public function displayEmail()
    {
        if (isset($this->user['email'])) {
            return $this->user['email'];
        } else {
            return 'N/A';
        }
    }
    public function fullName()
    {
        if (isset($this->user['first_name'])) {
            return $this->user['first_name'] . ' ' . $this->user['last_name'];
        } else {
            return 'Sir/Madame';
        }
    }

    /**
     * get User Id.
     *
     * @return int [description]
     */
    public function getUserId()
    {
        if (isset($this->user['id'])) {
            return $this->user['id'];
        } else {
            return 0;
        }
    }
    
    
    /**
     * Get username
     *
     * @return string [username]
     */
    public function username()
    {
        return $this->user['user_name'];
    }
    
}
