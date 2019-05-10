<?php

namespace app\common\model;

use think\Model;

class Node extends Model
{   
    
    /**
     * 获取节点树
     */
    public function getNodeTree()
    {
        $dirPath = env('app_path');
        $nodes = [];
        
        foreach ($this->scanDirFile($dirPath) as $filename) {
            $matches = [];
            if (!preg_match('|/(\w+)/controller/(\w+)|', str_replace(DIRECTORY_SEPARATOR, '/', $filename), $matches) || count($matches) !== 3) {
                continue;
            }
            $className = env('app_namespace') . str_replace('/', '\\', $matches[0]);
            if (!class_exists($className)) {
                continue;
            }
            
            //获取所有方法
            $classMethods = get_class_methods($className);
            //排除父类方法
            if($parentClass = get_parent_class($className)){
                $parentClassMethods = get_class_methods($parentClass);
                $classMethods = array_diff($classMethods, $parentClassMethods);
            }
            
            foreach ($classMethods as $funcName) {
                if (strpos($funcName, '_') !== 0 && $funcName !== 'initialize' && !in_array($funcName, [''])) {
                    $nodes[] = $this->parseNodeStr("{$matches[1]}/{$matches[2]}") . '/' . strtolower($funcName);
                }
            }
        }
        return $nodes;
    }

    /**
     * 搜索指定目录中指定后缀的文件
     */
    private function scanDirFile($dirPath, $data = [], $ext = 'php')
    {
        foreach (scandir($dirPath) as $dir) {
            if (strpos($dir, '.') === 0) {
                continue;
            }
            $tmpPath = realpath($dirPath . DIRECTORY_SEPARATOR . $dir);
            if (is_dir($tmpPath)) {
                $data = array_merge($data, self::scanDirFile($tmpPath));
            } elseif (pathinfo($tmpPath, 4) === $ext) {
                $data[] = $tmpPath;
            }
        }
        return $data;
    }
    
    /**
     * 组装节点字符
     */
    private function parseNodeStr($node)
    {
        $tmp = [];
        foreach (explode('/', $node) as $name) {
            $tmp[] = strtolower(trim(preg_replace("/[A-Z]/", "_\\0", $name), "_"));
        }
        return trim(join('/', $tmp), '/');
    }
    
    /**
     * 对节点分级，检查出控制器节点
     */
    public function nodeToTree($nodes)
    {
        $nodeData = [];
        foreach ($nodes as $v){
            $module = explode('/', $v)[0];
            $controller = explode('/', $v)[1];
            $nodeData[$module][$module.'/'.$controller][]=$v;
        }
        return $nodeData;
    }
    
    
}