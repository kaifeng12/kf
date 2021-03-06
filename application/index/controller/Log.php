<?php
namespace app\index\controller;

use controller\BaseIndex;
use think\Db;
use think\App;

class Log extends BaseIndex
{
    
    //生活日志页面
    public function log(){
        $logs=model('log')->getNewLog(999,1,1);        //获得按时间排序的所有生活日志文章
		$logs=[];//暂时不写
        return $this->fetch('',['logs'=>$logs]);
    }
        
    
    //技术杂谈页面
    public function talk(){
        $logs=model('log')->getNewLog(999,1,2);      //获得按时间排序的所有技术杂谈文章
        $is_wx=strpos($_SERVER["HTTP_USER_AGENT"],'MicroMessenger');
        $type=$is_wx?'wx':'qq';
        return $this->fetch('',['logs'=>$logs,'type'=>$type]);
    }
    
    
    //阅读文章显示
    public function read(){
        $id=$this->request->param('id');
        $is_wx=strpos($_SERVER["HTTP_USER_AGENT"],'MicroMessenger');
        $type=$is_wx?'wx':'qq';
        if($id){
            //判断是否登录
            $login=0;
            
            if(session('name') && session('head') && session('openid')){
                $this->assign('name',session('name'));
                $this->assign('head',session('head'));
                $this->assign('openid',session('openid'));
                $login=1;
            }          
            
            if(!$log=Db::name('log')->where(['is_deleted'=>0,'id'=>$id])->find()){
                echo '你访问的地址不存在';
                exit;
            }
            Db::name('log')->where('id',$id)->setInc('click');
            
            changDate($log,'date',1);
            $comment=model('comment')->getCom($id);
            return $this->fetch('',['login'=>$login,'comment'=>$comment,'log'=>$log,'type'=>$type]);
        }else{
            echo '你访问的地址不存在';
        }
    }
    
    //文章搜索
    public function find(){
        $key=$this->request->param('title');
        $this->assign('num',0);
        if(!$key){
            return $this->fetch('');
        }
        
        if($logs=Db::name('log')->where('is_deleted',0)->whereLike('title', "%{$key}%")->select()){
            return $this->fetch('',['num'=>1,'logs'=>$logs]);
        }else{
            return $this->fetch();
        }       
    }
    
    
    public function addcmt(){
        if(!$this->request->isPost()) die('页面不存在');
        list($text,$rid,$id)=array_combine([0,1,2], $this->request->param());
        if((!$rid && $rid!=='0') || !$id || !$text){
            $this->error('发表失败，请稍候再试');
        }
        $openid=session('openid');
        $is_wx=strpos($_SERVER["HTTP_USER_AGENT"],'MicroMessenger');
        $type=$is_wx?'wx':'qq';
        $comment=model('comment');
        if($comment->addComment($openid,$text,$rid,$id,$type)){            
            $this->success('发表成功');
        }else{
            $this->error('发表失败');
        }
        
    }
    
    public function logout(){
        $this->base_logout();
    }
    
    /**
     * 微信登录
     */
    public function wx_login(){
        $this->login('wx');
    }
    

}