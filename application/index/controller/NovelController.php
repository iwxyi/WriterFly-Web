<?php
namespace app\index\controller;
use app\common\model\UserModel;
use app\common\model\NovelModel;
use app\common\model\ChapterModel;
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
    
    public function dir()
    {
        $novelname = Request::instance()->param('novelname');
        $chapters = new ChapterModel();
        $chapters->where("userID='".session('user_id')."' and novelname='$novelname' and del=0");
        $chapters->order('sync_time desc');
        return $this->fetch('dir');
    }
    
    public function chapters()
    {
        $novelname = Request::instance()->param('novelname');
        $chapters = new ChapterModel();
        $chapters->where("userID='".session('user_id')."' and novelname='$novelname' and del=0");
        $chapters->order('sync_time desc');
        $chapters = $chapters->select();
        
        $this->assign('novelname', $novelname);
        $this->assign('chapters', $chapters);
        return $this->fetch('chapters');
    }
    
    public function outline()
    {
        
    }
    
    public function delete()
    {
        
    }
}