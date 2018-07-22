<?php

namespace Admin\Controller;

use Think\Controller;

class IndexController extends BaseController
{

    public function __construct()
    {
        $this->needUser = false;
        parent::__construct();
    }

    public function index()
    {
//        $date=date("Y-m-d H:i:s",time());

//        $date=date("Y-m-d H:i:s",'2018-06-09 19:00:07');
        $date=strtotime('2018-06-05 19:00:07');
        $this->returnSuccess(time()-$date,(time()-$date)/(60*60*24));
    }

    public function login()
    {
        session(null);
        $data = $_POST;

        $res = D('Member')->where("name = '" . $data['name'] . "' AND password = '" . md5($data['password']) . "'")->find();
        $return['name'] = $res['name'];
        $return['phone'] = $res['phone'];
        $return['email'] = $res['email'];
        $return['type'] = $res['type'];


        $item=D('Mem_to_dep')->where("member_id = '".$res['id']."'")->find();
        $return['dep'] = $item['department_name'];
        if ($res) {
            session('user', $return);
            $this->returnSuccess('login success', $return);
        } else {
            $this->returnErr('login error', $data);
        }

    }

    public function login2()
    {
        session(null);
        $data = json_decode($GLOBALS['HTTP_RAW_POST_DATA'], true);

        $res = D('Member')->where("name = '" . $data['name'] . "' AND password = '" . md5($data['password']) . "'")->select();
        $return['name'] = $res[0]['name'];
        $return['phone'] = $res[0]['phone'];
        $return['email'] = $res[0]['email'];
        $return['type'] = $res[0]['type'];
        if ($res) {
            session('user', $return);
            $this->returnSuccess('login success');
        } else {
            $this->returnErr('login error', $data);
        }
    }


    public function unlogin()
    {
        session(null);
        $this->returnSuccess('login out');
    }

    public function sendMail($to='',$title='',$content=''){
//    public function sendMail(){
//    {
////        $to=$_GET['to'];
////        $title=$_GET['title'];
////        $content=$_GET['content'];
        $flag = sendMail($to, $title, $content);
//        $flag = sendMail("530027054@qq.com", date("Y-m-d,H:i:s"), '456456');
        if ($flag) {
            echo "发送邮件成功！";
        } else {
            echo "发送邮件失败！";
        }
    }

    public function sendMsg()
    {
        print_r(sendMsg());
    }

