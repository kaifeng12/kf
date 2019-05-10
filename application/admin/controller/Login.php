<?php
namespace app\admin\controller;

use think\Controller;
use think\Db;
use think\App;
use think\captcha\Captcha;
use app\common\model\User;
use app\common\model\Visit;

class Login extends Controller
{
    
    public function initialize(){
        $ip=$this->request->ip();
        $visit = new Visit();
        $visit->ip_filter($ip) == 404 && abort(404);
    }
    
    public function index(){
        if(session('user')){
            $this->redirect('admin/home');
        }
        $userInfo = cookie('usercookie');
        if(!empty($userInfo['name']) && !empty($userInfo['pwd'])){
            $user = new User();
            if($user=$user->checklogin($userInfo['name'],$userInfo['pwd'],'cookie')){
                $this->redirect('admin/home');
            }
        }
        return $this->fetch('');  
    }
    
    //输出验证码
    public function verify(){
        $config=[
            // 验证码字体大小(px)
            'fontSize' => 15,
            // 是否画混淆曲线
            'useCurve' => false,
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
        $param=input();
        (empty($param['capt']) || empty($param['name']) || empty($param['pwd'])) && $this->error('参数错误'); 
        $verify=new Captcha();
        if(!$verify->check($param['capt'])){
            $this->error('验证码错误');
        }
        $user = new User();
        if($user=$user->checklogin($param['name'],$param['pwd'])){
            //验证成功，密码正确
            $this->success('登录成功');
        }else{
            $this->error('用户名或密码错误');
        }
    }

}