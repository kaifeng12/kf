<?php
namespace controller;

use think\Controller;
use think\Db;
use think\console\command\make\Model;

class BaseIndex extends Controller {
    
    
    public function initialize()
    {   
        $request=app('request');
        $ip=$request->ip();
        $status=model('visit')->ip_filter($ip);
        if($status==404){
            echo "<b style='font-size:50px'>forbidden</b>";
            exit;
        }
        
        list($module, $controller, $action) = [$request->module(), $request->controller(), $request->action()];
        //获得页面右边的数据信息
        $log=model('log')->getNewLog(3);
        $msg=model('msg')->getMainMsg();
        $this->assign('msgs',$msg);
        $this->assign('new',$log);

    }

}