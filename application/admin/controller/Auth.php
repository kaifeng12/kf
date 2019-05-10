<?php
namespace app\admin\controller;

use controller\BaseAdmin;
use think\Db;
use think\App;
use app\common\model\Node;
use think\Validate;
use think\validate\ValidateRule;

class Auth extends BaseAdmin 
{

    /**
     *角色管理
     */
    public function index()
    {
        $role = Db::name('auth')->where('status',1)->select();
        return $this->fetch('',['data'=>$role]);
    }
    
    /**
     * 角色表格数据
     */
    public function authData(){
        $limit=$this->request->param('limit',10);
        $page=$this->request->param('page',1);
        $count = Db::name('auth')->count();
        $db = Db::name('auth')->paginate($limit, $count, ['page' => $page]);
        echo json_encode([
            'code'=>0,
            'msg'=>'',
            'count'=>$count,
            'data'=>$db->all()
        ]);
    }
    
    /**
     * 修改角色相应状态
     */
    public function modify()
    {
        $id = input('id','');
        $action = input('act','');
        (empty($id) || empty($action)) && $this->error('参数错误');
        if(!Db::name('auth')->where('id',$id)->find()) $this->error('数据不存在');
        switch ($action) {
            case 'forbit':
                if(Db::name('auth')->update(['id'=>$id,'status'=>0]) === false) $this->error('禁用失败');
                $this->success('禁用成功');
            case 'enable':
                if(Db::name('auth')->update(['id'=>$id,'status'=>1]) === false) $this->error('启用失败');
                $this->success('启用成功');
            case 'delete':
                Db::name('auth')->where('id',$id)->delete();
                $this->success('删除成功');
        }
    }
    
    /**
     * 角色表单操作
     */
    public function role_form()
    {
        return $this->toForm('auth');
    }
    
    /**
     * 权限授权
     */
    public function apply()
    {
        if(!$this->request->isPost()){
            $id = input('id','');
            empty($id) && $this->error('参数错误');
            //已存在的角色授权
            $auth_node = Db::name('auth_node')->where(['auth'=>$id,'is_deleted'=>0])->column('*','node');
            //加入权限控制的节点
            $nodeInfo = Db::name('node')->where('is_auth',1)->column('node,title,is_menu,is_auth,is_login');
            $node = new Node();
            $nodeData = $node->nodeToTree(array_keys($nodeInfo));
            return $this->fetch('',['nodeData'=>$nodeData,'authNode'=>$auth_node,'auth_id'=>$id,'nodeInfo'=>$nodeInfo]);
        }else {
            $validate = Validate::make([
                'nodes'=>'require',
                'auth_id'=>'require'
            ]);
            $param = input();
            !$validate->check($param) && $this->error($validate->getError(),'',['checked'=>isset($param['checked']) && $param['checked']=='true'? true : false]);
            $auth_node = Db::name('auth_node')->where('auth',$param['auth_id'])->column('node');
            
            $checked = $param['checked']=='true'? true : false;
//             $this->error('失败','',['checked'=>$checked]);
            
            try {
                $checked = $param['checked']=='true'? true : false;
                $is_deleted = $checked ? 0 : 1;
                Db::transaction(function () use($param,$auth_node,$is_deleted){
                    $addNodes = [];
                    foreach ($param['nodes'] as $node){
                        if(!in_array($node, $auth_node)){
                            $addNodes[] = ['node'=>$node,'auth'=>$param['auth_id'],"is_deleted"=>$is_deleted];
                        }else {
                            Db::name('AuthNode')->where(['node'=>$node,'auth'=>$param['auth_id']])->update(["is_deleted"=>$is_deleted]);
                        }
                    }
                    if(!empty($addNodes)) Db::name('AuthNode')->insertAll($addNodes);
                });
            } catch (\Exception $e) {
                $this->error('编辑失败，'.$e->getMessage().$e->getline().$e->getFile(),'',['checked'=>$checked]);
            }
            
            $this->success('');
        }

    }
    
    
    /**
     * 节点管理
     */
    public function node()
    {
        $nodeData = $this->getnode();
        $nodeInfo = Db::name('node')->column('node,title,is_menu,is_auth,is_login');
        return $this->fetch('',['nodeData'=>$nodeData,'nodeInfo'=>$nodeInfo]);
    }
    
    /**
     * 改变节点状态
     */
    public function change()
    {
        $validate = Validate::make([
            'type'=>'require',
            'nodes'=>'require'
        ]);
        $param = input();
        !$validate->check($param) && $this->error($validate->getError(),'',['checked'=>isset($param['checked']) && $param['checked']=='true'? true : false]);
        if($param['type'] == 'title'){
            if(!empty($param['title'])){
                try {
                    if(!$res = Db::name('node')->where('node',$param['nodes'])->update(['title'=>$param['title']])) $this->error('编辑失败'.$param['title']);
                } catch (\Exception $e) {
                    $this->error('编辑失败'.$e->getMessage());
                }
            }
            $this->success('');
        }
        $nodes = Db::name('node')->whereIn('node', $param['nodes'])->column('node');
        try {
            $checked = $param['checked']=='true'? true : false;
            $is_checked = $checked ? 1 : 0;
            Db::transaction(function () use($param,$nodes,$is_checked){
                $addNodes = [];
                foreach ($param['nodes'] as $node){
                    if(!in_array($node, $nodes)){
                        $addNodes[] = ['node'=>$node,"is_{$param['type']}"=>$is_checked];
                    }else {
                        Db::name('node')->where('node',$node)->update(["is_{$param['type']}"=>$is_checked]);
                    }
                }
                if(!empty($addNodes)) Db::name('node')->insertAll($addNodes);
            });
        } catch (\Exception $e) {
            $this->error('编辑失败，'.$e->getMessage().$e->getline().$e->getFile(),'',['checked'=>$checked]);
        }

        $this->success('');
    }
    

    
    /**
     * 获取节点信息
     */
    private function getnode($filter=[]){
        $node = new Node();
        $nodes = $node->getNodeTree();
        if(!empty($filter)){
            foreach ($nodes as $k=>$v){
                if(!isset($filter[$v])) unset($nodes[$k]);
            }
        }
        
        return $node->nodeToTree($nodes);
    }
    
}
