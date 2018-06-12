<?php
namespace controller;

use think\Controller;
use think\Db;

class BaseIndex extends Controller {
    
    /**
     * 获得页面右边的数据信息
     * @param string $type
     * @return \Model\LogModel|\Model\MsgModel|\Model\PhotoModel
     */
    protected function listRight($type='log'){
        $log=new \Model\LogModel();
        $new=$log->getNewLog(3);
        
        $msg=new \Model\MsgModel();
        $msgs=$msg->getMainMsg();
        
        $this->assign('msgs',$msgs);
        $this->assign('new',$new);
        if($type=='log') return $log;
        elseif($type=='msg') return $msg;
        elseif ($type=='photo') return new \Model\PhotoModel();
        


    }
    
    protected function test()
    {
        return '123';
    }
    

}