<?php
namespace app\index\controller;
use app\common\model\UserModel;
use app\common\model\NovelModel;
use app\common\model\ChapterModel;
use app\common\model\ChapterLikeModel;
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
        if (!UserModel::isLogin())
            return $this->error('请先登录', url('User/goLogin'));
        
        $novelname = Request::instance()->param('novelname');
        $chapters = new ChapterModel();
        $chapters->where("userID='".session('user_id')."' and novelname='$novelname' and kind=0 and del=0");
        $chapters->order('sync_time desc');
        $chapters = $chapters->select();
        /* 章节排序 */
        
        $this->assign('title', $novelname);
        return $this->fetch('dir');
    }
    
    public function chapters()
    {
        if (!UserModel::isLogin())
            return $this->error('请先登录', url('User/goLogin'));
        
        $novelname = Request::instance()->param('novelname');
        $chapters = new ChapterModel();
        $chapters->where("userID='".session('user_id')."' and novelname='$novelname' and kind=0 and del=0");
        $chapters->order('sync_time desc');
        $chapters = $chapters->paginate(20);
        
        $this->assign('novelname', $novelname);
        $this->assign('title', $novelname);
        $this->assign('chapters', $chapters);
        return $this->fetch('chapters');
    }
    
    public function singleChapterList()
    {
        if (!UserModel::isLogin())
            return $this->error('请先登录', url('User/goLogin'));
        
        $chapters = new ChapterModel();
        $chapters->where("userID='".session('user_id')."' and kind=0 and del=0");
        $chapters->order('sync_time desc');
        $chapters = $chapters->paginate(20);
        
        $this->assign('title', '最新章节');
        $this->assign('chapters', $chapters);
        return $this->fetch('chapters');
    }
    
    public function chapter()
    {
        $chapterID = Request::instance()->param('chapter_id');
        $chapter = ChapterModel::get(['chapterID' => $chapterID, 'userID' => session('user_id'), 'kind' => 0]);
        if (is_null($chapterID))
            return $this->error('没有这篇章节');
        
        $this->assign('chapter', $chapter);
        return $this->fetch('chapter');
    }
    
    public function publishedChapter()
    {
        $chapterID = Request::instance()->param('chapter_id');
        if (is_null($chapterID) || empty($chapterID))
            $chapterID = Request::instance()->param('id');
        $chapter = ChapterModel::get(['chapterID' => $chapterID, 'kind' => 0, 'del' => 0]);
        if (is_null($chapterID))
            return $this->error('没有这篇章节');
        
        // 保存到阅读次数
        if (session('read_chapter_' . $chapterID) == null)
            session('read_chapter_' . $chapterID, '1');
        $chapter['read_count'] = $chapter['read_count'] + 1;
        $chapter->validate()->save();
        
        $this->assign('chapter', $chapter);
        return $this->fetch('publishedChapter');
    }
    
    public function sc()
    {
        return $this->publishedChapter();
    }
    
    public function saveChapter()
    {
        if (!UserModel::isLogin())
            return $this->error('请先登录', url('User/goLogin'));
        
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
        
        if ($chapter['live_publish'])
        {
            $chapter['publish_title'] = $title;
            $chapter['publish_body'] = $body;
        }
        
        $chapter->validate()->save();
        
        return 'save succeed';
    }
    
    public function savePublishChapter()
    {
        if (!UserModel::isLogin())
            return $this->error('请先登录', url('User/goLogin'));
        
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
        if (!UserModel::isLogin())
            return $this->error('请先登录', url('User/goLogin'));
        
        $param = Request::instance()->param();
        $chapterID = $param['chapter_id'];
        $chapter = ChapterModel::get(['chapterID' => $chapterID, 'userID' => session('user_id')]);
        if (is_null($chapterID))
            return $this->error('没有这篇章节');
        
        if ($chapter['publish_state'] == 0) // 直接发布
        {
            $chapter['publish_title'] = $chapter['title'];
            $chapter['publish_body'] = $chapter['body'];
            $chapter['publish_time'] = time();
            $chapter['publish_state'] = 1;
        }
        $chapter->validate()->save();
        
        $this->assign('chapter', $chapter);
        return $this->fetch('publishChapter');
    }
    
    public function publishChapter()
    {
        if (!UserModel::isLogin())
            return $this->error('请先登录', url('User/goLogin'));
        
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
        if (!UserModel::isLogin())
            return $this->error('请先登录', url('User/goLogin'));
        
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
        if (!UserModel::isLogin())
            return $this->error('请先登录', url('User/goLogin'));
        
        $param = Request::instance()->param();
        $chapterID = $param['chapter_id'];
        $chapter = ChapterModel::get(['chapterID' => $chapterID, 'userID' => session('user_id')]);
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
    
    public function publishedChapters()
    {
        $chapters = new ChapterModel();
        $targetUserID = Request::instance()->param('user_id');
        if ($targetUserID != null && !empty($targetUserID)) // 看特定作者的发布内容
        {
            $chapters->where("kind=0 and publish_state=1 and del=0 and userID='$targetUserID'");
            $user = UserModel::get(['userID' => $targetUserID]);
            $this->assign('title', $user->getName() . " 的发布内容");
        }
        else // 看全部作者的发布内容
        {
            $chapters->where("kind=0 and publish_state=1 and del=0");
            $this->assign('title', '分享广场');
        }
        $chapters->order('publish_time desc');
        $chapters = $chapters->paginate(30);
        
        $myLikes = array();
        if (UserModel::isLogin())
        {
            $userID = session('user_id');
            $likes = new ChapterLikeModel();
            $likes->where("userID = '$userID'");
            $likes = $likes->select();
            if ($likes != null)
            {
                foreach ($likes as $like)
                {
                    array_push($myLikes, $like->chapterID);
                }
            }
        }
        
        $this->assign('chapters', $chapters);
        $this->assign('likes', $likes);
        $this->assign('myLikes', $myLikes);
        return $this->fetch('publishedChapters');
    }
    
    public function myPublishList()
    {
        if (!UserModel::isLogin())
            return $this->error('请先登录', url('User/goLogin'));
        
        $chapters = new ChapterModel();
        $chapters->where("userID='".session('user_id')."' and kind=0 and publish_state=1 and del=0");
        $chapters->order('publish_time desc');
        $chapters = $chapters->select();
        
        $this->assign('chapters', $chapters);
        return $this->fetch('publishedChaptera');
    }
    
    public function likeChapter()
    {
        if (!UserModel::isLogin())
            return $this->error('请先登录', url('User/goLogin'));
        
        $chapterID = Request::instance()->param('chapter_id');
        $userID = session('user_id');
        $liked = ChapterLikeModel::isLiked($chapterID, $userID);
        if ($liked) // 已经喜欢了，取消
        {
            $chlk = new ChapterLikeModel();
            $chlk->where("chapterID = '$chapterID' and userID = '$userID'")->delete();
            
            $chapter = ChapterModel::get(['chapterID' => $chapterID]);
            $chapter['like_count'] = $chapter['like_count'] - 1;
            $chapter->validate()->save();
        }
        else // 开始喜欢
        {
            $chlk = new ChapterLikeModel();
            $chlk['chapterID'] = $chapterID;
            $chlk['userID'] = $userID;
            $chlk['create_time'] = time();
            $chlk->validate()->save();
            
            $chapter = ChapterModel::get(['chapterID' => $chapterID]);
            $chapter['like_count'] = $chapter['like_count'] + 1;
            $chapter->validate()->save();
        }
        
        return 'success';
    }
    
    public function publishedNovels()
    {
        
    }
    
    public function outline()
    {
        
    }
    
    public function delete()
    {
        
    }
}