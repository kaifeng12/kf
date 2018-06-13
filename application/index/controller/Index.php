<?php
namespace app\index\controller;

use controller\BaseIndex;
use think\Db;
use think\App;


class Index extends BaseIndex
{
    public function index()
    {   

        $log=model('log');
        $logs=$log->getNewLog(5,0);
        return $this->fetch('',['logs'=>$logs]);  
    }

    public function hello($name = 'ThinkPHP5')
    {
        return 'hello,' . $name;
    }
}
