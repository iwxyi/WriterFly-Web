<?php
namespace app\index\controller;
use app\common\model\UserModel;
use app\common\model\MuseModel;
use think\Controller;
use think\Request;

class MuseController extends Controller
{
    public function __construct()
	{
		// 调用父类的构造函数
		parent::__construct();

		// 验证用户是否登录
		if (!UserModel::isLogin()) {
			return $this->fetch('User/goLogin');
		}
	}
	
    /**
     * 根情节入口
     */
    public function entrance()
    {
        $muses = new MuseModel();
        $muses->where('floor = 1')->order('children_count desc, relay_time desc');
        $muses = $muses->paginate(30);
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
        $muses->order('create_time desc');
        $muses = $muses->paginate(30);
        $this->assign('muses', $muses);
        return $this->fetch('list');
    }
    
    /**
     * 前往创建根情节
     */
    public function goCreate()
    {
        $user = UserModel::currentUser();
        $muse = MuseModel::get(['userID' => session('user_id'),'children_count'=>0]);
        if (!is_null($muse))
            return $this->error('您有情节未被接力，无法新建根情节');
        return $this->fetch('create');
    }
    
    /**
     * 创建根情节
     */
    public function create()
    {
        $content = trim(Request::instance()->param('content'));
        if (mb_strlen($content) >= 300 || mb_strlen($content) < 30)
        {
            $this->assign('hasError', 'true');
            $this->assign('errorReason', '请输入30~300个汉字的内容');
            $this->assign('default', $content);
            return $this->goCreate();
        }
        
        $muse = new MuseModel();
        $muse['parentID'] = '';
        $muse['userID'] = session('user_id');
        $muse['content'] = $content;
        $muse['relay_time'] = $muse['create_time'] = time();
        $muse->validate()->save();
        
        $muse['path'] = '/' . $muse['museID'] . '/';
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
        
        // 获取父情节：create早
        $parents = new MuseModel();
        $parents->where("locate('$museID', path) and create_time<='$create_time'")->order('create_time');
        $parents = $parents->select();
        
        // 获取子情节：create晚
        $children = new MuseModel();
        $children->where("parentID='$museID' and create_time>'$create_time'")->order('create_time desc');
        $children = $children->select();
        
        $this->assign('parents', $parents);
        $this->assign('children', $children);
        return $this->fetch('line');
    }
    
    /**
     * 前往接力（已废弃）
     */
    public function goRelay()
    {
        $museID = Request::instance()->param('muse_id');
        if (!isset($museID) || $museID == '')
            return $this->error('未获取到情节');
        $muse = MuseModel::get(['museID' => $museID]);
        if (is_null($muse))
            return $this->error('未找到该情节');
        if ($muse->userID == session('user_id'))
            return $this->error('不能接力自己的情节');
    }
    
    /**
     * 收到接力
     */
    public function relay()
    {
        $parentID = Request::instance()->param('muse_id');
        if (!isset($parentID) || $parentID == '')
            return $this->error('未获取到情节');
        $parent = MuseModel::get(['museID' => $parentID]);
        if (is_null($parent))
            return $this->error('未找到该情节');
        /* if ($parent->userID == session('user_id'))
            return $this->error('不能接力自己的情节'); */
        $content = Request::instance()->param('content');
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
        $muse['relay_time'] = $muse['create_time'] = time();
        $muse->validate()->save();
        
        $muse['path'] = $parent['path'] . $muse['museID'] . '/';
        $muse->validate()->save();
        
        $parent['children_count'] = $parent['children_count'] + 1;
        $parent['offspring_count'] = $parent['offspring_count'] + 1;
        $parent['relay_time'] = time();
        $parent->validate()->save();
        return $this->success('接力成功', url('Muse/line?muse_id=' . $muse['museID']));
    }
    
    /**
     * - 我的接力
     * - 我的情节
     */
    public function mine()
    {
        
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