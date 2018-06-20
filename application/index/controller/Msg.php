<?php

namespace app\index\controller;

use controller\BaseIndex;
use think\Db;
use think\App;
class Msg extends BaseIndex
{
    
    public function msg(){
        $first=model('msg')->getMsg();
        $login=0;

        if(session('qname') && session('qhead') && session('openid')){
            $this->assign('qname',session('qname'));
            $this->assign('qhead',session('qhead'));
            $this->assign('openid',session('openid'));
            $login=1;
        }
        return $this->fetch('',['login'=>$login,'msgss'=>$first]);
    }
    
    public function add(){
        $rid=$this->request->param('rid');
        $openid=session('openid');
		if(!$openid) $this->error('页面不存在！');
		$text=$this->request->param('text');
        if(!$text) $this->error('发表失败，请稍候再试');
        if(model('msg')->addmsg($openid,$text,$rid)){            
            $this->success('发表成功','msg');
        }else{
            $this->error('发表失败，请稍候再试');
        }        
    }

    
    
    public function callback(){
        $state=isset($_GET['state'])?$_GET['state']:'';
        
        //查找$state是否含有12，并删除前两个字符，剩下的为文章id
        $str=strstr($state, '12');//得到字符串中‘12’后面的字符（包括12）
   
        if(isset($_GET['code']) && $str){
            $l_id=str_replace('12', '', $str);//去除‘12’
            
            $qq=new \Model\QqModel();
            if($user=$qq->callback($_GET['code'])){
                
                session('qname',$user['name']);
                session('qhead',$user['head']);
                session('openid',$user['openid']);
                
                if($l_id=='00'){
                    echo "<script>window.location='http://www.likaifeng.xyz/Home/Msg/msg'</script>";
                }else{
                    echo "<script>window.location='http://www.likaifeng.xyz/Home/Log/read/id/{$l_id}#cmt'</script>";
                }
            }else{
                dump('登录失败');
                exit;
            }
            /*
            $url="https://graph.qq.com/oauth2.0/token?grant_type=authorization_code&client_id=101450092&client_secret=df8d25dfe66beebd1f78e8367c0deda2&code={$_GET['code']}&redirect_uri=http://www.likaifeng.xyz/Home/Msg/callback";
            $Access_Token=file_get_contents($url);
            if($Access_Token){
                $Openidurl="https://graph.qq.com/oauth2.0/me?{$Access_Token}";
                $Openid = file_get_contents($Openidurl);
                $Openid = substr($Openid,45,32);
                $getInfoUrl = "https://graph.qq.com/user/get_user_info?{$Access_Token}&oauth_consumer_key=101450092&openid={$Openid}";
                
                $json = file_get_contents($getInfoUrl);
                $QQUserInfo = json_decode($json,true);
                dump($QQUserInfo['figureurl_qq_2']);
                dump($QQUserInfo['nickname']);
                */
            
        }
          
    }
    
    public function logout(){
        session('qname',null);
        session('qhead',null);
        session('openid',null);
        echo "<script>window.location='http://www.likaifeng.xyz/Home/Msg/msg'</script>";
    }
    
    /*
    public function mail(){
        
        $mail=new \Org\Util\Phpmailer();
        $mail->IsSMTP();                                      //告诉类使用smtp发件
        $mail->SMTPDebug  = 0;                                //是否开启调试模式
        $mail->SMTPAuth   = true;                             //设定SMTP需要验证
        $mail->CharSet    = "utf-8";                          //设置邮件编码
        $mail->Host       = 'smtp.163.com';                 //发件箱服务器
        $mail->Port       = 25;                               //发件箱端口
        $mail->Username   = 'lf100212@163.com';               //发件箱账号
        $mail->Password   = '74123lll';               			//发件箱密码
        $mail->SetFrom('lf100212@163.com');                      //发件箱
        //回复箱
        // $rows=explode('|',$arr['r_email']);
        $mail->AddAddress('664964290@qq.com');                            //收件箱
        $mail->Subject    = '新的在线留言消息';                           //邮箱标题
        $mail->MsgHTML('你好');							      //邮箱内容
        if(!$mail->Send()){
            echo $mail->ErrorInfo;
        }else{
            echo 'yes';
        }
    }
    */

}