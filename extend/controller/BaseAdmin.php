<?php
namespace controller;

use think\Controller;
use think\Db;
use think\console\command\make\Model;

class BaseAdmin extends Controller {
    
    
    public function initialize()
    {   
        $this->checksess();
    }

    /**
     * 验证是否存在session，以验证是否登录
     */
    protected function checksess(){
        if(!session('user')){
            echo "<b style='font-size:50px'>404</b>";
            exit;
        }
    }

}