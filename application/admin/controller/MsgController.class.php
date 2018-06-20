<?php
namespace Admin\Controller;
use Think\Controller;

class MsgController extends Controller {
    
    
    //定义空操作
    public function _empty(){
        $action=array('logadd');
        if(in_array(ACTION_NAME, $action)){
            checksess();
            $this->display(ACTION_NAME);
            exit;
        }else {
        echo '您访问的地址不存在';
        }
    }
            
    //生成初始展示页面
    public function right(){
        checksess();     //验证session
        $msg=new \Model\MsgModel();
        $first=$msg->getMsg();

        $this->assign('msgs',$first);
        $this->display('right');
    }
        

    public function reply(){
        checksess();     //验证session
        $_POST['c_date']= time();
        $_POST['u_id']= session('id');
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
    