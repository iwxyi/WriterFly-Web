<?php
namespace app\index\controller;
use app\common\model\UserModel;
use think\Controller;
use think\Request;

class IndexController extends Controller
{
    public function index()
    {
        return $this->fetch('index');
    }
    
    public function rank()
    {
        $type = Request::instance()->param('type');
        if (is_null($type))
            $type = 'level';
        $users = new UserModel();
        $users->where("allwords>allwords_yestoday or words_yestoday>0 or VIP_deadline>0")
              ->order($type);
        $users = $users->select();
        
        $this->assign('users', $users);
        
        return $this->fetch('rank');
    }
}
