<?php
namespace app\common\model;

use think\Model;
use think\Db;

class Auth extends Model
{
    
    public function checkAuth($node)
    {
        $user = session('user');
        $auths = Db::name('user')->where('id',$user['id'])->value('auth');
        $auths = $auths ? explode(',', Db::name('user')->where('id',$user['id'])->value('auth')) : [];
        $auth_node = Db::name('AuthNode')->whereIn('auth', $auths)->where('is_deleted',0)->select();
        
        halt($auth_node);
    }
}