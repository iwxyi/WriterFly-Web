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
        $sort = $type;
        if (is_null($type) || $type == 'level')
            $sort = 'level desc';
        else if ($type == 'yestoday')
            $sort = 'words_yestoday desc';
        else if ($type == 'today')
            $sort = new \think\db\Expression('allwords - allwords_yestoday desc');
        else
            $sort = 'level desc';

        if ($type != 'room' && $type != 'myroom')
        {
            $users = new UserModel();
            $users->where("allwords>allwords_yestoday or words_yestoday>0 or VIP_deadline>0")
                  ->order($sort);
            $users = $users->select();
            
            $this->assign('users', $users);
            return $this->fetch('rank');
        }
    }
}
