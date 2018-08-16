<?php

namespace app\index\controller;


use think\Db;
use think\App;
use think\Controller;
class Api extends Controller
{
    /**
     * 判断发起授权
     */
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
        }
        if(isset($url)) $this->redirect($url);
    }
    
    /**
     * 授权回调
     */
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
        }
        if($action=='read' && !empty($param)){
            $this->redirect("http://www.likaifeng.xyz/index/Log/read/id/{$param}.html#cmt");
        }else $this->redirect('msg/msg');
          
    }


}