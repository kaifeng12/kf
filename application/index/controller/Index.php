<?php
namespace app\index\controller;

use controller\BaseIndex;
use think\App;


class Index extends BaseIndex
{
    public function index()
    {   
        $log=model('log');
        $logs=$log->getNewLog(5,0);
        return $this->fetch('',['logs'=>$logs]);  
    }

}
