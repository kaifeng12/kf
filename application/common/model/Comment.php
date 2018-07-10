<?php
namespace app\common\model;

use think\Model;
use think\Db;

class Comment extends Model
{
    public $msg;
    
    //获得指定文章的评论及其回复
    public function getCom($id,$rid=0){
        //连表查询得到对应qq用户信息
        $first=$this->where(['is_deleted'=>0,'rid'=>$rid,'c.lid'=>$id])->alias('c')->join('qq q','q.id = c.qid')->field('c.id as id,lid,qid,rid,date,text,name,head')->select()->toArray();
        if(!$first){           //得到所有一级评论
            return [];
        }

        foreach ($first as &$v){
            if($res=$this->where(['rid'=>$v['id'],'is_deleted'=>0,'lid'=>$id])->alias('c')->join('qq q','q.id = c.qid')->select()->toArray()){
                //如果存在二级回复，替换所有日期
                changDate($res,'date',1);
                $v['res']=$res;                
            }else $v['res']=[];     //无二级回复值为空，用来在模板中判断
        }
        changDate($first,'date',1);    
        return $first;
    }
    
    //增加评论
    public function addComment($openid,$text,$rid,$lid){
        
        if(!$qq=Db::name('qq')->where('openid',$openid)->find()){
            $this->msg="qq用户不存在";
            return 0;
        }

        $mess=[
            'lid' => $lid,
            'rid' => $rid,
            'date' => time(),
            'text' =>$text,
            'qid' => $qq['id'],
        ];
        
        if($this->save($mess)) return true;
        $this->msg="数据添加失败";
        return 0;
        
    }
    
}