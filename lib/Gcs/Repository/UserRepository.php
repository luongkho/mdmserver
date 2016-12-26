<?php
/**
 * Description: class for Users service
 * Using verify user, update, delete user
 * 
 * Modify History:
 *  September 10, 2015: cuongnd initial version
 */
namespace Gcs\Repository;

use Gcs\Contract\UserRepositoryInterface;
use Gcs\Contract\AuthRepositoryInterface;

class UserRepository implements UserRepositoryInterface, AuthRepositoryInterface
{

    const ERR_EMAIL_EXIST = "E101";
    const ERR_USERNAME_EXIST = "E102";
    const ERR_OLD_PASSWORD_WRONG = "E103";
    const ERR_PASSWORD_WRONG = "E104";
    const ERR_PERMISSION_WRONG = "E105";
    const ERR_USER_EXIST_WRONG = "E106";
    const ACCOUNT_DELETED = "E107";

    /**
     * authetication user system with username and password.
     *
     * @param string $username
     * @param string $password
     *
     * @return bool
     */
    public function login($username, $password)
    {
        return $this->validateUser($username, $password);
    }

    /**
     * logout and clear session.
     *
     * @return bool
     */
    public function logout()
    {
        // TODO: write logic here
        $this->clearUser();
    }

    /**
     * [validateUser description].
     *
     * @param [type] $username [description]
     * @param [type] $password [description]
     *
     * @return [type] [description]
     */
    public function validateUser($username, $password)
    {
        $userModel = \UserInfoTable::getInstance();
        $user = $userModel->findOneBy('user_name', $username);
        if ($user) {
            // check pasword
            $passwordToken = explode(":", $user['password']);
            if (sha1($password . $passwordToken[1]) == $passwordToken[0]) {
                // checking role
                if (strtolower($user->getRole()->getRoleName()) == 'admin') {
                    return array('status' => true, 'code' => 0);
                } else {
                    return array('status' => false, 'code' => self::ERR_PERMISSION_WRONG);
                }
            } else {
                return array('status' => false, 'code' => self::ERR_PASSWORD_WRONG);
            }
        } else {
            return array('status' => false, 'code' => self::ERR_USER_EXIST_WRONG);
        }
    }

    /**
     * Save user information to session.
     *
     * @param string $username [description]
     */
    public function setUser($username = '')
    {
        $userModel = \UserInfoTable::getInstance();
        $userInfo = $userModel->findOneBy('user_name', $username);
        // TODO: store that user information to session
        $sfContext = \sfContext::getInstance();
        $sfContext->getUser()->setAttribute('username', $username);
        $sfContext->getUser()->setAttribute('userinfo', $userInfo->toArray());
        $sfContext->getUser()->setAuthenticated(true);
    }

    /**
     * clear user from session.
     *
     * @return [type] [description]
     */
    public function clearUser()
    {
        $sfContext = \sfContext::getInstance();
        $sfContext->getUser()->setAttribute('username', null);
        $sfContext->getUser()->setAttribute('userinfo', null);
        $sfContext->getUser()->setAuthenticated(false);
    }

    /**
     * get User information.
     *
     * @return [type] [description]
     */
    public function getUser()
    {
        $sfContext = \sfContext::getInstance();

        return $sfContext->getUser()->getAttribute('userinfo');
    }

    /**
     * get all user list.
     *
     * @return object [description]
     */
    public function getList()
    {
        $userModel = \UserInfoTable::getInstance();

        return $userModel->findAll();
    }

    /**
     * List all user by request.
     *
     * @return array [description]
     */
    public function getListUserDataTable($request)
    {
        $columns = array(
            array('db' => 'd.id', 'dt' => 0),
            array('db' => 'd.status', 'dt' => 1),
            array('db' => 'd.user_name', 'dt' => 2, 'is_search' => true),
            array('db' => 'd.first_name', 'dt' => 3, 'is_search' => true),
            array('db' => 'd.last_name', 'dt' => 4, 'is_search' => true),
            array('db' => 'd.email', 'dt' => 5, 'is_search' => true),
            array('db' => 'd.role_id', 'dt' => 6, 'int_search' => array(
                    'type' => 'foreign',
                    'name' => 'RoleTable',
                    'select' => 'role_id',
                    'where' => 'role_name'
                )),
            array('db' => 'd.phone_number', 'dt' => 7, 'is_search' => true),
            array(
                'db' => 'd.birthday',
                'dt' => 8,
                'formatter' => function ($d, $row) {
            return date('jS M y', strtotime($d));
        },
            )
        );

        $limit = \SSP::limit($request);
        $order = \SSP::order($request, $columns);
        $where = \SSP::filter($request, $columns);
        $whereInt = \SSP::filter_integer($request, $columns);
        $query = \UserInfoTable::getInstance()->createQuery('d');
        $query = $query->limit($limit['limit'])->offset($limit['offset']);
        foreach ($order as $orderBy) {
            $query = $query->orderBy($orderBy);
        }
        foreach ($where as $key => $val) {
            $query = $query->orWhere($key . ' ILIKE ?', '%' . $val . '%');
        }
        foreach ($whereInt as $key => $value) {
            $query = $query->orWhereIn($key, $value);
        }

        return array('result' => $query->execute(), 'count' => $query->count());
    }

    /**
     * get all user list.
     *
     * @return object [description]
     */
    public function getRoleList()
    {
        $userModel = \RoleTable::getInstance();

        return $userModel->findAll();
    }

