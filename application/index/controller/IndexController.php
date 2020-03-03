<?php
namespace app\index\controller;
use think\Controller;
use think\Request;
use app\commom\model\User;

class IndexController extends Controller
{
    public function index()
    {
        return $this->fetch('index');
    }
    
    public function rank()
    {

        
        return $this->fetch('rank');
    }
}
