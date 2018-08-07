<?php
namespace app\common\model;
use think\Model;
use think\Request;
use think\Db;

class Photo extends Model
{

    public $err;

    /**
     * 图片上传操作
     * @param string $key   图片资源$_FILES里的键值
     * @param int $issave   图片上传是否已选定所属相册
     * @return mixed        成功返回图片信息array，失败返回false，属性err保存错误信息
     */
    public function upload($key,$issave=1)
    {
        $config = array(
            'rootPath' => './Public/',
            'savePath' => 'uploads/photo/',
            'autoSub' => false
        );
        $upload = new \Think\Upload($config); // 实例化文件上传类
        if ($z = $upload->uploadOne($_FILES[$key])) {
            $imgpath = $upload->rootPath . $z['savepath'] . $z['savename'];
            $_POST['p_path'] = '/Public/' . $z['savepath'] . $z['savename']; // 把图片路径保存进post数组，方便使用create()
        } else {
            $this->err = $upload->getError();
            return false;
        }
        
        //修改上传图片的尺寸，限制宽高600*300
        $img = new \Think\Image();
        $img->open($imgpath); // 打开图片资源
        
        if ($img->width() > 600 || $img->height() > 300) {
            if (! unlink($imgpath)){
                $this->err='失败';
                return false;
            }
            $img->thumb(600, 300);
            $img->save($imgpath);
        }
        
        // 生成缩略图
        $thm = new \Think\Image();
        $thm->open($imgpath);
        $img->thumb(100, 100); // 生成100*100缩略图，默认等比例缩放
        $thumb = $z['savepath'] . 'thumb_' . $z['savename']; // 此信息用来保存到数据库
        $img->save($upload->rootPath . $thumb); // 保存缩略图，与源文件同目录
        $_POST['p_thumb'] = '/Public/' . $thumb;
        
        if(!$issave){
            $_POST['g_id']=0;
        }
        $this->create();
        if($p_id=$this->add()){
            return $this->where('p_id='.$p_id)->find();
        }else{
            $this->err='添加失败';
            return false;
        }  
    }
}