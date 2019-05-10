<?php
namespace app\common\model;

use think\Model;

class User extends Model
{
    /**
     * 检查登录
     * @param $name
     * @param $pwd
     */
    public function checklogin($name,$pwd,$type = ''){
        empty($type) && $pwd=md5($pwd);
        $user=$this->where(['name'=>$name,'pwd'=>$pwd])->find();
        if($user && $user['pwd']==$pwd){
            session('user',['id'=>$user['id'],'username'=>$user['name'],'head'=>$user['head'],'name'=>$user['net_name']]);       //用户id存入session
            $l_time=$user['last_login'];
            $this->save(['last_login'=>time()],['id'=>$user['id']]);
            if(empty($type)){
                //输入密码登录
                cookie('username' ,$name ,3600*24*7);
            }
            return $user;
        }else{
            return false;
        }
    }
}