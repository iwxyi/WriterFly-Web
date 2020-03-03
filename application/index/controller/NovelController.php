<?php
namespace app\index\controller;
use app\common\model\UserModel;
use app\common\model\NovelModel;
use think\Controller;
use think\Request;

class NovelController extends Controller
{	
    public function index()
    {
        return $this->list();
    }
    
    public function list()
    {
        if (!UserModel::isLogin())
            return $this->error('请先登录', url('User/goLogin'));
        
        $novels = new NovelModel();
        $novels->where(['userID' => session('user_id'), 'del' => 0])
               ->order('sync_time desc');
        $novels = $novels->select();
        
        $this->assign('novels', $novels);
        return $this->fetch('list');
    }
    
    public function recycleList()
    {
        if (!UserModel::isLogin())
            return $this->error('请先登录', url('User/goLogin'));
        
        $novels = new NovelModel();
        $novels->where(['userID' => session('user_id'), 'del' => 1])
               ->order('sync_time desc');
        $novels = $novels->select();
        
        $this->assign('novels', $novels);
        return $this->fetch('list');
    }
}