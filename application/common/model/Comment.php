<?php
namespace app\common\model;

use think\Model;
use think\Db;

class Comment extends Model
{
    public $msg;
    
    //获得指定文章的评论及其回复
    public function getCom($id,$rid=0){
        $first=$this->getAllMsg($id);
        if(!$first){           //得到所有一级评论
            return [];
        }

        foreach ($first as &$v){
            if($res=$this->getAllMsg($id,$v['id'])){
                //如果存在二级回复，替换所有日期
                changDate($res,'date',1);
                $v['res']=$res;
            }else $v['res']=[];     //无二级回复值为空，用来在模板中判断
        }
        changDate($first,'date',1);    
        return $first;
    }
    
    
    /**
     * 判断链qq表，wx表
     * @param int $lid  文章id
     * @param int $rid  回复id
     * @return array|false
     */
    private function getAllMsg($lid,$rid=0){
        $coments=$this->where(['rid'=>$rid,'is_deleted'=>0,'lid'=>$lid])->order('date desc')->select()->toArray();
        foreach ($coments as &$v){
            if($v['type']=='qq'){
                $qq=Db::name('qq')->where('id',$v['qid'])->field('id',true)->find();
                $v=array_merge($v,$qq);
            }elseif($v['type']=='wx'){
                $wx=Db::name('WechatFans')->where('id',$v['wid'])->field('nickname as name,headimgurl as head,openid')->find();
                $v=array_merge($v,$wx);
            }
            $v['name']=emojiDecode($v['name']);
        }
        return $coments;
    }
    
    /**
     * 增加评论
     * @param string $openid
     * @param string $text  内容
     * @param int $rid  回复的评论id
     * @param int $lid  文章id
     * @param string    用户类型 qq | wx
     * @return number|boolean
     */
    public function addComment($openid,$text,$rid,$lid,$type){
        $mess=[
            'lid' => $lid,
            'rid' => $rid,
            'date' => time(),
            'text' =>$text,
            'type' => $type,
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