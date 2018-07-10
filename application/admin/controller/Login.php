<?php
namespace app\admin\controller;

use think\Controller;
use think\Db;
use think\App;
use think\captcha\Captcha;
class Login extends Controller
{
    public function index(){
        if(session('user')){
            $this->redirect('admin/home');
        }
        return $this->fetch('');  
    }
    
    //输出验证码
    public function verify(){
        $config=[
            // 验证码字体大小(px)
            'fontSize' => 14,
            // 是否画混淆曲线
            'useCurve' => true,
            // 验证码图片高度
            'imageH'   => 40,
            // 验证码图片宽度
            'imageW'   => 150,
            // 验证码位数
            'length'   => 4,
            // 验证成功后是否重置
            'reset'    => true
        ];
        $captcha=  new Captcha($config);
        return $captcha->entry();
        
    }
    
    //登录验证，ajax
    public function checklogin(){
        $param=$this->request->param();
        $verify=new Captcha();
        if(!$verify->check($param['capt'])){
            echo -1;
            exit;
        }
        $ip=$this->request->ip();
        $status=model('visit')->ip_filter($ip);
        if($status==404) return 0;
        if($user=model('user')->checklogin($param['name'],$param['pwd'])){
            //验证成功，密码正确
            echo 1;
        }else{
            echo 0;
        }
    }

}