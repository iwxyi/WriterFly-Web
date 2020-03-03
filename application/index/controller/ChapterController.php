<?php
namespace app\index\controller;
use app\common\model\UserModel;
use app\common\model\NovelModel;
use app\common\model\ChapterModel;
use think\Controller;
use think\Request;

class ChapterController extends Controller
{
    public function index()
    {
        return $this->list();
    }
    
    public function list()
    {
        
    }
}