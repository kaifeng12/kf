<?php
namespace app\index\controller;

use controller\BaseIndex;
use think\Db;
use think\App;


class Index extends BaseIndex
{
    public function index()
    {
        $str=$this->test();
        dump($str);
    }

    public function hello($name = 'ThinkPHP5')
    {
        return 'hello,' . $name;
    }
}
