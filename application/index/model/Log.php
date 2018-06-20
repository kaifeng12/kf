<?php

namespace app\index\model;

use think\Model;

class Log extends Model
{   
    
    /**
     * 获得指定条数的最新文章或最热文章 
     * @param int $num  要获得的文章数
     * @param int $new 默认1获得最新文章，0获得最热文章
     * @param int $type  0（默认）全部，1返回生活日志，2返回技术杂谈
     * @return mixed    成功返回文章数组array,失败返回false
     */
    public function getNewLog($num,$new=1,$type=0){
        
        if($new==1){
            if($type==0){
                $logs=$this->where('is_deleted=0')->order('date desc')->limit($num)->select()->toArray();
            }elseif ($type==1){
                $logs=$this->where('type=0 and is_deleted=0')->order('date desc')->limit($num)->select()->toArray();
            }elseif ($type==2){
                $logs=$this->where('type=1 and is_deleted=0')->order('date desc')->limit($num)->select()->toArray();
            }
        }else{
            if($type==0){
                $logs=$this->where('is_deleted=0')->order('click desc')->limit($num)->select()->toArray();
            }elseif ($type==1){
                $logs=$this->where('type=0 and is_deleted=0')->order('click desc')->limit($num)->select()->toArray();
            }elseif ($type==2){
                $logs=$this->where('type=1 and is_deleted=0')->order('click desc')->limit($num)->select()->toArray();
            }
        }
        if(!$logs) return false;
        changDate($logs,'date');
        return $logs;
    }
}