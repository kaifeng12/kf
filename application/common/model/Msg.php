<?php

namespace app\common\model;

use think\Model;
use think\db;

class Msg extends Model
{   
    //获得留言板信息
    public function getMsg(){
        
        //连表查询获得qq用户信息
        $first=$this->getAllMsg(0);
        foreach ($first as &$v){
            $v['res']=[];
            //是否有回复
            if($res=$this->getAllMsg($v['id'])){
                changDate($res,'date',1);//替换日期
                $v['res']=$res;
            }
        }
        changDate($first, 'date',1);
        return $first;
    }
    
    //只获得最新的3个留言
    public function getMainMsg($num=3){
        $msgs=$this->getAllMsg(0,$num);
        if($msgs){
            changDate($msgs,'date');
            return $msgs;
        }
        return false;
    }
    

    /**
     * 判断链qq表，wx表
     * @param number $num
     * @return array|false
     */
    private function getAllMsg($rid=0,$num=0){
        $num=empty($num)?9999:$num;
        $msgs=$this->where(['rid'=>$rid,'is_deleted'=>0])->order('date desc')->limit($num)->select()->toArray();
        foreach ($msgs as &$v){
            if($v['type']=='qq'){
                $qq=Db::name('qq')->where('id',$v['qid'])->field('id',true)->find();
                $v=array_merge($v,$qq);
            }elseif($v['type']=='wx'){
                $wx=Db::name('WechatFans')->where('id',$v['wid'])->field('nickname as name,headimgurl as head,openid')->find();
                $v=array_merge($v,$wx);
            }
            $v['name']=emojiDecode($v['name']);
        }
        return $msgs;
    }
    
    /**
     * 留言或回复
     * @param string $openid
     * @param string $text  
     * @param int $rid  回复id,0则为留言
     * @param string $type qq | wx
     * @return boolean
     */
    public function addmsg($openid,$text,$rid,$type){
        $mess=[
            'uid' => 1,
            'rid' => $rid,
            'date' => time(),
            'text' =>$text,
            'type' => $type
        ];
        if($type=='qq'){
            if(!$qq=Db::name('qq')->where("openid='{$openid}'")->find()){
                return false;
            }
            $mess['qid']=$qq['id'];
        }elseif ($type=='wx'){
            if(!$wx=Db::name('WechatFans')->where("openid='{$openid}'")->find()){
                return false;
            }
            $mess['wid']=$wx['id'];
        }
        if($this->save($mess)) return true;
        return false;
        
    }

}