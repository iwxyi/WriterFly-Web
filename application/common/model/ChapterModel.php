<?php
namespace app\common\model;
use think\Model;
use app\common\model\UserModel;

class ChapterModel extends Model
{
    protected $table = 'chapters';
    
    public function getUser() {
        return UserModel::get(['userID' => $this->userID]);
    }
}