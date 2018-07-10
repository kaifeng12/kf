<?php
namespace app\common\model;

use think\Model;

class User extends Model
{
    public function checklogin($name,$pwd){

        $pwd=md5($pwd);
        $user=$this->where(['name'=>$name,'pwd'=>$pwd])->find();
        if($user && $user['pwd']==$pwd){
            session('user',['id'=>$user['id'],'head'=>$user['head'],'name'=>$user['net_name']]);       //用户id存入session
            $l_time=$user['last_login'];
            $this->save(['last_login'=>time()],['id'=>$user['id']]);
            return $user;
        }else{
            return false;
        }
    }
}