<?php
namespace app\admin\controller;

use controller\BaseAdmin;
use think\Db;
use think\App;
use app\common\model\Node;
use think\Validate;
use think\validate\ValidateRule;
use think\Image;

class User extends BaseAdmin 
{
    

    /**
     *用户管理
     */
    public function index()
    {
        if($this->request->isPost()){
            $limit=$this->request->param('limit',10);
            $page=$this->request->param('page',1);
            $count = Db::name('user')->count();
            $db = Db::name('user')->paginate($limit, $count, ['page' => $page]);
            echo json_encode([
                'code'=>0,
                'msg'=>'',
                'count'=>$count,
                'data'=>$db->all()
            ]);
        }else {
            return $this->fetch('');
        }
    }
    
    
    
    /**
     * 权限授权
     */
    public function apply()
    {
        $id = input('id','');
        empty($id) && $this->error('参数错误');
        if(!$this->request->isPost()){
            if(!$user = Db::name('user')->where('id',$id)->find()) $this->error('用户不存在');
            $user_auths = explode(',', $user['auth']);
            $auths = Db::name('auth')->select();
            return $this->fetch('',['auths'=>$auths,'id'=>$id,'user_auths'=>$user_auths]);
        }
        if(empty(input('aid'))){
            $auth='';
        }else {
            $auth = join(',', input('aid'));
        }
        if(!Db::name('user')->where('id',$id)->update(['auth'=>$auth])) $this->error('授权失败');
        $this->success('授权成功');
    }
    
    
    /**
     * 修改用户账号密码
     */
    public function change_pwd()
    {
        $id = input('id','');
        empty($id) && $this->error('参数错误');
        if($this->request->isPost()){
            
            $validate = Validate::make([
                'pwd'=>'require|confirm:pwd1',
                'pwd1'=>'require'
            ])->message('pwd.confirm','两次输入密码不一致！');
            !$validate->check(input()) && $this->error($validate->getError());
            
            if(Db::name('user')->where('id',$id)->update(['pwd'=>md5(input('pwd'))])){
                if($id == $this->user['id']){
                    session('user',null);
                    cookie('username',null);
                    $this->success('修改密码成功,请重新登录');
                }
                $this->success('修改密码成功');
            }
            $this->error('修改失败');
        }
        return $this->fetch('',['id'=>$id]);
    }
    
    /**
     * 修改账号信息
     */
    public function edit()
    {
        $id = input('id','');
        empty($id) && $this->error('参数错误');
        if(!$this->request->isPost()){
            if(!$user = Db::name('user')->where('id',$id)->find()) $this->error('用户不存在');
            return $this->fetch('form',['user'=>$user]);
        }
        $validate = Validate::make([
            'name'=>'require',
            'net_name'=>'require',
            'head'=>'require'
        ]);
        !$validate->check(input()) && $this->error($validate->getError());
        if(!Db::name('user')->where('id',$id)->update(['name'=>input('name'),'net_name'=>input('net_name'),'head'=>input('head')])) $this->error('修改失败');
        $user = [
            'id'=>$id,
            'username'=>input('name'),
            'net_name'=>input('net_name'),
            'head'=>input('head')
        ];
        if(session('user')['id']==$id){
            //修改正在登录的账号
            session('user',$user);
            if(!empty(cookie('username'))) cookie('username' ,$user['username'] ,3600*24*7);
        }
        $this->success('修改成功');
    }
    
    /**
     * 上传头像
     */
    public function upload_face()
    {
        $photo=$this->request->file('head');
        if(!$photo) $this->error('参数错误');
        
        $info=$photo->getInfo();
        $extension=strrchr($info['name'], '.');//图片后缀
        $image=Image::open($photo);
        $savePath='static/uploads/photo/';
        $thumbSaveName=$savePath.'face_'.md5(time()).$extension;
        $image->thumb(100, 100)->save($thumbSaveName);
        $this->success('','',['head'=>'/'.$thumbSaveName]);
    }
    
    /**
     * 添加用户
     */
    public function add()
    {
        return $this->toForm('user','form');
    }

    /**
     * 删除用户
     */
    public function delete()
    {
        $id = input('id','');
        empty($id) && $this->error('参数错误');
        if(!Db::name('user')->where(['id'=>$id,'power'=>1])->delete()) $this->error('删除失败');
        if($id = session('user')['id']) session('user',null);
        $this->success('删除成功');
    }
    
}
