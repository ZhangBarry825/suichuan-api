<?php
/**
 * Created by PhpStorm.
 * User: Barry
 * Date: 2018/5/8
 * Time: 10:37
 */

namespace Admin\Controller;


class ColumnController extends BaseController
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
            if($page!=0){
                $lists = D('Column')->where("available = '1' AND site = '1'")->page($page.',8')->select();
            }else{
                $lists = D('Column')->where("available = '1' AND site = '1'")->select();
            }
//        foreach ($lists as $key => $item){
////            if(empty($item['limit_name'])){
////                $lists[$key]
////            }
////        }
            $num['num']=count(D('Column')->where("available = '1' AND site = '1'")->select());
            $this->returnSuccess($num, $lists);
        }elseif ($site==2){
            if($page!=0){
                $lists = D('Column')->where("available = '1' AND site = '2'")->page($page.',8')->select();
            }else{
                $lists = D('Column')->where("available = '1' AND site = '2'")->select();
            }
//        foreach ($lists as $key => $item){
////            if(empty($item['limit_name'])){
////                $lists[$key]
////            }
////        }
            $num['num']=count(D('Column')->where("available = '1' AND site = '2'")->select());
            $this->returnSuccess($num, $lists);

        }elseif ($site==3){
            if($page!=0){
                $lists = D('Column')->where("available = '1' AND site = '3'")->page($page.',8')->select();
            }else{
                $lists = D('Column')->where("available = '1' AND site = '3'")->select();
            }
//        foreach ($lists as $key => $item){
////            if(empty($item['limit_name'])){
////                $lists[$key]
////            }
////        }
            $num['num']=count(D('Column')->where("available = '1' AND site = '3'")->select());
            $this->returnSuccess($num, $lists);
        }


    }

    public function create()
    {
        $user['name'] = $_POST['name'];
        $user['limit_date'] = $_POST['limit_date'];


        $is_had = D('Column')->where("name = '" . $user['name'] . "'")->select();
        if ($is_had) {
            $this->returnErr('name is had');
        }

        $res = D('Column')->data($user)->add();

        if ($res ) {
            $this->returnSuccess('create successful');
        } else {
            $this->returnErr('create error');
        }
    }

    public function update()
    {
        $user = $_POST;
        $is_had = D('Column')->where("id != '".$user['id']."' AND name = '".$user['name']."'")->select();
        if ($is_had) {
            $this->returnErr('name is had');
        }


        if($user['limit_date']==-1){
            $user['limit_name']='适时更新';
        }else if($user['limit_date']==-2){
            $user['limit_name']='每年2月份更新';
        }else if($user['limit_date']==-3){
            $user['limit_name']='每季度更新';
        }else if($user['limit_date']==-4){
            $user['limit_name']='链接失效更新';
        }else if($user['limit_date']==-5){
            $user['limit_name']='变更更新';
        }else if($user['limit_date']==1){
            $user['limit_name']='每天更新';
        }else if($user['limit_date']==14){
            $user['limit_name']='两周更新';
        }else if($user['limit_date']==30){
            $user['limit_name']='每月更新';
        }else if($user['limit_date']==180){
            $user['limit_name']='半年更新';
        }else if($user['limit_date']==365){
            $user['limit_name']='每年更新';
        }else{
            $user['limit_name']='每'.$user['limit_date'].'天更新';
        }

        $res = D('Column')->where("id = '" . $user['id'] . "'")->save($user);

        if ($res) {
            $this->returnSuccess('update successful');
        } else {
            $this->returnErr('error');
        }
    }

    public function detail(){
        $id=$_POST['id'];
        $res=D('Column')->where("id = '" . $id . "'")->find();
        if ($res) {
            $this->returnSuccess('',$res);
        } else {
            $this->returnErr('error');
        }
    }

    public function delete(){
        $id=$_POST['id'];
        $is_had = D('Column')->where("id = '" . $id . "'")->select();
        if (!$is_had) {
            $this->returnErr('id is not had');
        }else{
            D('Column')->where("id = '" . $id . "'")->delete();
            D('Dep_to_col')->where("column_id = '" . $id . "'")->delete();
            $this->returnSuccess('delete successful');
        }

    }


}