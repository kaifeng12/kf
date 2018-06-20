<?php
namespace Admin\Controller;
use Think\Controller;

class AdminController extends Controller {
    
    //定义空操作
    public function _empty(){
        $action=array('left','top','right','right2','right3','right4','right5');
        if(in_array(ACTION_NAME, $action)){
            checksess();
            $this->display(ACTION_NAME);
            exit;
        }
        echo '您访问的地址不存在';
    }

    
    public function home(){       
        checksess();     //验证session        
        $this->display();
    }
    
    public function top(){
        checksess();     //验证session
        $this->display();
    }
    
    public function left(){
        checksess();     //验证session
        $this->display();
    }
    

    
    public function logout(){
        session('id',null);
        exit("<script>top.location.href='".__MODULE__."'</script>");
    }
    

    

}