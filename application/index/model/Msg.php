<?php

namespace app\index\model;

use think\Model;
use think\db;

class Msg extends Model
{   
    //获得留言板信息
    public function getMsg(){
        
        //连表查询获得qq用户信息
        if(!$first=$this->where(['rid'=>0,'is_deleted'=>0])->alias('m')->join('qq q','m.qid=q.id')->field('m.*,head,name,openid')->select()->toArray()) return false;
        foreach ($first as &$v){
            $v['res']=[];
            //是否有回复
            if($res=$this->where(['rid'=>$v['id'],'is_deleted'=>0])->alias('m')->join('qq q','m.qid=q.id')->field('m.*,head,name,openid')->select()->toArray()){
                changDate($res,'date',1);//替换日期
                $v['res']=$res;
            }
        }
        changDate($first, 'date',1);
        return $first;
    }
    
    //只获得最新的3个留言
    public function getMainMsg($num=3){ 
        if($first=$this->where('rid=0 and is_deleted=0')->alias('m')->join('qq q','q.id=m.qid')->order('date desc')->limit($num)->select()->toArray()){
            changDate($first,'date');
            return $first;
        }
        return false;
    }
    
    public function addmsg($openid,$text,$rid){
        if(!$qq=Db::name('qq')->where("openid='{$openid}'")->find()){
            return false;
        }
        
        $mess=[
            'uid' => 1,
            'rid' => $rid,
            'date' => time(),
            'text' =>$text,
            'qid' => $qq['id'],
        ];
        
        if($this->save($mess)) return true;
        return false;
        
    }

}