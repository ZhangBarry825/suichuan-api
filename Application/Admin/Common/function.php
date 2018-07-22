<?php
/**
 * Created by PhpStorm.
 * User: Barry
 * Date: 2018/5/7
 * Time: 18:44
 */

use PHPMailer\PHPMailer\PHPMailer;
use PHPMsg\PHPMsg\ServerApi;

//require ('./message/ServerApi.php');

//require('./message/ServerAPI.php');

function getUser()
{

    $user = session('user');
    if ($user)
        return $user;
    else
        return false;
}

function sendMsg()
{
    require_once("message/ServerApi.php");


    //网易云信分配的账号，请替换你在管理后台应用下申请的Appkey
    $AppKey = '1c9f7579abb8a32cc5e16274c0402347';
//网易云信分配的账号，请替换你在管理后台应用下申请的appSecret
    $AppSecret = '1fbb161ab31e';
    $p = new ServerAPI($AppKey,$AppSecret,'fsockopen');     //fsockopen伪造请求
    //发送短信验证码
//    print_r( $p->sendSmsCode('6272','13888888888','','5') );

    $url='https://api.netease.im/sms/sendtemplate.action';
    $data['mobile']=15138389776;
    //发送短信
    return $p->sendSmsCode('6272','13888888888','','5');

//发送模板短信
//    print_r( $p->sendSMSTemplate('6272',array('13888888888','13666666666'),array('xxxx','xxxx' )));

}

function sendMail($to,$title,$content){
    //引入PHPMailer的核心文件 使用require_once包含避免出现PHPMailer类重复定义的警告
    require_once ('mailto/PHPMailer.class.php');
    require_once ('mailto/Exception.class.php');
    require_once ('mailto/SMTP.class.php');
    require_once ('mailto/POP3.class.php');
    require_once ('mailto/OAuth.class.php');
    //实例化PHPMailer核心类
    $mail = new PHPMailer();

    //是否启用smtp的debug进行调试 开发环境建议开启 生产环境注释掉即可 默认关闭debug调试模式
//    $mail->SMTPDebug = 1;

    //使用smtp鉴权方式发送邮件
    $mail->isSMTP();

    //smtp需要鉴权 这个必须是true
    $mail->SMTPAuth=true;

    //链接qq域名邮箱的服务器地址
    $mail->Host = 'smtp.163.com';

    //设置使用ssl加密方式登录鉴权
    $mail->SMTPSecure = 'ssl';

    //设置ssl连接smtp服务器的远程服务器端口号，以前的默认是25，但是现在新的好像已经不可用了 可选465或587
    $mail->Port = 465;

    //设置smtp的helo消息头 这个可有可无 内容任意
    // $mail->Helo = 'Hello smtp.qq.com Server';

    //设置发件人的主机域 可有可无 默认为localhost 内容任意，建议使用你的域名
    $mail->Hostname = 'localhost';

    //设置发送的邮件的编码 可选GB2312 我喜欢utf-8 据说utf8在某些客户端收信下会乱码
    $mail->CharSet = 'UTF-8';

    //设置发件人姓名（昵称） 任意内容，显示在收件人邮件的发件人邮箱地址前的发件人姓名
    $mail->FromName = 'PHPMail测试';

    //smtp登录的账号 这里填入字符串格式的qq号即可
    $mail->Username ='1104405460@163.com';

    //smtp登录的密码 使用生成的授权码（就刚才叫你保存的最新的授权码）
    $mail->Password = 'zb530027054';

    //设置发件人邮箱地址 这里填入上述提到的“发件人邮箱”
    $mail->From = '1104405460@163.com';

    //邮件正文是否为html编码 注意此处是一个方法 不再是属性 true或false
    $mail->isHTML(true);


//设置收件人邮箱地址 该方法有两个参数 第一个参数为收件人邮箱地址 第二参数为给该地址设置的昵称 不同的邮箱系统会自动进行处理变动 这里第二个参数的意义不大
    $mail->addAddress($to,'PHPMail测试接收者');



    //添加多个收件人 则多次调用方法即可
    // $mail->addAddress('xxx@163.com','lsgo在线通知');

    //添加该邮件的主题
    $mail->Subject = $title;

    //添加邮件正文 上方将isHTML设置成了true，则可以是完整的html字符串 如：使用file_get_contents函数读取本地的html文件
    $mail->Body = $content;

    //为该邮件添加附件 该方法也有两个参数 第一个参数为附件存放的目录（相对目录、或绝对目录均可） 第二参数为在邮件附件中该附件的名称
    // $mail->addAttachment('./d.jpg','mm.jpg');
    //同样该方法可以多次调用 上传多个附件
    // $mail->addAttachment('./Jlib-1.1.0.js','Jlib.js');

    $status = $mail->send();

    //简单的判断与提示信息
    if($status) {
        return true;
    }else{
        return false;
    }
}

function testMail(){

}

/**
 * 导出excel
 * @param array $data 导入数据
 * @param string $savefile 导出excel文件名
 * @param array $fileheader excel的表头
 * @param string $sheetname sheet的标题名
 * @throws PHPExcel_Reader_Exception
 */
