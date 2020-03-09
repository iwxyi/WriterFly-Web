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
	
    public function entrance()
    {
        $muses = new MuseModel();
        $muses->where('floor = 1')->order('children_count desc, relay_time desc');
        $muses = $muses->paginate(30);
        $this->assign('muses', $muses);
        return $this->fetch('list');
    }
    
    public function latest()
    {
        $muses = new MuseModel();
        $muses->order('relay_time desc');
        $muses = $muses->paginate(30);
        $this->assign('muses', $muses);
        return $this->fetch('list');
    }
    
    public function goCreate()
    {
        return $this->fetch('create');
    }
    
    public function create()
    {
        $content = trim(Request::instance()->param('content'));
        if (mb_strlen($content) >= 300 || mb_strlen($content) < 30)
        {
            $this->assign('hasError', 'true');
            $this->assign('errorReason', '请输入30~300个汉字的内容');
            $this->assign('default', $content);
            return $this->error($content);
            return $this->goCreate();
        }
        
        $muse = new MuseModel();
        $muse['fromID'] = '';
        $muse['userID'] = session('user_id');
        $muse['content'] = $content;
        $muse['relay_time'] = $muse['create_time'] = time();
        $muse->validate()->save();
        
        $muse['path'] = '/' . $muse['museID'] . '/';
        $muse->validate()->save();
        return $this->success('创建情节成功', url('Muse/latest'));
    }
        
    public function goRelay()
    {
        
    }
    
    public function relay()
    {
        
    }
    
    public function line()
    {
        
    }
    
    public function mine()
    {
        
    }
}