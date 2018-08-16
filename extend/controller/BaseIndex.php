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
    
    protected function login($type){
        if($type=='qq'){
            
        }elseif ($type=='wx'){
            $wechat=model('wechat');
            if($this->request->get('code','')){
                $res=$wechat->callback(0);
                if(isset($res['errcode'])) halt($res);
                $userInfo=$wechat->getUserInfo($res['openid']);
                if(isset($userInfo['errcode'])) return $userInfo;
                session('name',$userInfo['nickname']);
                session('head',$userInfo['headimgurl']);
                session('openid',$userInfo['openid']);
                $this->redirect('msg');
            }
            $wechat->oauth2(0);
        }
    }
    
    
    protected function base_logout(){
        session('name',null);
        session('head',null);
        session('openid',null);
        $this->success('已成功退出');
    }

}