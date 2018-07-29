<?php
namespace app\admin\controller;

use controller\BaseAdmin;
use think\Db;
use think\App;

class Photo extends BaseAdmin{
    
    //相册显示
    public function index(){
        $group=Db::name('img_group')->where('is_deleted',0)->select();
        return $this->fetch('',['group'=>$group]);
    }
    
    //相册创建
    public function create(){
        $pho= new \Model\PhotoModel();

        if($photo=$pho->upload('g_cover',0)){

            $data['g_cover']=$photo['p_thumb'];
            $data['g_name']=$_POST['g_name'];
            if(D('img_group')->add($data)){
                $this->success('上传成功');
            }else{
                $this->error($pho->err);
            }
        }else{
            $this->error($pho->err);
        } 
    }
    
    //相册删除操作
    public function groupDelete(){
        checksess();
		$g_id=isset($_POST['g_id']) ? $_POST['g_id'] : '';
		
		if($g_id){
			$str=implode(',',$g_id);
			$str='g_id in('.$str.')';
			if(D('img_group')->where($str)->delete()){
				$this->success('删除成功');
			}else{
				$this->error('删除失败');
			}
		}
    }
    
    //图片显示
    public function showPhoto(){
        checksess();
        $id=isset($_GET['id']) ? $_GET['id'] : '';
        
        $photo=D('photo')->where('g_id='.$id.' and p_isdelete=0')->limit(5)->select();
        
        $p= json_encode($photo);
        if(!$photo) $p="[0]";
        
        $this->assign('ph',$p);
        
        $this->assign('g_id',$id);
        $this->display();
    }
    
    //获得指定数量的图片信息，ajax实现换一组图片的功能
    public function photolist(){
        checksess();
        $page=$_GET['page'];                                    //当前页码
        $act=$_GET['act'];                                      //换组动作，下一组还是上一组
        $group=$_GET['group'];                                  //图片所属相册id
        $count=D('photo')->where('g_id='.$group.' and p_isdelete=0')->count();      //获得数据总数量
        $pgc=ceil($count/5);                                    //获得总页数
       
        if($page=='1' && $act=='prev'){
            echo 0;
            exit;
        }
        if($page==$pgc && $act=='next'){
            echo 0;
            exit;
        }
        if($act=='prev'){
            $page--;
        }elseif ($act=='next'){
            $page++;
        }
        $firstRow=5*($page-1);                                  //limit开始的数据
        $lists=D('photo')->where('g_id='.$group.' and p_isdelete=0')->limit($firstRow,5)->select();
        $json= json_encode($lists);
        echo $json;
        
    }
    
    //图片上传操作
    public function upload(){
        checksess();
        $pho=new \Model\PhotoModel();
        if($pho->upload('p_img')){
            $this->success('上传成功');
        }else{
            $this->error($pho->err);
        }      
    }
    
    //图片删除操作
    public function delete(){
       
        $str=implode(',',$_POST['p_id']);
        $str='p_id in('.$str.')';
        if(D('photo')->where($str)->save(array('p_isdelete'=>1))){
            $this->success('删除成功');
        }else $this->error('删除失败');

    }
}