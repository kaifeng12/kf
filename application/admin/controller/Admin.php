<?php
namespace app\admin\controller;

use controller\BaseAdmin;
use think\Db;
use think\App;

class Admin extends BaseAdmin
{
    
    public function home(){             
        return $this->fetch('');
    }
    
    public function top(){
        checksess();     //验证session
        $this->display();
    }
    
    public function left(){
        checksess();     //验证session
        $this->display();
    }
    

    
    public function logout(){
        session('id',null);
        $this->success('退出成功','login/index');
    }
    

    

}