    /**
     * get User information by user id.
     *
     * @param int $user_id [description]
     *
     * @return array [description]
     */
    public function getUserById($user_id)
    {
        $userTable = \UserInfoTable::getInstance();

        return $userTable->find($user_id);
    }

    public function deleteUser($user_id)
    {

        $userModel = \UserInfoTable::getInstance();
        $userInfo = $userModel->find($user_id);
        //if (empty($userInfo)) return false;
        $delete = $userInfo->delete();
        // remove devices are belong to this user
        if ($delete) {
            \Doctrine::getTable('DeviceInventory')
                    ->createQuery()
                    ->delete()
                    ->where('user_id = ?', $user_id)
                    ->execute();
            \Doctrine::getTable('EnrollWp')
                    ->createQuery()
                    ->delete()
                    ->where('user_id = ?', $user_id)
                    ->execute();
        }
        return $delete;
    }

    /**
     * Check if username or email existed
     *
     * @param null $user_name
     * @param null $email
     * @return false|1|2
     */
    public function checkExistUser($user_name = null, $email = null, $id = null)
    {
        if (empty($user_name) && empty($email))
            return false;
        $userTable = \UserInfoTable::getInstance();
        $existed = false;
        if (!empty($user_name)) {
            $user = $userTable->findOneBy('user_name', $user_name);
            if (!empty($user) && $user->id != $id)
                return 1;
        }
        if (!$existed && !empty($email)) {
            $user = $userTable->findOneBy('email', $email);
            if (!empty($user) && $user->id != $id)
                return 2;
        }
        return false;
    }

    /**
     * Add new user to database
     * @param Array $data
     * @return boolean| Object
     */
    public function addNewUser($data)
    {
        $valid = $this->validateUserInfo($data);
        if ($valid === true) {
            /* Save Device */
            $editMode = !empty($data['id']);
            if ($editMode) {
                $userModel = \UserInfoTable::getInstance();
                $userInfo = $userModel->find($data['id']);
            } else {
                $userInfo = new \UserInfo();
                $userInfo->setUserName($data['user_name']);
            }
            // Edit user not change 'user_name'
            $userInfo->setEmail($data['email']);
            $userInfo->setFirstName($data['first_name']);
            $userInfo->setLastName($data['last_name']);
            $userInfo->setPhoneNumber($data['phone_number']);
            $userInfo->setBirthdayInFormat($data['birthday']);
            $userInfo->setRoleId($data['role']);
            $userInfo->setStatus($data['status']);
            //TODO: Hardcode TenantId = 100456
            $userInfo->setTenantId('100456');
            if (!empty($data['password'])) {
                if ($editMode) {
                    if ($userInfo->getHashedPassword($data['old_password']) == $userInfo->getPassword()) {
                        // Check old password
                        $userInfo->setPasswordInHash($data['password']);
                    } else {
                        return self::ERR_OLD_PASSWORD_WRONG;
                    }
                }
                $userInfo->setPasswordInHash($data['password']);
            }
            $userInfo->save();
            $userId = $userInfo->getId();
            if (!empty($userId)) {
                if ($editMode) {
                    \Doctrine_Query::create()
                            ->update('DeviceInventory d')
                            ->set('d.owner_name', '?', $userInfo->getFullName())
                            ->set('d.owner_email', '?', $userInfo->getEmail())
                            ->where('d.user_id = ?', $userId)
                            ->execute();
                }
                return $userInfo;
            }
            return false;
        }
        return $valid;
    }

    
    /**
     * Validate user info available or not
     * @param Array $data
     * @return boolean
     */
    private function validateUserInfo($data)
    {
        $isExist = $this->checkExistUser($data['user_name'], $data['email'], $data['id']);
        if ($isExist !== false) {
            return $isExist == 2 ? self::ERR_EMAIL_EXIST : self::ERR_USERNAME_EXIST;
        }
        return true;
    }

    /**
     * Getting user info by username
     * @param String $username
     * @return Object
     */
    public function getUserInfoByUsername($username)
    {
        $userInfo = \UserInfoTable::getInstance()->findOneByUserName($username);
        return $userInfo;
    }

    /**
     * Logout if current user's id match
     * @param sfWebRequest $request
     */
    public function checkUserAlive($id)
    {
        $result = array('msg' => '', 'redirect' => '');
//        $id = $request->getParameter("id");
        $isAlive = $this->getUserById($id);

        // If user was deleted already
        if (!$isAlive) {
            $this->logout();
            $result['msg'] = self::ACCOUNT_DELETED;
            $result['redirect'] = public_path('login', true);
        }
        return $result;
    }

    /**
     * [validateUserEnroll description].
     *
     * @param [type] $username [description]
     * @param [type] $password [description]
     *
     * @return [type] [description]
     */
    public function validateUserEnroll($username, $password)
    {
        $userModel = \UserInfoTable::getInstance();
        $user = $userModel->findOneBy('user_name', $username);
        if ($user) {
            // check pasword
            $passwordToken = explode(":", $user['password']);
            if (sha1($password . $passwordToken[1]) == $passwordToken[0]) {
                return array('status' => true, 'code' => 0);
            } else {
                return array('status' => false, 'code' => self::ERR_PASSWORD_WRONG);
            }
        } else {
            return array('status' => false, 'code' => self::ERR_USER_EXIST_WRONG);
        }
    }

}
