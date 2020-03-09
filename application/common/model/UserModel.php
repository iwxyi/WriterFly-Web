<?php
namespace app\common\model;
use think\Model;

class UserModel extends Model
{
    protected $table = 'users';
    
    static public function login($username,  $password)
	{
		$map = array('username' => $username);
		$user = self::get($map); // 同时赋值给自己？

		if (!is_null($user) && $user->checkPassword($password)) {
			session('user_id', $user->getData('userID'));
            session('room_id', $user->getData('roomID'));
            
            $lastest = 3600*24*7;
            cookie('username', $user->getData('username'), $lastest);
            cookie('password', $user->getData('password'), $lastest);
			return true;
		}
		return false;
	}
    
    private function checkPassword($password)
	{
		return ($this->getData('password') === $this::encryptPassword($password)
			|| $this->getData('password') === $password); // 调试，允许不加密的密码
	}
    
    static private function encryptPassword($password)
	{
		if (!is_string($password))
			throw new \RuntimeException("传入变量类型非字符串");
		return sha1(md5($password) . 'smartmeeting');
	}
    
    static public function logOut()
	{
		session('user_id', null);
        cookie('username', null);
		return true;
	}
    
    static public function isLogin()
	{
		$user_id = session('user_id');
        if (empty($user_id))
        {
            $username = cookie('username');
            $password = cookie('password');
            if (!empty($username) && !empty('password'))
                return login($username, $password);
        }
		return !empty($user_id);
	}
    
    static public function currentUser()
    {
        $user_id = session('user_id');
        if (empty($user_id))
            return null;
        return UserModel::get(['userID' => $user_id]);
    }
    
    public function getName()
	{
		if (($this->getData('nickname')) != "")
			return $this->getData('nickname');
		return $this->getData('username');
	}
}