<?php
/**
 * Created by PhpStorm.
 * User: Barry
 * Date: 2018/5/8
 * Time: 11:19
 */

namespace Admin\Controller;


class DepartmentController extends BaseController
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
        $num=$_POST['num'];
//        print_r($site);

        if($site==1){
            $lists = D('Dep_to_col')->where("site = '1'")->page($page.',8')->select();
            $num['num']=count(D('Dep_to_col')->where("site = '1'")->select());
            $this->returnSuccess($num, $lists);
        } elseif ($site==2){
            $lists = D('Dep_to_col')->where("site = '2'")->page($page.',8')->select();
            $num['num']=count(D('Dep_to_col')->where("site = '2'")->select());
            $this->returnSuccess($num, $lists);
        }elseif ($site==3){
            $lists = D('Dep_to_col')->where("site = '3'")->page($page.',8')->select();
            $num['num']=count(D('Dep_to_col')->where("site = '3'")->select());
            $this->returnSuccess($num, $lists);
        }

    }


    public function deparmentListall()
    {
        $site=$_POST['site'];//判断网站
        $page=$_POST['page'];
        $num=$_POST['num'];
//        print_r($site);

        if($site==1){
            $lists = D('Department')->where("site = '1'")->page($page.',8')->select();
            $num['num']=count(D('Department')->where("site = '1'")->select());
            $this->returnSuccess($num, $lists);
        } elseif ($site==2){
            $lists = D('Department')->where("site = '2'")->page($page.',8')->select();
            $num['num']=count(D('Department')->where("site = '2'")->select());
            $this->returnSuccess($num, $lists);
        }elseif ($site==3){
            $lists = D('Department')->where("site = '3'")->page($page.',8')->select();
            $num['num']=count(D('Department')->where("site = '3'")->select());
            $this->returnSuccess($num, $lists);
        }

    }

    public function create()
    {
        $user = $_POST;
        $name=D('Column')->where("id = '" . $user['column'] . "' AND site = '".$user['site']."'")->field('name')->find();
        $is_had = D('Dep_to_col')->where("department_name = '" . $user['name'] . "' AND column_id ='" . $user['column'] . "' AND site = '".$user['site']."'")->select();
        if ($is_had) {
            $this->returnErr('rule is had');
        }

        $is_dep_had=D('Department')->where("name = '" . $user['name'] . "' AND site = '".$user['site']."'")->select();
        if(!$is_dep_had){
            $res = D('Department')->data($user)->add();
        }

        $id=D('Department')->where("name = '" . $user['name'] . "' AND site = '".$user['site']."'")->field('id')->find();

        $data['department_id']=$id['id'];
        $data['column_id']=$user['column'];

        $data['department_name']=$user['name'];
        $data['column_name']=$name['name'];
        $data['beizhu']=$user['beizhu'];
        $data['site']=$user['site'];

        $res2=D('Dep_to_col')->data($data)->add();

        if ($res2) {
            $this->returnSuccess('create successful');
        } else {
            $this->returnErr('error');
        }
    }

    public function update()
    {
        $user = $_POST;
        $user['site']=$user['aaa'];
        $dep_id=D('Dep_to_col')->where("id = ".$user['id']." AND site = '".$user['site']."'")->find();
        $is_name_had=D('Department')->where("name = '".$user['name']."' AND id != '".$dep_id['department_id']."' AND site = '".$user['site']."'")->select();
        if($is_name_had){
            $this->returnErr('name is had');
        }
        $is_rule_had=D('Dep_to_col')->where("department_name = '".$user['name']."' AND column_id = ".$user['column']." AND site = '".$user['site']."'")->select();
        if($is_rule_had){
            $this->returnErr('rule is had');
        }

        $data0['name']=$user['name'];
        $data0['beizhu']=$user['beizhu'];
        $res=D('Department')->where("id = '".$dep_id['department_id']."' AND site = '".$user['site']."'")->save($data0);

        $column_name=D('Column')->where("id = ".$user['column']." AND site = '".$user['site']."'")->find();
        $data['department_name']=$user['name'];
        $data['column_name']=$column_name['name'];
        $data['column_id']=$user['column'];
        $data['beizhu']=$user['beizhu'];


        $res2=D('Dep_to_col')->where("id = ".$user['id']." AND site = '".$user['site']."'")->save($data);

        $data2['department_name']=$user['name'];
        $res3=D('Dep_to_col')->where("department_name = '".$dep_id['department_name']."' AND site = '".$user['site']."'")->save($data2);

        if ($res2) {
            $this->returnSuccess('update successful');
        } else {
            $this->returnErr('error');
        }
    }

    public function detail(){
        $id=$_POST['id'];
        $res=D('Dep_to_col')->where("id = '" . $id . "'")->find();

        $data['name']=$res['department_name'];
        $data['id']=$res['department_id'];
        $data['column']=$res['column_id'];
        $data['beizhu']=$res['beizhu'];

        if ($res) {
            $this->returnSuccess('',$data);
        } else {
            $this->returnErr('error');
        }
    }

    public function delete(){
        $id=$_POST['id'];
        $site=$_GET['site'];

        $is_had = D('Dep_to_col')->where("id = '" . $id . "' AND site = ".$site)->find();

        if (!$is_had) {
            $this->returnErr('id is not had');
        }else{
            D('Dep_to_col')->where("id = '" . $id . "'")->delete();
            $name_is_had=D('Dep_to_col')->where("department_name = '" . $is_had['department_name'] . "' AND site = ".$site)->select();
            if(!$name_is_had){
                D('Department')->where("id = '" . $is_had['department_id'] . "'  AND site = ".$site)->delete();
                $mem_id=D('Mem_to_dep')->where("department_id = '".$is_had['department_id']."'  AND site = ".$site)->select();
                foreach ($mem_id as $item) {
                    D('Member')->where("id = ".$item['member_id']."  AND site = ".$site)->delete();
                }
                D('Mem_to_dep')->where("department_id = '".$is_had['department_id']."'  AND site = ".$site)->delete();
            }
            $this->returnSuccess('delete successful');
        }

    }
}