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
     * 修改用户信息
     * @return mixed|string|void
     */
    public function change_pwd(){
        if(!$this->request->isPost()){
            $user=Db::name('user')->where('id',$this->request->param('id'))->find();
            return $this->fetch('',['user'=>$user]); 
        }
        $post=$this->request->post();
        if(Db::name('user')->update(['id'=>$post['uid'],'net_name'=>$post['name'],'pwd'=>md5($post['pwd'])])){
            session('user',null);
            return $this->success('修改成功','login/index','',1);
        }
        
    }
    
    /**
     * 退出系统
     */
    public function logout(){
        session('user',null);
        $this->success('退出成功','login/index','',1);
    }
    

    

}