<?php

namespace app\index\controller;

use app\common\model\UserInfo;
use Firebase\JWT\JWT;
use think\Controller;


header('Access-Control-Allow-Origin:*'); //允许跨域
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    header('Access-Control-Allow-Headers:x-requested-with,content-type,authorization'); //浏览器页面ajax跨域请求会请求2次，第一次会发送OPTIONS预请求,不进行处理，直接exit返回，但因为下次发送真正的请求头部有带token，所以这里设置允许下次请求头带token否者下次请求无法成功
    exit("ok");
}
class Index extends Controller
{
    public function regist()
    {
        $request = request();
        $data = $request->param();
        $validate =  new \think\Validate([
            'password|密码' => 'require',
            'password1|确认密码' => 'require|confirm:password',
            'mobile|手机号' => 'require|unique:user',
            'code|邀请码' => 'require'
        ]);
        if (!$validate->check($data["form"])) {
            $res = ["code" => 0, "msg" => $validate->getError()];
            return json($res);
        }
        $User_model = new \app\common\model\User();
        $UserPid_model = new \app\common\model\UserPid();
        $msg = [
            "mobile" => $data["form"]["mobile"],
            "password" => md5($data["form"]["password"]),
            "code" => $data["form"]["code"],
        ];
        $userInfo_model = new UserInfo();
        $userInfo = $userInfo_model->where("code", $data["form"]["code"])->where("is_used",0)->find();
        if ($userInfo) {
            $pid = $userInfo["pid"];
            $userInfo["is_used"] = 1;
            $userInfo->save();
            $id = $User_model->insertGetId($msg);
            if ($id) {
                $UserPid_model->save(["pid"=>$pid,"user_id"=>$id]);
                $res = ["code" => 1, "msg" => "注册成功"];
                return json($res);
            } else {
                $res = ["code" => 0, "msg" => "注册失败"];
                return json($res);
            }
        } else {
            $res = ["code" => 0, "msg" => "邀请码错误"];
            return json($res);
        }
    }

    public function login()
    {
        $User_model = new \app\common\model\User();
        $request = request();
        $data = $request->param();
        $validate =  new \think\Validate([
            'mobile|手机号' => 'require',
            'password|密码' => 'require',
        ]);
        if (!$validate->check($data["form"])) {
            $res = ["code" => 0, "msg" => $validate->getError()];
            return json($res);
        } else {
            $where = ["mobile" => $data["form"]["mobile"], "password" => md5($data["form"]["password"])];
            $user = $User_model->where($where)->find();
            if ($user) {
                $key = "gongjingrong";
                $token = [
                    'iat' => time(), //签发时间
                    'nbf' => time(), //(Not Before)：某个时间点后才能访问，比如设置time+30，表示当前时间30秒后才能使用
                    'exp' => time() + 604800, //过期时间,这里设置604800
                    'data' => [ //自定义信息，不要定义敏感信息
                        'userid' => $user->id,
                        'role' => "admin"
                    ]
                ];
                $jwtToken = JWT::encode($token, $key);
                $res = ["code" => 1, "msg" => "登录成功", "data" => $jwtToken];
                return json($res);
            } else {
                $res = ["code" => 0, "msg" => "用户名或密码错误"];
                return json($res);
            }
        }
    }
}
