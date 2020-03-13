<?php
namespace app\common\model;
use think\Model;

class ChapterLikeModel extends Model
{
    protected $table = 'chapter_likes';
    
    static public function isLiked($chapterID, $userID)
    {
        return ChapterLikeModel::get(['chapterID' => $chapterID, 'userID' => $userID]) != null;
    }
}