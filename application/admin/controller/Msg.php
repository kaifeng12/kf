<?php
namespace app\admin\controller;

use controller\BaseAdmin;
use think\Db;
use think\App;

class Msg extends BaseAdmin {
    
    //生成初始展示页面
    public function index(){
        $first=model('index/msg')->getMsg();
        return $this->fetch('',['msgs'=>$first]);
    }
        

    public function reply(){
        $data=$this->request->param();
        $data['date']= time();
        $data['uid']= session('id');
        $data['qid']=10;
        if(Db::name('msg')->insert($data)){
            $this->success('回复成功','index','',1);
        }else{
            $this->error('回复失败','index','',2);
        }
    }
    
    
    public function delete(){
        $id=$this->request->param('id');
        if(Db::name('msg')->where('id',$id)->update(['is_deleted'=>1])){
            echo 1;
        }else{
            echo 0;
        }
    }
    

}
    