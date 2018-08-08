<?php
namespace app\admin\controller;

use controller\BaseAdmin;
use think\Db;
use think\App;

class Visit extends BaseAdmin {
    /**
     *访问统计 
     */
    public function index(){
        return $this->fetch('');
    }
    
    /**
     * ajax获取访问统计数据
     */
    public function table(){
        $limit=$this->request->param('limit',90);
        $page=$this->request->param('page',1);
        $filter=$this->request->param('filter',[]);
        $visit=model('visit')->visitList($limit,$page,$filter);
        echo json_encode($visit);
    }
    
    public function forbidden(){
        $id=$this->request->param('id');
        $forbidden=$this->request->param('forbidden')=='true'?1:0;
        $ip=Db::name('visit')->where('id',$id)->value('ip');
        return Db::name('ip_info')->where('ip',$ip)->update(['forbidden'=>$forbidden]);
    }

}
