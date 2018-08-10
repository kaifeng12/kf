<?php
namespace app\admin\controller;

use controller\BaseAdmin;
use think\Db;
use think\App;

class Log extends BaseAdmin {
    
    //生成初始展示页面
    public function logs(){
        checksess();     //验证session
        $log= D('log');
        $logs=$log->where('l_isdelete=0 and l_type=0')->select();
        $this->assign('logs',$logs);
        $this->display('right');
    }
        
    //生成技术杂谈页面
    public function talk(){
        $logs=Db::name('log')->where(['is_deleted'=>0,'type'=>1])->field('content',true)->select();
        return $this->fetch('',['logs'=>$logs]);
    }
    
    
    //生成编辑文章的页面
    public function logedit(){
        $id=$this->request->param('id','');
        $logs=Db::name('log')->where('id',$id)->find();
        return $this->fetch('',['logs'=>$logs]);
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
        $ids=$this->request->param('isdelete',[]);
        $type=$this->request->param('type','1');
        if($ids){
            foreach ($ids as $id){
                Db::name('log')->where('id',$id)->update(['is_deleted'=>1]);
            }
        }
        $url=$type=='1'?'talk':'logs';
        $this->redirect($url);
    }
}
    