<?php
/**
 * Created by PhpStorm.
 * User: Barry
 * Date: 2018/5/8
 * Time: 10:37
 */

namespace Admin\Controller;


class MemberController extends BaseController
{
    public function __construct()
    {
        parent::__construct();

        $user = session('user');
        if ($user['type'] != 'admin') {
            $this->returnErr('没有权限');
        }
    }

    public function listall()
    {
        $site=$_POST['site'];//判断网站
        $page=$_POST['page'];
        if($site==1){
            $lists = D('Member')->where("site = '1'")->page($page.',8')->field('password',true)->select();
            foreach ($lists as $key => $item){
                $dep=D('Mem_to_dep')->where("member_id ='".$item['id']."' AND site = '1'")->select();
                $lists[$key]['department']=$dep[0]['department_name'];
            }

            $num['num']=count(D('Member')->where("site = '1'")->select());

            $this->returnSuccess($num, $lists);

        }elseif ($site==2){
            $lists = D('Member')->where("site = '2'")->page($page.',8')->field('password',true)->select();

            foreach ($lists as $key => $item){
                $dep=D('Mem_to_dep')->where("member_id ='".$item['id']."' AND site = '2'")->select();
                $lists[$key]['department']=$dep[0]['department_name'];
            }

            $num['num']=count(D('Member')->where("site = '2'")->select());

            $this->returnSuccess($num, $lists);

        }elseif ($site==3){
            $lists = D('Member')->where("site = '3'")->page($page.',8')->field('password',true)->select();

            foreach ($lists as $key => $item){
                $dep=D('Mem_to_dep')->where("member_id ='".$item['id']."' AND site = '3'")->select();
                $lists[$key]['department']=$dep[0]['department_name'];
            }

            $num['num']=count(D('Member')->where("site = '3'")->select());

            $this->returnSuccess($num, $lists);

        }

    }

    public function create()
    {
        $site=$_POST['site'];//判断网站
        $user['name'] = $_POST['name'];
        $user['password'] = $_POST['password'];
        $user['phone'] = $_POST['phone'];
        $user['email'] = $_POST['email'];
        $user['beizhu'] = $_POST['beizhu'];
        $user['department_id'] = $_POST['department'];
        $user['site'] = $_POST['site'];
        $department = $_POST['department'];

        if($site==1){
            $is_had = D('Member')->where("name = '" . $user['name'] . "' AND site = '1'")->select();
            if ($is_had) {
                $this->returnErr('name is had');
            }
            $user['password'] = md5($user['password']);
            $user['type'] = 'mem';
            $res = D('Member')->data($user)->add();
            $id=D('Member')->where("name = '" . $user['name'] . "' AND site = '1'")->field('id')->find();
            $data['member_id']=$id['id'];
            $data['department_id']=$user['department_id'];
            $data['member_name']=$user['name'];
            $data['site']=$user['site'];
            $name=D('Department')->where("id = '" . $user['department_id'] . "' AND site = '1'")->field('name')->find();
            $data['department_name']=$name['name'];
            $res2=D('Mem_to_dep')->data($data)->add();
            if ($res && $res2) {
                $this->returnSuccess('create successful');
            } else {
                $this->returnErr('create error');
            }
        }elseif ($site==2){
            $is_had = D('Member')->where("name = '" . $user['name'] . "' AND site = '2'")->select();
            if ($is_had) {
                $this->returnErr('name is had');
            }
            $user['password'] = md5($user['password']);
            $user['type'] = 'mem';
            $res = D('Member')->data($user)->add();
            $id=D('Member')->where("name = '" . $user['name'] . "' AND site = '2'")->field('id')->find();
            $data['member_id']=$id['id'];
            $data['department_id']=$user['department_id'];
            $data['member_name']=$user['name'];
            $data['site']=$user['site'];
            $name=D('Department')->where("id = '" . $user['department_id'] . "' AND site = '2'")->field('name')->find();
            $data['department_name']=$name['name'];
            $res2=D('Mem_to_dep')->data($data)->add();
            if ($res && $res2) {
                $this->returnSuccess('create successful');
            } else {
                $this->returnErr('create error');
            }
        }elseif ($site==3){
            $is_had = D('Member')->where("name = '" . $user['name'] . "' AND site = '3'")->select();
            if ($is_had) {
                $this->returnErr('name is had');
            }
            $user['password'] = md5($user['password']);
            $user['type'] = 'mem';
            $res = D('Member')->data($user)->add();
            $id=D('Member')->where("name = '" . $user['name'] . "' AND site = '3'")->field('id')->find();
            $data['member_id']=$id['id'];
            $data['department_id']=$user['department_id'];
            $data['member_name']=$user['name'];
            $data['site']=$user['site'];
            $name=D('Department')->where("id = '" . $user['department_id'] . "' AND site = '3'")->field('name')->find();
            $data['department_name']=$name['name'];
            $res2=D('Mem_to_dep')->data($data)->add();
            if ($res && $res2) {
                $this->returnSuccess('create successful');
            } else {
                $this->returnErr('create error');
            }
        }

    }

    public function updatemyself()
    {
        $user = $_POST;
        $site=$_POST['site'];//判断网站

        $is_had = D('Member')->where("id != '".$user['id']."' AND name = '".$user['name']."' AND site = ".$site)->select();
        if ($is_had) {
            $this->returnErr('name is had');
        }

        $user['password'] = md5($user['password']);
        if($user['type']!='admin'){
            $user['type'] = 'mem';
        }
        $res = D('Member')->where("id = '" . $user['id'] . "' AND site = ".$site)->save($user);
        $data['member_id']=$user['id'];
        $data['department_id']=$user['department'];
        $name=D('Department')->where("id = '".$data['department_id']."'")->find();
        $data['department_name']=$name['name'];
        $data['member_name']=$user['name'];
        $res2=D('Mem_to_dep')->where("member_id = ".$user['id']." AND site = ".$site)->save($data);
        $this->returnSuccess('update successful');
        if ( $res2) {
            $this->returnSuccess('update successful');
        } else {
            $this->returnErr('error');
        }
    }

    public function detail(){
        $id=$_POST['id'];
        $res=D('Member')->where("id = '" . $id . "'")->field('password',true)->find();

        $column=D('Mem_to_dep')->where("member_id = '".$id."'")->find();
        $res['department']=$column['department_id'];

        if ($res) {
            $this->returnSuccess('',$res);
        } else {
            $this->returnErr('error');
        }
    }

    public function delete(){
        $id=$_POST['id'];
        $is_had = D('Member')->where("id = '" . $id . "'")->select();
        if (!$is_had) {
            $this->returnErr('id is not had');
        }else{
            D('Member')->where("id = '" . $id . "'")->delete();
            D('Mem_to_dep')->where("member_id = '" . $id . "'")->delete();
            $this->returnSuccess('delete successful');
        }

    }


}