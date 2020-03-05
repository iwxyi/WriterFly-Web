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
        $chapters->where("userID='".session('user_id')."' and novelname='$novelname' and kind=0 and del=0");
        $chapters->order('sync_time desc');
        return $this->fetch('dir');
    }
    
    public function chapters()
    {
        $novelname = Request::instance()->param('novelname');
        $chapters = new ChapterModel();
        $chapters->where("userID='".session('user_id')."' and novelname='$novelname' and kind=0 and del=0");
        $chapters->order('sync_time desc');
        $chapters = $chapters->select();
        
        $this->assign('novelname', $novelname);
        $this->assign('chapters', $chapters);
        return $this->fetch('chapters');
    }
    
    public function chapter()
    {
        $chapterID = Request::instance()->param('chapter_id');
        $chapter = ChapterModel::get(['chapterID' => $chapterID, 'userID' => session('user_id'), 'kind' => 0]);
        $this->assign('chapter', $chapter);
        return $this->fetch('chapter');
    }
    
    public function saveChapter()
    {
        $param = Request::instance()->param();
        $chapterID = $param['chapter_id'];
        $title = $param['title'];
        $body = $param['body'];
        $chapter = ChapterModel::get(['chapterID' => $chapterID, 'userID' => session('user_id')]);
        if (is_null($chapterID))
            return $this->error('没有这篇章节');
        
        $chapter['title'] = $title;
        $chapter['body'] = $body;
        $chapter['alter_time'] = $chapter['sync_time'] = time();
        $chapter->validate()->save();
        
        return 'save succeed';
    }
    
    public function savePublish()
    {
        $param = Request::instance()->param();
        $chapterID = $param['chapter_id'];
        $title = $param['title'];
        $body = $param['body'];
        $chapter = ChapterModel::get(['chapterID' => $chapterID, 'userID' => session('user_id')]);
        if (is_null($chapterID))
            return $this->error('没有这篇章节');
        
        $chapter['publish_title'] = $title;
        $chapter['publish_body'] = $body;
        $chapter['publish_time'] = time();
        $chapter->validate()->save();
        
        return 'save succeed';
    }
    
    public function goPublishChapter()
    {
        $param = Request::instance()->param();
        $chapterID = $param['chapter_id'];
        $chapter = ChapterModel::get(['chapterID' => $chapterID, 'userID' => session('user_id')]);
        if (is_null($chapterID))
            return $this->error('没有这篇章节');
        
        if ($chapter['publish_state'] == 0) // 直接发布
        {
            $chapter['publish_title'] = $chapter['title'];
            $chapter['publish_body'] = $chapter['body'];
            $chapter['publish_time'] = $chapter['sync_time'] = time();
            $chapter['publish_state'] = 1;
        }
        $chapter->validate()->save();
        
        $this->assign('chapter', $chapter);
        return $this->fetch('publishChapter');
    }
    
    public function publishChapter()
    {
        $param = Request::instance()->param();
        $chapterID = $param['chapter_id'];
        $chapter = ChapterModel::get(['chapterID' => $chapterID, 'userID' => session('user_id')]);
        if (is_null($chapterID))
            return $this->error('没有这篇章节');
        
        if ($chapter['publish_state'] < 0) // 禁止
            return 'banned';
        
        $chapter['publish_state'] = 1;
        $chapter['publish_title'] = $chapter['title'];
        $chapter['publish_body'] = $chapter['body'];
        $chapter['publish_time'] = time();
        $chapter->validate()->save();
        return 'succeed';
    }
    
    public function unpublishChapter()
    {
        $param = Request::instance()->param();
        $chapterID = $param['chapter_id'];
        $chapter = ChapterModel::get(['chapterID' => $chapterID, 'userID' => session('user_id')]);
        if (is_null($chapterID))
            return $this->error('没有这篇章节');
        
        if ($chapter['publish_state'] < 0) // 禁止
        {
            return 'banned';
        }
        
        $chapter['publish_state'] = 0;
        $chapter->validate()->save();
        return 'succeed';
    }
    
    public function livePublish()
    {
        $param = Request::instance()->param();
        $chapterID = $param['chapter_id'];
        $chapter = ChapterModel::get(['chapterID' => $chapterID, 'user_id' => session('user_id')]);
        if (is_null($chapterID))
            return $this->error('没有这篇章节');
        
        if ($chapter['live_publish'] == 1)
            $chapter['live_publish'] = 0;
        else
        {
            $chapter['live_publish'] = 1;
            $chapter['publish_body'] = $chapter['body'];
        }
        $chapter->validate()->save();
        return 'succeed';
    }
    
    public function outline()
    {
        
    }
    
    public function delete()
    {
        
    }
}