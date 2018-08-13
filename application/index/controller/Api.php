<?php

namespace app\index\controller;


use think\Db;
use think\App;
use think\Controller;
class Api extends Controller
{
    
    public function oauth2(){
        $action=$this->request->param('action','');
        $type=$this->request->param('type','');
        $state=$this->request->param('state','');
        $param=$this->request->param('param','');
        if(empty($action) || empty($type)) return '';
        $redirect_uri=url('index/api/callback',['action'=>$action,'type'=>$type,'param'=>$param],'html',true);
        if($type=='qq'){
              $param=json_encode(['action'=>$action,'type'=>$type,'param'=>$param]);
              $redirect_uri=url('index/api/callback','','html',true)."?param={$param}";

            $url="https://graph.qq.com/oauth2.0/authorize?response_type=code&client_id=101450092&redirect_uri={$redirect_uri}";
        }elseif ($type=='wx'){
            $appid=sys_config('appid', 'wxtest');
            $url="https://open.weixin.qq.com/connect/oauth2/authorize?appid={$appid}&redirect_uri={$redirect_uri}&response_type=code&scope=snsapi_userinfo&state=1012#wechat_redirect";
        }
        if(isset($url)) $this->redirect($url);
    }
    
    public function callback(){
        $action=$this->request->param('action','');
        $type=$this->request->param('type','');
        $param=$this->request->param('param','');
        $state=$this->request->get('state','');
        $code=$this->request->get('code','');
        if(empty($action) || empty($type) || empty($code)){
            if(empty($param)){
                return '';
            }
            $param=json_decode($param,true);
            list($action,$type,$param)=[$param['action'],$param['type'],$param['param']];
        }
   
        if($type=='qq'){
            if($user=model('qq')->callback($code)){
                
                session('name',$user['name']);
                session('head',$user['head']);
                session('openid',$user['openid']);
            }
        }elseif ($type=='wx'){
            if(empty($code) || empty($state)) return false;
            $appid=sys_config('appid', 'wxtest');
            $appsecret=sys_config('appsecret', 'wxtest');
            $url="https://api.weixin.qq.com/sns/oauth2/access_token?appid={$appid}&secret={$appsecret}&code={$code}&grant_type=authorization_code";
            $res=curl($url);
            if(isset($res['errcode'])) return 'error';
            
            $url="https://api.weixin.qq.com/sns/userinfo?access_token={$res['access_token']}&openid={$res['openid']}&lang=zh_CN";
            $userInfo=curl($url);
            if(isset($userInfo['errcode'])) return 'error';
            $userInfo['nickname']=emojiEncode($userInfo['nickname']);
            Db::name('WechatFans')->strict(false)->insert($userInfo);
            session('name',$userInfo['nickname']);
            session('head',$userInfo['headimgurl']);
            session('openid',$userInfo['openid']);
            
        }
        if($action=='read' && !empty($param)){
            $this->redirect("http://www.likaifeng.xyz/index/Log/read/id/{$param}.html#cmt");
        }else $this->redirect('msg/msg');
          
    }


}