function exportExcel($data, $savefile, $fileheader, $sheetname){
    //引入phpexcel核心文件，不是tp，你也可以用include（‘文件路径’）来引入
    import("Org.Util.PHPExcel");
    import("Org.Util.PHPExcel.Reader.Excel2007");
    //或者excel5，用户输出.xls，不过貌似有bug，生成的excel有点问题，底部是空白，不过不影响查看。
    //import("Org.Util.PHPExcel.Reader.Excel5");
    //new一个PHPExcel类，或者说创建一个excel，tp中“\”不能掉
    $excel = new \PHPExcel();
    if (is_null($savefile)) {
        $savefile = time();
    }else{
        //防止中文命名，下载时ie9及其他情况下的文件名称乱码
        iconv('UTF-8', 'GB2312', $savefile);
    }
    //设置excel属性
    $objActSheet = $excel->getActiveSheet();
    //根据有生成的excel多少列，$letter长度要大于等于这个值
    $letter = array('A','B','C','D','E','F','G','H','I','J','K');
    //设置当前的sheet
    $excel->setActiveSheetIndex(0);
    //设置sheet的name
    $objActSheet->setTitle($sheetname);
    //设置表头
    for($i = 0;$i < count($fileheader);$i++) {
        //单元宽度自适应,1.8.1版本phpexcel中文支持勉强可以，自适应后单独设置宽度无效
        //$objActSheet->getColumnDimension("$letter[$i]")->setAutoSize(true);
        //设置表头值，这里的setCellValue第二个参数不能使用iconv，否则excel中显示false
        $objActSheet->setCellValue("$letter[$i]1",$fileheader[$i]);
        //设置表头字体样式
        $objActSheet->getStyle("$letter[$i]1")->getFont()->setName('微软雅黑');
        //设置表头字体大小
        $objActSheet->getStyle("$letter[$i]1")->getFont()->setSize(12);
        //设置表头字体是否加粗
        $objActSheet->getStyle("$letter[$i]1")->getFont()->setBold(true);
        //设置表头文字垂直居中
        $objActSheet->getStyle("$letter[$i]1")->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        //设置文字上下居中
        $objActSheet->getStyle($letter[$i])->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
        //设置表头外的文字垂直居中
        $excel->setActiveSheetIndex(0)->getStyle($letter[$i])->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    }
    //单独设置D列宽度为15
    $objActSheet->getColumnDimension('A')->setWidth(30);
    $objActSheet->getColumnDimension('B')->setWidth(50);
    $objActSheet->getColumnDimension('C')->setWidth(50);
    $objActSheet->getColumnDimension('D')->setWidth(25);
    $objActSheet->getColumnDimension('E')->setWidth(25);
    $objActSheet->getColumnDimension('F')->setWidth(25);
    $objActSheet->getColumnDimension('G')->setWidth(25);
    //这里$i初始值设置为2，$j初始值设置为0，自己体会原因
    for ($i = 2;$i <= count($data) + 1;$i++) {
        $j = 0;
        foreach ($data[$i - 2] as $key=>$value) {
            //不是图片时将数据加入到excel，这里数据库存的图片字段是img
            if($key != 'img'){
                $objActSheet->setCellValue("$letter[$j]$i",$value);
            }
            //是图片是加入图片到excel
            if($key == 'img'){
                if($value != ''){
                    $value = iconv("UTF-8","GB2312",$value); //防止中文命名的文件
                    // 图片生成
                    $objDrawing[$key] = new \PHPExcel_Worksheet_Drawing();
                    // 图片地址
                    $objDrawing[$key]->setPath('.\Uploads'.$value);
                    // 设置图片宽度高度
                    $objDrawing[$key]->setHeight('80px'); //照片高度
                    $objDrawing[$key]->setWidth('80px'); //照片宽度
                    // 设置图片要插入的单元格
                    $objDrawing[$key]->setCoordinates('D'.$i);
                    // 图片偏移距离
                    $objDrawing[$key]->setOffsetX(12);
                    $objDrawing[$key]->setOffsetY(12);
                    //下边两行不知道对图片单元格的格式有什么作用，有知道的要告诉我哟^_^
                    //$objDrawing[$key]->getShadow()->setVisible(true);
                    //$objDrawing[$key]->getShadow()->setDirection(50);
                    $objDrawing[$key]->setWorksheet($objActSheet);
                }
            }
            $j++;
        }
        //设置单元格高度，暂时没有找到统一设置高度方法
        $objActSheet->getRowDimension($i)->setRowHeight('20px');
    }
    header('Content-Type: application/vnd.ms-excel');
    //下载的excel文件名称，为Excel5，后缀为xls，不过影响似乎不大
    header('Content-Disposition: attachment;filename="' . $savefile . '.xlsx"');
    header('Cache-Control: max-age=0');
    // 用户下载excel
    $objWriter = PHPExcel_IOFactory::createWriter($excel, 'Excel2007');
    $objWriter->save('php://output');
    // 保存excel在服务器上
    //$objWriter = new PHPExcel_Writer_Excel2007($excel);
    //或者$objWriter = new PHPExcel_Writer_Excel5($excel);
    //$objWriter->save("保存的文件地址/".$savefile);
}
