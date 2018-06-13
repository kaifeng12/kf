<?php

namespace app\index\model;

use think\Model;

class Msg extends Model
{   
    //获得留言板信息
    public function getMsg(){
        
        //连表查询获得qq用户信息
        $first=$this->where('r_id=0 and m_isdelete=0')->join('b_qq ON b_msg.q_id = b_qq.q_id')->select();
        
        if(!$first){           //得到所有一级回复
            return false;
        }
        for($i=0;$i< count($first);$i++){
            $where['r_id']=$first[$i]['m_id'];
            $where['m_isdelete']='0';
            if($res=$this->where($where)->join('b_qq ON b_msg.q_id = b_qq.q_id')->select()){
                //如果存在二级回复，替换所有日期
                $res=changDate($res,'c_date',1);
                //二级回复信息加到所属一级回复数组下
                $first[$i]['res']=$res;
            }else $first[$i]['res']=array();         //无二级回复值为空，用来在模板中判断
            //$first[$i]['c_date']= date('Y-m-d H:i:s',$first[$i]['c_date']);
        }
        $first=changDate($first,'c_date',1);
        
        return $first;
        
        
    }
    
    //只获得最新的3个留言
    public function getMainMsg($num=3){ 
        if($first=$this->where('rid=0 and is_deleted=0')->alias('m')->join('qq q','q.id=m.qid')->order('date desc')->limit($num)->select()->toArray()){
            $first=changDate($first,'date');
            return $first;
        }else return false;
    }
    
    public function addmsg($openid,$text,$r_id){
        $QQ=D('qq');
        
        if(!$qq=$QQ->where("q_openid='{$openid}'")->find()){
            return '没有该qq';
        }
        
        $mess=[
            'u_id' => 1,
            'r_id' => $r_id,
            'c_date' => time(),
            'c_text' =>$text,
            'q_id' => $qq['q_id'],
        ];
        
        if($mid=$this->add($mess)) return $mid;
        return '添加失败';
        
    }

}