<?php
/**
 * Created by PhpStorm.
 * User: Barry
 * Date: 2018/5/15
 * Time: 16:25
 */

namespace Admin\Controller;


class CheckController extends BaseController
{

    public function __construct()
    {
        parent::__construct();
    }

    //手动检测
    public function check()
    {
        $user = session('user');
        if ($user['type'] != 'admin') {
            $this->returnErr('没有权限');
        }


        $data['is_checking'] = 1;
        $res = D('Checking')->where('1 = 1')->save($data);

//        shell_exec("sh /usr/local/doit/do.sh");
//        system("python3 /www/site_checker/site_checker/suichuan_crawler.py && curl -HOST http://suichuan.barry.umdev.cn/admin/index/checked");

        $descriptorspec = array(
            0 => array("pipe", "r"), // 标准输入，子进程从此管道中读取数据
            1 => array("pipe", "w"), // 标准输出，子进程向此管道中写入数据
            2 => array("file", "/tmp/error-output.txt", "a") // 标准错误，写入到一个文件
        );

        $cwd = '/tmp';
        $env = array('some_option' => 'aeiou');

//        $process = proc_open('python3 /www/site_checker/site_checker/suichuan_crawler.py && curl -HOST http://suichuan.barry.umdev.cn/admin/index/checked?num=1', $descriptorspec, $pipes, $cwd, $env);
        $process = proc_open('sh /usr/local/doit/doone.sh', $descriptorspec, $pipes, $cwd, $env);

        if (is_resource($process)) {
// $pipes 现在看起来是这样的：
// 0 => 可以向子进程标准输入写入的句柄
// 1 => 可以从子进程标准输出读取的句柄
// 错误输出将被追加到文件 /tmp/error-output.txt

            fwrite($pipes[0], '<?php print_r($_ENV); ?>');
            fclose($pipes[0]);

            echo stream_get_contents($pipes[1]);
            fclose($pipes[1]);


// 切记：在调用 proc_close 之前关闭所有的管道以避免死锁。
            $return_value = proc_close($process);

            echo "command returned $return_value\n";
        }

    }

    //查询是否检测完成
    public function checking()
    {
        $res = D('Checking')->where('id = 1')->find();
        $res2 = D('Checking')->where('id = 2')->find();
        $res3 = D('Checking')->where('id = 3')->find();
        if ($res['is_checking'] == 1 && $res2['is_checking'] == 1&& $res3['is_checking'] == 1) {
            $this->returnSuccess('success', true);
        }else{
            $this->returnSuccess('success', false);
        }
    }

    public function listall()
    {
        $page = $_POST['page'];
        $res = D('Task')->page($page, 8)->where("end_time != 'null'")->order('start_time desc')->select();
        foreach ($res as $index=>$item){
            if(substr($item['task_id'],7,12)=='www.suichuan'){
                $res[$index]['web_site']='遂川县政府门户网站';
            }elseif (substr($item['task_id'],7,12)=='pub.jian.gov'){
                $res[$index]['web_site']='县政府信息公开平台';
            }

        }
        $count['num'] = count(D('Task')->where("end_time != 'null'")->select());
        $this->returnSuccess($count, $res);
    }

    public function delete()
    {
        $user = session('user');
        if ($user['type'] != 'admin') {
            $this->returnErr('没有权限');
        }

        $id = $_POST['id'];

        $item = D('Task')->where('id = ' . $id)->find();

        $res = D('Task')->where('id = ' . $id)->delete();

        $res2 = D('Links')->where("task_id = '" . $item['task_id'] . "'")->delete();

        if ($res && $res2) {
            $this->returnSuccess('success');
        } else {
            $this->returnErr('error');
        }
    }
}