<?php
namespace app\admin\controller;

use think\Controller;
use think\Db;
use think\App;
use think\captcha\Captcha;

class Log extends Controller {
    
    
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
        $log= D('log');
        $logs=$log->where('l_isdelete=0 and l_type=0')->select();
        $this->assign('logs',$logs);
        $this->display('right');
    }
        
    //生成技术杂谈页面
    public function right1(){
        checksess();     //验证session
        $log= D('log');
        $logs=$log->where('l_isdelete=0 and l_type=1')->select();
        $this->assign('logs',$logs);
        $this->display('right1');
    }
    
    
    //生成编辑文章的页面
    public function logedit(){
        checksess();     //验证session        
        if($id=$_GET['id']){
            $log=D('log');
            $logs=$log->where('l_id='.$id)->find();
            $this->assign('logs',$logs);
            $this->display('logedit');
        }
    }
    
    //执行文章编辑
    public function logediting(){
        checksess();     //验证session
        $id=$_GET['id'];
        //判断有无修改封面
        $cover=isset($_POST['cover'])?$_POST['cover']:'';
        if($cover){
            $_POST['l_cover']=$cover;
        }
        
        //判断是否修改发布时间
        $tmp=isset($_POST['istime'])?$_POST['istime']:'';
        if($tmp=='1'){
            unset($_POST['istime']);
            $_POST['l_date']= "".time();
        }
        
        $_POST['l_content'].='<div class="clear"></div>';       //设置clear
        
        $log= D('log');
        $log->create();
        if($log->where('l_id='.$id)->save()){
            $this->right();
        }else echo '编辑失败';
    }
    
    //执行文章发布
    public function add(){
        checksess();
        $_POST['l_date']=time();
        $_POST['l_author']='峰';
        $_POST['l_content'].='<div class="clear"></div>';       //设置clear        
        $log=D('log');
        $log->create();
        if($log->add()){
            $this->right();
        }else echo '发布失败';
    }
    
    //执行删除文章
    public function deletelogs(){
        checksess();     //验证session
        $del=$_POST['isdelete'];
        $data['l_isdelete']='1';
        $str= implode($del,',');
        $log= D('log');
        if($log->where('l_id in ('.$str.')')->save($data)){
            $this->right();
            exit;
        }else echo '删除失败';
    }
}
    