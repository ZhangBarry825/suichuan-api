<?php
/**
 * Created by PhpStorm.
 * User: Barry
 * Date: 2018/5/7
 * Time: 18:00
 */

namespace Admin\Controller;


use Think\Controller;

class BaseController extends Controller
{
    protected $user;
    protected $needUser = true;

    public function __construct()
    {
        if ($this->needUser) {
            $this->user = getUser();
            if ($this->user === false)
                $this->returnErr('重新登录');
        }
    }

    public function returnErr($msg='err',$data='',$code=400){
        $res['msg']=$msg;
        $res['data']=$data;
        $res['code']=$code;
        $res['time']=time();
        $this->ajaxReturn($res);
    }
    public function returnSuccess($msg='success',$data='',$code=200){
        $res['msg']=$msg;
        $res['data']=$data;
        $res['code']=$code;
        $res['time']=time();
        $this->ajaxReturn($res);
    }
}