<?php
namespace controller;

use think\Controller;
use think\Db;
use think\console\command\make\Model;

class BaseAdmin extends Controller {
    
    
    public function initialize()
    {   
        $ip=$this->request->ip();
        $status=model('visit')->ip_filter($ip);
        if($status==404){
            throw new \think\exception\HttpException(404, '');
        }
        $this->checksess();
    }

    /**
     * 验证是否存在session，以验证是否登录
     */
    protected function checksess()
    {
        if(!session('user')){
            if(!empty(cookie('username'))){
                $username = cookie('username');
                if(!$user = Db::name('user')->where('name',$username)->find()){
                    session('user',$user);
                    cookie('username',cookie('username'),3600*24*7);
                }
            }else {
                $this->redirect('admin/login/index');
            }
        }
        $this->user = session('user');
    }
    
    /**
     * 
     * @param string $table 数据表
     * @param string $template 模板
     * @param string $key  
     * @return mixed|string
     */
    protected function toForm($table='',$template='',$key='')
    {
        $param = input();
        if(!empty($table)){
            $db = Db::name($table);
        }else {
            empty($this->table) && $this->error('表不存在');
            $db = Db::name($this->table);
        }
        $key = empty($key) ? 'id' : $key;
        if($this->request->isPost()){
            //提交表单
            if (false !== $this->_callback('_form_filter', $param, [])) {
                if(empty($param[$key])){
                    if($db->strict(false)->insert($param) === false) $this->error('增加数据失败');
                    $this->success('增加数据成功');
                }
                if($db->where($key,$param[$key])->strict(false)->update($param) === false) $this->error('修改失败');
                $this->success('修改成功');
            }
        }else {
            $data = [];
            if(!empty($param[$key])){
                $data = $db->where($key,$param[$key])->find();
                empty($data) && $this->error('数据不存在');
            }
            if (false !== $this->_callback('_form_filter', $data, [])) return $this->fetch($template,['data'=>$data]);
            return $data;
            
        }
    }
    
    /**
     * 当前对象回调成员方法
     * @param string $method
     * @param array|bool $data1
     * @param array|bool $data2
     * @return bool
     */
    protected function _callback($method, &$data1, $data2)
    {
        foreach ([$method, "_" . $this->request->action() . "{$method}"] as $_method) {
            if (method_exists($this, $_method) && false === $this->$_method($data1, $data2)) {
                return false;
            }
        }
        return true;
    }

}