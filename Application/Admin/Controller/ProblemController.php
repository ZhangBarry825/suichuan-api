<?php
/**
 * Created by PhpStorm.
 * User: Barry
 * Date: 2018/5/20
 * Time: 15:04
 */

namespace Admin\Controller;


class ProblemController extends BaseController
{
    public function __construct()
    {
        parent::__construct();

        $user = session('user');
        if ($user['type'] != 'admin') {
            $this->returnErr('没有权限');
        }
    }
    public function listall(){
        $page = $_POST['page'];
        $res = D('Task')->page($page, 8)->order('start_time desc')->select();
        $count['num'] = count(D('Task')->select());
        $this->returnSuccess($count, $res);
    }


}