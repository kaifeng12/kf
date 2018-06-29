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
        $data['uid']= session('id');halt($data);
        $msg= D('msg');
        $msg->create();
        if($msg->add()){
            $this->right();
        }else{
            echo '回复失败';
            sleep(2);
            $this->right();
        }

    }
    
    
    public function delete(){
        checksess();     //验证session    
        if(session('id')!=='1') exit;
        $id=$_POST['m_id'];
        if(D('msg')->where('m_id='.$id)->save(array('m_isdelete'=>'1'))){
            echo 1;
        }else{
            echo 0;
        }
    }
    

}
    