    public function exportData()
    {
        $task_id = $_GET['task_id'];
        $task_num='';
        if(substr($task_id,7,12)=='www.suichuan'){
            $task_num=1;
            D('Update_list')->where('1 = 1')->delete();
            $departments = D('Department')->where("site = '".$task_num."'")->select();
            $update_list = null;
            foreach ($departments as $index => $department) {
                $columns1 = D('Dep_to_col')->where("department_id = " . $department['id']." AND site = '".$task_num."'")->select();
                foreach ($columns1 as $i => $value) {
                    $data2 = D('Columns_updates')->where("task_id = '" . $task_id . "'  AND ptitle = '" . $value['column_name'] . "'")->select();
                    $column = D('Column')->where(" id = " . $value['column_id']." AND site = '".$task_num."'")->find();
                    $max = '';
                    $url = '';
                    $check_date='';
                    foreach ($data2 as $i2 => $value2) {
                        $check_date=$value2['check_date'];
                        $max = $data2[$i2]['sdate'];
                        $url = $data2[$i2]['purl'];
                        if ($max < $value2['sdate']) {
                            $max = $value2['sdate'];
                            $url = $value2['purl'];
                        }
                    }
                    $update_list['check_date'] = $check_date;
                    $update_list['department'] = $department['name'];
                    $update_list['column'] = $value['column_name'];
                    $update_list['url'] = $url;
                    $update_list['recent_date'] = $max;
                    $update_list['limit_date'] = $column['limit_date'];
                    $update_list['limit_name'] = $column['limit_name'];
                    $update_list['limit_num'] = $column['limit_num'];

                    if($update_list['limit_date']==-1){
                        $update_list['next_date'] = '适时更新';
                    }elseif ($update_list['limit_date']==-2){
                        $recent_date=$update_list['recent_date'];
                        $update_list['next_date'] = date("Y-m-d",strtotime("$recent_date + 1 year"));
                    }elseif ($update_list['limit_date']==-3){
                        $recent_date=$update_list['recent_date'];
                        $update_list['next_date'] = date("Y-m-d",strtotime("$recent_date + 3 month"));
                    }elseif ($update_list['limit_date']==-4){
                        $recent_date=$update_list['recent_date'];
                        $update_list['next_date'] = "链接失效时更新";
                    }elseif ($update_list['limit_date']==-5){
                        $recent_date=$update_list['recent_date'];
                        $update_list['next_date'] = "变更更新";
                    }else{
                        $recent_date=$update_list['recent_date'];
                        $limit_date=$update_list['limit_date'];
                        $update_list['next_date'] = date("Y-m-d",strtotime("$recent_date + ".$limit_date." day"));
                    }
                    //判断是否超时

                    if(strtotime($update_list['next_date'])<time()){
                        $update_list['is_time_out']='超时';
                    }elseif(strtotime($update_list['next_date'])>time()){
                        $update_list['is_time_out']=' ';
                    }
                    if($update_list['next_date']=='适时更新'||$update_list['next_date']=='链接失效时更新'||$update_list['next_date']=='变更更新'){
                        $update_list['is_time_out']=' ';
                    }
//
                    D('Update_list')->add($update_list);
                }
            }

            $outDate = D('Update_list')->field('check_date')->select();
            $typeData = D('Update_list')->field('department,column,url,recent_date,next_date,limit_name,is_time_out')->select();
//        print_r($typeData[0]);
            $fileheader = array('单位', '栏目', '该栏目最新信息链接', '该栏目最新信息日期','下次更新日期', '更新周期','是否超时');
            exportExcel($typeData, '检测记录-' . $outDate[0][check_date], $fileheader, 'Sheet1');
        }elseif (substr($task_id,7,12)=='pub.jian.gov'){
            $task_num=2;
            D('Update_list')->where('1 = 1')->delete();
            $departments = D('Department')->where("site = '".$task_num."'")->select();
            $update_list = null;
            foreach ($departments as $index => $department) {
                $columns1 = D('Dep_to_col')->where("department_id = " . $department['id']." AND site = '".$task_num."'")->select();
                foreach ($columns1 as $i => $value) {
                    $column = D('Column')->where(" id = " . $value['column_id']." AND site = '".$task_num."'")->find();
                    $data2 = D('Columns_updates')->where("task_id = '" . $task_id . "'  AND purl = '" . $column['purl'] . "'")->select();
                    if($data2){
                        $max = '';
                        $url = '';
                        $check_date='';
                        foreach ($data2 as $i2 => $value2) {
                            $check_date=$value2['check_date'];
                            $max = $data2[$i2]['sdate'];
                            $url = $data2[$i2]['purl'];
                            if ($max < $value2['sdate']) {
                                $max = $value2['sdate'];
                                $url = $value2['purl'];
                            }
                        }
                        $update_list['check_date'] = $check_date;
                        $update_list['department'] = $department['name'];
                        $update_list['column'] = $value['column_name'];
                        $update_list['url'] = $url;
                        $update_list['recent_date'] = $max;
                        $update_list['limit_date'] = $column['limit_date'];
                        $update_list['limit_name'] = $column['limit_name'];
                        $update_list['limit_num'] = $column['limit_num'];

                        if($update_list['limit_date']==-1){
                            $update_list['next_date'] = '适时更新';
                        }elseif ($update_list['limit_date']==-2){
                            $recent_date=$update_list['recent_date'];
                            $update_list['next_date'] = date("Y-m-d",strtotime("$recent_date + 1 year"));
                        }elseif ($update_list['limit_date']==-3){
                            $recent_date=$update_list['recent_date'];
                            $update_list['next_date'] = date("Y-m-d",strtotime("$recent_date + 3 month"));
                        }elseif ($update_list['limit_date']==-4){
                            $recent_date=$update_list['recent_date'];
                            $update_list['next_date'] = "链接失效时更新";
                        }elseif ($update_list['limit_date']==-5){
                            $recent_date=$update_list['recent_date'];
                            $update_list['next_date'] = "变更更新";
                        }else{
                            $recent_date=$update_list['recent_date'];
                            $limit_date=$update_list['limit_date'];
                            $update_list['next_date'] = date("Y-m-d",strtotime("$recent_date + ".$limit_date." day"));
                        }
                        //判断是否超时
                        if(strtotime($update_list['next_date'])<time()){
                            $update_list['is_time_out']='超时';
                        }elseif(strtotime($update_list['next_date'])>time()){
                            $update_list['is_time_out']=' ';
                        }
                        if($update_list['next_date']=='适时更新'||$update_list['next_date']=='链接失效时更新'||$update_list['next_date']=='变更更新'){
                            $update_list['is_time_out']=' ';
                        }
                        D('Update_list')->add($update_list);
                    }

                }
            }

            $outDate = D('Update_list')->field('check_date')->select();
            $typeData = D('Update_list')->field('department,column,url,recent_date,next_date,limit_name,is_time_out')->select();
//        print_r($typeData[0]);
            $fileheader = array('单位', '栏目', '该栏目最新信息链接', '该栏目最新信息日期','下次更新日期', '更新周期','是否超时');
            exportExcel($typeData, '检测记录-' . $outDate[0][check_date], $fileheader, 'Sheet1');

        }elseif (substr($task_id,7,12)=='jasc.jxzwfww'){
            $task_num=3;
        }

   }

