<?php
namespace app\admin\controller;

use controller\BaseAdmin;
use think\Db;
use think\App;

class Admin extends BaseAdmin
{
    
    /**
     * 首页
     * @return mixed|string
     */
    public function home(){    
        return $this->redirect('visit/index');
    }
    
    
    /**
     * 退出系统
     */
    public function logout(){
        session('user',null);
        cookie('username',null);
        $this->success('退出成功','login/index','',1);
    }
    

    

}