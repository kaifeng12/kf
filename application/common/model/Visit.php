<?php
namespace app\common\model;
use think\Model;
use think\Request;
use think\Db;

class Visit extends Model
{
    
    protected $type=[
        'date' => 'timestamp'
    ];
    
    public function visitList($limit,$page,$filter){
        $total=ceil($this->count()-1);
        $field='v.id,v.ip,adr,isp,forbidden,module,node,status,date,time';
        $data=$this->where('v.id','<>','1')->alias('v')->join('ip_info p','v.ip=p.ip')->field($field)->order('v.id asc')->limit($limit*($page-1),$limit)->select()->toArray();
        return [
            'code'=>0,
            'msg'=>'',
            'count'=>$total,
            'data'=>$data
        ];
    }
    
    
    
    
    /**
     * ip过滤
     * @param string $ip
     * @return boolean|number status
     */
    public function ip_filter($ip){
        if(preg_match('/^127\.0\.0/',$ip)) return 200;
        $node=request()->module().'/'.request()->controller().'/'.request()->action();
        $nodearr=explode('/', $node);
        $action=$nodearr[1].'/'.$nodearr[2];
        $action=='index/index' && $action='index';//简化访问主页的表述
        if($ipinfo=Db::name('ip_info')->where('ip',$ip)->find()){
            //已访问过
            $visit_info=$this->where(['ip'=>$ip])->select();
            $status=$ipinfo['forbidden']?404:200;
            foreach ($visit_info as $v){
                if($v['module']==$nodearr[0] && $v['node']==$action && $v['status']==$status){
                    $addid=$v['id'];
                }
            }
            if(isset($addid)){
                $this->where('id',$addid)->setInc('time');//次数加一
            }else{
                $type=$status==200?1:0;
                $this->record($ip, $nodearr[0], $action,$type);
            }
            return $status;
        }
        
        if(!$info=$this->get_info($ip)){
            $this->where('id',1)->update(['status',501]);
            return false;
        }
        $forbidden=$info['country_id']=='CN'?0:1;
        Db::name('ip_info')->insert([
           'ip'=>$ip,
            'adr'=>"{$info['country']}-{$info['region']}-{$info['city']}",
            'isp'=>$info['isp'],
            'forbidden'=>$forbidden,
        ]);
        if(!$forbidden){
            $this->record($ip,$nodearr[0],$action);
            return 200;
        }
        $this->record($ip,$nodearr[0],$action,0);
        return 404;
    }
    
    
    /**
     * 调用ip地址淘宝API
     * @param string $id
     * @return array
     */
    private function get_info($ip){
        $url = "http://ip.taobao.com/service/getIpInfo.php?ip={$ip}";
        $ret = file_get_contents($url);
        $arr = json_decode($ret,true);
        ($arr['code']==0 && $arr['data']) || $arr=[];
        return $arr['data'];
    }
    
    /**
     * 记录访问信息
     * @param array $info       ip信息
     * @param string $module    访问的模块
     * @param string $node      访问的节点
     * @param number $type      处理类型
     */
    private function record($ip,$module,$node,$type=1){
        $data=[
            'ip'=>$ip,
            'module'=>$module,
            'node'=>$node,
            'date'=>time(),
        ];    
        $type==1 && $data['status']=200;
        $type==0 && $data['status']=404;
        
        $this->save($data);
    }
}