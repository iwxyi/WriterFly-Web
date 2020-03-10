<?php
namespace app\index\controller;
use app\common\model\UserModel;
use app\common\model\MuseModel;
use app\common\model\SafeModel;
use think\Controller;
use think\Request;

class MuseController extends Controller
{
    /**
     * 根情节入口
     */
    public function entrance()
    {
        $muses = new MuseModel();
        $muses->where('floor = 1 and banned = 0')->order('children_count desc, relay_time desc');
        $muses = $muses->paginate(30);
        $this->assign('isLogin', UserModel::isLogin() ? '1' : '0');
        $this->assign('muses', $muses);
        $this->assign('offspring', true);
        return $this->fetch('list');
    }
    
    /**
     * 按时间排序
     */
    public function latest()
    {
        $muses = new MuseModel();
        $muses->where('banned = 0')->order('create_time desc');
        $muses = $muses->paginate(30);
        $this->assign('latest', true);
        $this->assign('muses', $muses);
        return $this->fetch('list');
    }
    
    /**
     * 前往创建根情节
     */
    public function goCreate()
    {
        $user = UserModel::currentUser();
        $muse = MuseModel::get(['userID' => session('user_id'), 'children_count'=>0, 'parentID'=>'0', 'banned'=>'0']);
        if (!is_null($muse))
            return $this->error('您有开头情节未被接力，无法再次新建');
        $this->assign('isLogin', UserModel::isLogin() ? '1' : '0');
        return $this->fetch('create');
    }
    
    /**
     * 创建根情节
     */
    public function create()
    {
        if (!UserModel::isLogin())
            return $this->error('请先登录', url('User/goLogin'));
        
        $content = trim(Request::instance()->param('content'));
        if (mb_strlen($content) >= 300 || mb_strlen($content) < 30)
        {
            $this->assign('hasError', 'true');
            $this->assign('errorReason', '请输入30~300个汉字的内容');
            $this->assign('default', $content);
            return $this->goCreate();
        }
        else if (SafeModel::hasSensitive($content))
        {
            $this->assign('hasError', 'true');
            $this->assign('errorReason', '请检查内容是否包含敏感词');
            $this->assign('default', $content);
            return $this->goCreate();
        }
        
        $muse = new MuseModel();
        $muse['parentID'] = '';
        $muse['userID'] = session('user_id');
        $muse['content'] = $content;
        $muse['relay_time'] = $muse['create_time'] = time();
        $muse->validate()->save();
        
        $muse['path'] = $muse['museID'];
        $muse->validate()->save();
        return $this->success('创建情节成功', url('Muse/latest'));
    }
    
    /**
     * 显示情节线
     * - 父情节
     * - 子情节
     */
    public function line()
    {
        $museID = Request::instance()->param('muse_id');
        if (!isset($museID) || $museID == '')
            return $this->error('未获取到情节');
        $muse = MuseModel::get(['museID' => $museID]);
        if (is_null($muse))
            return $this->error('未找到该情节');
        
        // 获取时间。在包含这个ID的情况下，比这个早的都是父情节，晚的都是子情节
        $create_time = strtotime($muse->create_time);
        $path = $muse->path;
        
        // 获取父情节：create早
        $parents = new MuseModel();
        $parents->where("find_in_set(museID, '$path') and create_time<='$create_time' and banned = 0")->order('create_time');
        $parents = $parents->select();
        
        // 获取子情节：create晚
        $children = new MuseModel();
        $children->where("parentID='$museID' and create_time>'$create_time' and banned = 0")->order('create_time desc');
        $children = $children->select();
        
        $this->assign('isLogin', UserModel::isLogin() ? '1' : '0');
        $this->assign('parents', $parents);
        $this->assign('current', $muse);
        $this->assign('children', $children);
        return $this->fetch('line');
    }
    
    /**
     * 前往接力（已废弃）
     */
    public function goRelay()
    {
        
    }
    
    /**
     * 收到接力
     */
    public function relay()
    {
        if (!UserModel::isLogin())
            return $this->error('请先登录', url('User/goLogin'));
        
        $parentID = Request::instance()->param('muse_id');
        if (!isset($parentID) || $parentID == '')
            return $this->error('未获取到情节');
        $parent = MuseModel::get(['museID' => $parentID]);
        if (is_null($parent))
            return $this->error('未找到该情节');
        if ($parent->userID == session('user_id'))
            return $this->error('不能接力自己的情节');
        if (!is_null(MuseModel::get(['userID' => session('user_id'), 'parentID' => $parentID])))
            return $this->error('您已经接力过该情节了');
        $content = Request::instance()->param('content');
        if (SafeModel::hasSensitive($content))
            return $this->error('请检查自己的内容是否包含敏感词');
        if (mb_strlen($content) >= 300 || mb_strlen($content) < 30)
        {
            return $this->error('请输入30~300字的汉字接力');
        }
        
        // 创建新的接力
        $muse = new MuseModel();
        $muse['userID'] = session('user_id');
        $muse['parentID'] = $parentID;
        $muse['content'] = $content;
        $muse['floor'] = $parent['floor'] + 1;
        $muse['prev_userID'] = $parent['userID'];
        $muse['relay_time'] = $muse['create_time'] = time();
        $muse->validate()->save();
        
        $his_path = $parent['path'];
        $muse['path'] = $parent['path'] . ',' . $muse['museID'];
        $muse->validate()->save();
        
        $parent['children_count'] = $parent['children_count'] + 1;
        $parent['relay_time'] = time();
        $parent->validate()->save();
        
        $lines = new MuseModel();
        $lines->where("find_in_set(museID, '$his_path')")->setInc('offspring_count');
        
        /* 清理三天前没有接力的 */
        $time = time() - 3600*24*3;
        
        
        
        return $this->success('接力成功', url('Muse/line?muse_id=' . $muse['museID']));
    }
    
    /**
     * - 我的情节
     * - 接力我的
     */
    public function mine()
    {
        if (!UserModel::isLogin())
            return $this->error('请先登录', url('User/goLogin'));
        $userID = session('user_id');
        
        // 我的情节
        $mines = new MuseModel();
        $mines->where("userID = '$userID' and banned = 0")->order('create_time desc');
        $mineAfters = $mines->select();
        
        // 接力我的
        $nexts = new MuseModel();
        $nexts->where("prev_userID = '$userID' and banned = 0")->order('create_time desc');
        $afterMines = $nexts->select();
        
        $this->assign('mineAfters', $mineAfters);
        $this->assign('afterMines', $afterMines);
        return $this->fetch('mine');
    }
    
    /**
     * 举报
     */
    public function report()
    {
        $museID = Request::instance()->param('muse_id');
        if (!isset($museID) || $museID == '')
            return $this->error('未获取到情节');
        $muse = MuseModel::get(['museID' => $museID]);
        if (is_null($muse))
            return $this->error('未找到该情节');
        $muse['report_count'] = $muse['report_count'] + 1;
        $muse->validate()->save();
        return $this->success('举报成功，请等待检查');
    }
}