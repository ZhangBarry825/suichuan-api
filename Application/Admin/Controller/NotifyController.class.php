<?php
/**
 * Created by PhpStorm.
 * User: Barry
 * Date: 2018/5/29
 * Time: 10:56
 */

namespace Admin\Controller;


class NotifyController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
        $user = session('user');
        if ($user['type'] != 'admin') {
            $this->returnErr('没有权限');
        }
    }
    public function sendMail($to='',$title='',$content='')
    {
        $flag = sendMail($to, $title, $content);
        if ($flag) {
//            echo "发送邮件成功！";
            return true;
        } else {
//            echo "发送邮件失败！";
            return false;
        }
    }
    public function tellEmail(){
        $data=$_POST;
        $title=$_POST['title'];
        $content=$_POST['content'];
        $members=D('Mem_to_dep')->where("site = '".$data['site']."' AND department_id = '".$data['department']."'")->select();
        foreach ($members as $index=>$member){
            $user=D('Member')->where("id = ".$member['member_id'])->field('password',true)->find();
            $res=$this->sendMail($user['email'],$title,$content);
//            while (!$res){
//                $res=$this->sendMail($user['email'],$title,$content);
//            }
        }
//        $this->sendMail('530027054@qq.com','标题','内容');
        $this->returnSuccess('success');
    }


}