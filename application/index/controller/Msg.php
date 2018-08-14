<?php

namespace app\index\controller;

use controller\BaseIndex;
use think\Db;
use think\App;
class Msg extends BaseIndex
{
    
    public function msg(){
        $first=model('msg')->getMsg();
        $login=0;
        $is_wx=strpos($_SERVER["HTTP_USER_AGENT"],'MicroMessenger');
        $type=$is_wx?'wx':'qq';
        if(session('name') && session('head') && session('openid')){
            $this->assign('name',session('name'));
            $this->assign('head',session('head'));
            $this->assign('openid',session('openid'));
            $login=1;
        }
        return $this->fetch('',['login'=>$login,'msgss'=>$first,'type'=>$type]);
    }
    
    public function add(){
        $rid=$this->request->param('rid');
        $openid=session('openid');
		if(!$openid) $this->error('页面不存在！');
		$text=$this->request->param('text');
        if(!$text) $this->error('发表失败，请稍候再试');
        if(model('msg')->addmsg($openid,$text,$rid)){            
            $this->success('发表成功','msg');
        }else{
            $this->error('发表失败，请稍候再试');
        }        
    }
    
    public function logout(){
        session('name',null);
        session('head',null);
        session('openid',null);
        echo "1";
    }
    

    public function wx_login(){
        $wechat=model('wechat');
        if($this->request->get('code','')){
            $res=$wechat->callback(0);
            if(isset($res['errcode'])) halt($res);
            $userInfo=$wechat->getUserInfo($res['openid']);
            if(isset($userInfo['errcode'])) return $userInfo;
        }
        $wechat->oauth2(0);
    }
    
}