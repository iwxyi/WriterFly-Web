<?php
namespace app\index\controller;
use app\common\model\UserModel;
use app\common\model\RoomModel;
use think\Controller;
use think\Request;

class IndexController extends Controller
{
    public function index()
    {
        return $this->fetch('index');
    }
    
    public function rank()
    {
        $type = Request::instance()->param('type');
        $sort = $type;
        $this->assign('rank_name', '码字风云榜');
        if (is_null($type) || $type == 'level')
        {
            $type = 'level';
            $sort = 'level desc';
        }
        else if ($type == 'yestoday')
        {
            $sort = 'words_yestoday desc';
            $this->assign('rank_name', '昨日风云榜');
        }
        else if ($type == 'today')
        {
            $sort = new \think\db\Expression('allwords - allwords_yestoday desc');
            $this->assign('rank_name', '今日风云榜');
        }
        else if ($type == 'room' || $type == 'myroom')
            $sort = 'level DESC';
        else
        {
            $type = 'level';
            $sort = 'level desc';
        }

        if ($type != 'room' && $type != 'myroom')
        {
            $users = new UserModel();
            $time = time();
            $users->where("allwords>allwords_yestoday or words_yestoday>0 or VIP_deadline>0 or sync_time>$time")
                  ->order($sort);
            $users = $users->select();
            
            $this->assign('users', $users);
            $this->assign('time', $time);
            $this->assign('online_time', $time - 900);
            return $this->fetch('rank');
        }
        else if ($type == 'room')
        {
            $rooms = new RoomModel();
            $rooms->order($sort);
            $rooms = $rooms->select();
            
            $this->assign('rooms', $rooms);
            return $this->fetch('rooms');
        }
        else if ($type == 'myroom')
        {
            $roomID = session('room_id');
            if ($roomID == '')
                return $this->error('您尚未加入房间', url('Index/rank?type=room'));
            $users = new UserModel();
            $time = time();
            $users->where("roomID = '$roomID'")
                  ->order($sort);
            $users = $users->select();
            
            $room = RoomModel::get(['roomID' => $roomID]);
            $this->assign('rank_name', $room->roomname);
            $this->assign('users', $users);
            $this->assign('time', $time);
            $this->assign('online_time', $time - 900);
            return $this->fetch('rank');
        }
    }
}
