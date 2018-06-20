<?php
namespace app\index\controller;

use controller\BaseIndex;
use think\Db;
use think\App;
class Photo extends BaseIndex {
    
    public function index(){        
        $group=Db::name('img_group')->where('is_deleted',0)->select();
        return $this->fetch('',['group'=>$group]);
    }
    
    //图片显示
    public function showphoto(){
        $id=$this->request->param('id');
        $photo=Db::name('photo')->where(['gid'=>$id,'is_deleted'=>0])->limit(5)->select();
        
        $p= json_encode($photo);
        $photo || $p='[0]';
        return $this->fetch('',['ph'=>$p,'gid'=>$id]);
    }
    
    
    //获得指定数量的图片信息，ajax实现换一组图片的功能
    public function photolist(){
        if(!$this->request->isAjax()) return '页面不存在';
        $param=$this->request->param();
        $page=$param['page'];                                    //当前页码
        $act=$param['act'];                                      //换组动作，下一组还是上一组
        $group=$param['group'];                                  //图片所属相册id
        $count=Db::name('photo')->where(['gid'=>$group,'is_deleted'=>0])->count();
        if($page=='1' && $act=='prev'){
            echo 0;
            exit;
        }
        $act=='prev' && $page--;
        $act=='next' && $page++;
        $db=Db::name('photo')->where(['gid'=>$group,'is_deleted'=>0])->paginate(5,(int)$count,['page'=>$page]);
        if(!$lists=$db->all()){
            echo 0;
            exit;
        }
        $json= json_encode($lists);
        echo $json;
        
    }

}