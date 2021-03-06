<?php
namespace app\admin\controller;

use controller\BaseAdmin;
use think\Db;
use think\App;
use think\Image;

class Photo extends BaseAdmin{
    
    //相册显示
    public function index(){
        $group=Db::name('img_group')->where('is_deleted',0)->select();
        return $this->fetch('',['group'=>$group]);
    }
    
    //相册创建
    public function create(){
        $file=$this->request->file('cover');
        $title=$this->request->post('name','');
        if(!$file || !$title) $this->error('参数错误');
        $thumbSaveName=$this->saveCover('cover','cover', 100, 100);
        Db::name('img_group')->insert(['name'=>$title,'cover'=>'/'.$thumbSaveName]);
        $this->redirect('index');
    }
    
    //相册删除操作
    public function groupDelete(){
        $ids=$this->request->post('id');
        if($ids){
            foreach ($ids as $id){
                Db::name('img_group')->where('id',$id)->update(['is_deleted'=>1]);
            }
        }
        $this->redirect('index');
    }
    
    //图片显示
    public function showphoto(){
        $gid=$this->request->param('gid','');
        $page=$this->request->param('page',1,'trim,intval');
        $map=['is_deleted'=>0,'gid'=>$gid];
        $total=Db::name('photo')->where($map)->count();
        $db=Db::name('photo')->where($map)->paginate(5,(integer)$total,['page'=>$page]);
        $photo=$db->all();
        $ph=json_encode($photo);
        if(!$photo) $ph='[0]';
        $data=['gid'=>$gid,'ph'=>$ph,'lastPage'=>$db->lastPage(),'currentPage'=>$db->currentPage()];
        return $this->fetch('',$data);        
    }
    
    //获得指定数量的图片信息，ajax实现换一组图片的功能
    public function photolist(){
        if(!$this->request->isAjax()) return '页面不存在';
        $param=$this->request->param();
        $page=$param['page'];                                    //当前页码
        $act=$param['act'];                                      //换组动作，下一组还是上一组
        $group=$param['gid'];                                  //图片所属相册id
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
    
    //图片上传操作
    public function addphoto(){
        $gid=$this->request->post('gid','');
        $photo=$this->request->file('photo');
        if(empty($gid) || !$photo) $this->error('参数错误');
        $imageInfo=$this->saveCover('photo','photo', 100, 100);
        if(!$imageInfo['image'] || !$imageInfo['thumb']) $this->error('上传失败');
        Db::name('photo')->insert(['gid'=>$gid,'path'=>'/'.$imageInfo['image'],'thumb'=>'/'.$imageInfo['thumb']]);
        $this->success('');
    }
    
    //图片删除操作
    public function delete(){
        $ids=$this->request->param('id','');
        $gid=$this->request->param('gid','');
        if($ids && $gid){
            foreach ($ids as $id){
                Db::name('photo')->where('id',$id)->update(['is_deleted'=>1]);
            }
        }
        $this->redirect('showphoto',['gid'=>$gid]);
    }
    
    /**
     * 编辑封面
     */
    public function edit(){
        $type=$this->request->post('type','');
        $gid=$this->request->post('gid','');
        if(empty($type) || empty($gid)) $this->error('参数错误');
        if($type=='cover'){
            $thumbSaveName=$this->saveCover('cover','cover', 100, 100);
            Db::name('img_group')->where('id',$gid)->update(['cover'=>'/'.$thumbSaveName]);
            $this->success('编辑成功','',['gid'=>$gid,'path'=>'/'.$thumbSaveName]);
        }elseif ($type=='name'){
            $title=$this->request->post('title');
            empty($title) && $this->error('不能为空');
            if(Db::name('img_group')->where('id',$gid)->update(['name'=>$title])){
                $this->success('');
            }
            $this->error('编辑失败');
        }

    }
    
    /**
     * 保存上传的图片缩略图
     * @param string $type 
     * @param string $key
     * @param float $width  缩略图宽
     * @param float $height
     * @return string|array
     */
    private function saveCover($type,$key,$width,$height){
        $file=$this->request->file($key);
        $info=$file->getInfo();
        $extension=strrchr($info['name'], '.');//图片后缀
        $image=Image::open($file);
        $savePath='static/uploads/photo/';
        if($type=='cover'){
            $thumbSaveName=$savePath.'cover_'.md5(time()).$extension;
            $image->thumb($width, $height)->save($thumbSaveName);
            return $thumbSaveName;
        }elseif ($type=='photo'){
            $imageSaveName=$savePath.md5(microtime()).$extension;//多图上传可能再一秒内上传多张，应该用微妙时间戳
            if ($image->width() > 600 || $image->height() > 300){
                $image->thumb(600, 300)->save($imageSaveName);
            }else {
                $image->save($imageSaveName);
            }
            $thumbSaveName=$savePath.'thumb_'.md5(microtime()).$extension;
            $image->thumb($width, $height)->save($thumbSaveName);
            return ['image'=>$imageSaveName,'thumb'=>$thumbSaveName];
        }
    }
}