    public function exportFail()
    {
        $task_id = $_GET['task_id'];
        $date = date("Y-m-d", time());
        D('Failed_links')->where("task_id = '" . $task_id . "' AND url = 'http://jx.gsxt.gov.cn' OR url = 'http://www.mofcom.gov.cn' OR url = 'http://www.most.gov.cn' OR url = 'http://www.fmprc.gov.cn/web'")->delete();
        $data = D('Failed_links')->where("task_id = '" . $task_id . "'")->field("task_id,url,title,status_code,error,sdate")->distinct(true)->select();
        $fileheader = array('task_id','url', 'title','status_code', 'error', 'sdate');
        exportExcel($data, '问题记录' , $fileheader, 'Sheet1');
    }


    public function executeTable(){
        $Model = new \Think\Model();
        $res000=$Model->execute("
        create table link_title_count (select url, title, count(*) as ct from links group by url, title);
        create table link_title_map (select url, title, ct from link_title_count where title <> '' group by url having ct = max(ct));
        update links, link_title_map
        set links.title = link_title_map.title
        where links.url = link_title_map.url;
        update links, link_title_map
        set links.ptitle = link_title_map.title
        where links.purl = link_title_map.url;");
    }
    //检测完成回调
    public function checked()
    {
        $num = $_GET['num'];
        $data['is_checking'] = 0;
        $res = D('Checking')->where("id = '" . $num."'")->save($data);

        if($num==1){
            $task = D('Task')->where("task_id LIKE '%http://www.suichuan.gov.cn%' AND end_time is null")->find();
        }elseif ($num==2){
            $task = D('Task')->where("task_id LIKE '%http://pub.jian.gov.cn/jxsc%' AND end_time is null")->find();
        }elseif ($num==3){
            $task = D('Task')->where("task_id LIKE '%http://jasc.jxzwfww.gov.cn/jazwfw%' AND end_time is null")->find();
        }
        $end['end_time'] = date('Y-m-d,H:i:s', time());
        D('Task')->where("id = '" . $task['id']."'")->save($end);
        $Model = new \Think\Model();

        $this->executeTable();

        $res00 = $Model->execute("update links, (
        select status_code, error, sdate, url, task_id from links where task_id = '".$task['task_id']."' and status_code <> 0) as t1
        set links.status_code = t1.status_code,
        links.error = t1.error,
        links.sdate = t1.sdate
        where links.url = t1.url and links.task_id = t1.task_id;");

        //错误的链接
        $res01=$Model->query("select * from links where task_id = '".$task['task_id']."' and status_code <> 200");
        foreach ($res01 as $index => $value) {
            $value['check_date']=date("Y-m-d",time());
            $res2 = D('Failed_links')->add($value);
        }

        //最新日期链接
        $res02 = $Model->query("select purl, ptitle, max(sdate) from links where task_id = '".$task['task_id']."' GROUP BY purl");
        foreach ($res02 as $index => $value) {
            $add[$index]['task_id'] = $task['task_id'];
            $add[$index]['purl'] = $value['purl'];
            $add[$index]['ptitle'] = $value['ptitle'];
            $add[$index]['sdate'] = $value['max(sdate)'];
            $add[ $index]['check_date'] = date('Y-m-d', time());
            $res3 = D('Columns_updates')->add($add[$index]);
        }

        //删除link_title_count、link_title_map表
        $res03=$Model->execute("drop table link_title_count,link_title_map");

//        检测提醒超时
        $this->checkTimeOut($task['task_id']);

        if($num==2){


            //先清空Links数据表
            $deleteLinks=D('Links')->where('1 = 1')->delete();

            //清空七天前的Failed_links和Columns_updates表
            $tasks=D('Task')->select();
            $del_days='';
            foreach ($tasks as $index=>$item){
                $date=strtotime($item['start_time']);
                $del_days[$index]=(time()-$date)/(60*60*24);
                if($del_days[$index]>7){
                    D('Columns_updates')->where("task_id = '".$item['task_id']."'")->delete();
                    D('Failed_links')->where("task_id = '".$item['task_id']."'")->delete();
                    D('Task')->where("task_id = '".$item['task_id']."'")->delete();
                }
            }
            $this->returnSuccess(gettype($del_days),$deleteLinks);

        }
    }

    public function checkTimeOut($task_id){
        $task_num='';
        if(substr($task_id,7,12)=='www.suichuan'){
            $task_num=1;
        }elseif (substr($task_id,7,12)=='pub.jian.gov'){
            $task_num=2;
        }elseif (substr($task_id,7,12)=='jasc.jxzwfww'){
            $task_num=3;
        }

//        D('Update_list')->where('1 = 1')->delete();
        $departments = D('Department')->where("site = '".$task_num."'")->select();
        $update_list = null;
        foreach ($departments as $index => $department) {
            $columns1 = D('Dep_to_col')->where("department_id = " . $department['id']." AND site = '".$task_num."'")->select();
            foreach ($columns1 as $i => $value) {
                $data2 = D('Columns_updates')->where("task_id = '" . $task_id . "'  AND ptitle = '" . $value['column_name'] . "'")->select();
                $column = D('Column')->where(" id = " . $value['column_id']." AND site = '".$task_num."'")->find();
                $max = '';
                $url = '';
                foreach ($data2 as $i2 => $value2) {
                    $max = $data2[$i2]['sdate'];
                    $url = $data2[$i2]['purl'];
                    if ($max < $value2['sdate']) {
                        $max = $value2['sdate'];
                        $url = $value2['purl'];
                    }
                }
                $update_list['check_date'] = date('Y-m-d',time());
                $update_list['department'] = $department['name'];
                $update_list['column'] = $value['column_name'];
                $update_list['url'] = $url;
                $update_list['recent_date'] = $max;
                $update_list['limit_date'] = $column['limit_date'];
                $update_list['limit_name'] = $column['limit_name'];
                $update_list['limit_num'] = $column['limit_num'];
                $update_list['site'] = $task_num;

                if($update_list['limit_date']==-1){
                    $update_list['next_date'] = '适时更新';
                }elseif ($update_list['limit_date']==-2){
                    $recent_date=$update_list['recent_date'];
                    $update_list['next_date'] = date("Y-m-d",strtotime("$recent_date + 1 year"));
                }elseif ($update_list['limit_date']==-3){
                    $recent_date=$update_list['recent_date'];
                    $update_list['next_date'] = date("Y-m-d",strtotime("$recent_date + 3 month"));
                }elseif ($update_list['limit_date']==-4){
                    $recent_date=$update_list['recent_date'];
                    $update_list['next_date'] = "链接失效时更新";
                }elseif ($update_list['limit_date']==-5){
                    $recent_date=$update_list['recent_date'];
                    $update_list['next_date'] = "变更更新";
                }else{
                    $recent_date=$update_list['recent_date'];
                    $limit_date=$update_list['limit_date'];
                    $update_list['next_date'] = date("Y-m-d",strtotime("$recent_date + ".$limit_date." day"));
                }
//                //判断是否超时

                if($update_list['next_date']=='适时更新'||$update_list['next_date']=='链接失效时更新'||$update_list['next_date']=='有变更时更新'){
                    $update_list['is_time_out']='否';
                }else{
                    if(strtotime($update_list['next_date'])<time()){
                        $update_list['is_time_out']='超时';

                        $members=D('Mem_to_dep')->where("site = '".$update_list['site']."' AND department_name = '".$update_list['department']."'")->select();
                        foreach ($members as $index=>$member){
                            $user=D('Member')->where("id = ".$member['member_id'])->field('password',true)->find();
//                            print_r($user['email'],'遂川网站内容更新超时',$member['department_name']."的".$user['name'].",".$update_list['column']."栏目下有内容更新超时，请及时更新。");
                            $this->sendMail($user['email'],'遂川网站内容更新超时',$member['department_name']."的".$user['name']."，".$update_list['column']."栏目下有内容更新超时，请及时更新。");
                        }


                    }
                }
//
//                D('Update_list')->add($update_list);
            }
        }

    }

}