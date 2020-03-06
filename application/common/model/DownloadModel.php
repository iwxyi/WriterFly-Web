<?php
namespace app\common\model;
use think\Model;
use app\common\model\UserModel;

class DownloadModel extends Model
{
    protected $table = 'downloads';
    
    static public function getCount() {
        return (new DownloadModel())->count();
    }
}