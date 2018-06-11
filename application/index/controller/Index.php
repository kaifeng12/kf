<?php
namespace app\index\controller;

class Index
{
    public function index()
    {
        dump('你们好');
    }

    public function hello($name = 'ThinkPHP5')
    {
        return 'hello,' . $name;
    }
}
