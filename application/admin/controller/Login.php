<?php
namespace app\admin\controller;

use controller\BaseAdmin;
use think\Db;
use think\App;
use think\captcha\Captcha;
class Login extends BaseAdmin 
{
    public function index(){       
        return $this->fetch('');  
    }
    
    
    //输出验证码
    public function captchar(){
        $config=array(
            'imageW'=> 150,     //宽
            'imageH'=> 35,      //高
            'fontSize'=> 20,    //字体大小
            'length' => 4,      
            'useNoise' => false,    //去除干扰点
        );
        ob_clean();
        $verify= new \Think\Verify($config);
        $verify->entry();
        
    }
    
    //登录验证，ajax
    public function checklogin(){
        $name=$_POST['name'];
        $pwd=$_POST['pwd'];
        $capt=$_POST['capt'];
        $verify=new \Think\Verify();
        if(!$verify->check($capt)){
            echo -1;
            exit;
        } 
        
        $users=new \Model\UserModel();
        if($users->checklogin($name, $pwd)){
            //验证成功，密码正确
            echo 1;
        }else{
            echo 0;
        }

        /*
        $user= D('user');        
        if($user=$user->where($where)->find()){
            //验证成功，密码正确
            session('id',$user['u_id']);       //用户id存入session
            //是否勾选是十天免登录
            if($rem=='1'){
                $time=3600*24*10;           //设置有效时间10天
                $value=array(
                    'name'=>$user['u_name'],
                    'pwd' =>$user['u_pwd']
                );
                cookie('remember',$value,$time);
            }
            echo 1;  
        }else{
            echo 0;
        }
        */
        
    }
    /*
    public function test(){
        $user= D('user');

        $name = 'lf100212';
        $pwd = "100212lkf";
        $user=new \Model\UserModel();
        if($u=$user->checklogin($name, $pwd)){
            dump($u);
            echo 'ok';
        }else{
            echo 'no';
        }
        
        //dump($arr);
    }
    */

}