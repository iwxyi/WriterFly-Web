<?php
namespace app\common\model;
use think\Model;

class MuseModel extends Model
{
    protected $table = 'muses';
    
    public function getUserName()
    {
        $user = UserModel::get(['userID' => $this->userID]);
        if (is_null($user))
            return '';
        return $user->getName();
    }
    
    public function getLine($museID)
    {
        
    }
}