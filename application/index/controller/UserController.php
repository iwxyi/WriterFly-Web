<?php
namespace app\index\controller;
use app\common\model\UserModel;
use app\common\model\RoomModel;
use think\Controller;
use think\Request;

class UserController extends Controller
{
    public function __construct()
	{
		// 调用父类的构造函数
		parent::__construct();

		// 验证用户是否登录
		if (!UserModel::isLogin()) {
			return $this->goLogin();
		}
	}
    
    public function goLogin()
    {
        return $this->fetch('login');
    }
    
    public function login()
    {
        // 接收 post 信息
		$post = Request::instance()->post();
        
        if (UserModel::login($post['username'], $post['password'])) {
			return $this->success('用户登录成功', url('Index/index'));
		}
		else {
			return $this->error('用户名不存在或密码错误', Request::instance()->header('referer'));
		}
    }
    
    public function goRegister()
    {
        
    }
    
    public function register()
    {
        
    }
    
    public function logOut()
    {
        if (UserModel::logOut())
        {
            return $this->success('退出成功', url('rank//'));
        }
        else
        {
            return $this->error('退出失败', url('index'));
        }
    }
    
    public function joinRoom()
    {
        $roomID = Request::instance()->param('room_id');
        if (is_null(RoomModel::get(['roomID' => $roomID])))
            return $this->error('不存在这个房间');
        
        $user = UserModel::currentUser();
        if (is_null($user))
            return $this->goLogin();
        if ($user->getData('roomID'))
        {
            session('room_id', $roomID);
            return $this->error('您已加入房间', url('rank//?type=myroom'));
        }
        // 加入房间
        $user->roomID = $roomID;
        session('room_id', $roomID);
        return $this->success('加入房间成功', url('rank//?type=myroom'));
    }
    
    public function exitRoom()
    {
        $roomID = session('room_id');
        if (is_null(RoomModel::get(['roomID' => $roomID])))
            return $this->error('不存在这个房间');
        $user = UserModel::currentUser();
        if (is_null($user))
            return $this->goLogin();
        $user->room_id = '';
        session('room_id', null);
        return $this->success('退出房间成功', url('rank//?type=room'));
    }
}