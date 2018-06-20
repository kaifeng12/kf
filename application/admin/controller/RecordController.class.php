<?php
namespace Admin\Controller;
use Think\Controller;
class RecordController extends Controller {
    public function right(){ 
        $visit=D('visit');
        $count=$visit->count();
        $page=new \Think\Page($count,25);
        $page->setConfig('next','下一页');
        $page->setConfig('prev','上一页');
        $show = $page->show();     
        $list = $visit->order('v_date desc')->limit($page->firstRow.','.$page->listRows)->select();
        $list = changDate($list, 'v_date',1);
        
        $this->assign('record',$list);
        $this->assign('page',$show);
        $this->display();  
    }
    

}
