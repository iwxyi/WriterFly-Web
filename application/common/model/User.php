<?php
namespace app\common\model;
use think\Model;

class User extends Model
{
    protected $tableName = 'users';
	
    static public function login($username,  $password)
	{
		$map = array('username' => $username);
		$User = self::get($map); // 同时赋值给自己？

		if (!is_null($User) && $User->checkPassword($password)) {
			session('user_id', $User->getData('user_id'));
			return true;
		}

		return false;
	}
}