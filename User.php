<?php

namespace app\index\controller;

use app\common\model\UserInfo;
use Firebase\JWT\JWT;
use think\Controller;

class User extends Controller
{
    public function get_pid(){
        $id = $this->uid;
        $UserPid_model = new \app\common\model\UserPid();
        $pid = $UserPid_model->where("user_id",$id)->select();
        $res = ["code" => 1, "msg" => "获取成功","data"=>$pid];
        return json($res);
    }

    public function add_pid(){
        $UserPid_model = new \app\common\model\UserPid();
        $id = $this->uid;
        $request = request();
        $data = $request->param();
        $pid = $data["pid"];
        $UserPid_model->save(["pid"=>$pid,"user_id"=>$id]);
        $res = ["code" => 1, "msg" => "添加成功"];
        return json($res);
    }

    public function del_pid(){
        $UserPid_model = new \app\common\model\UserPid();
        $request = request();
        $data = $request->param();
        $id = $data["id"];
        $userPid = $UserPid_model->where("id",$id)->find();
        $userPid->delete();
        $res = ["code" => 1, "msg" => "删除成功"];
        return json($res);
    }
}
