<?php
namespace app\common\model;
use think\Model;
use think\Request;

class Visit extends Model
{
    
    /**
     * ip过滤
     * @param string $ip
     * @return boolean|number status
     */
    public function ip_filter($ip){
        if(preg_match('/^127\.0\.0/',$ip)) return 200;
        if(!$info=$this->get_info($ip)){
            $this->where('id',1)->update(['status',501]);
            return false;
        }
        $node=request()->module().'/'.request()->controller().'/'.request()->action();     
        if($info['country_id']!=='CN'){
            $this->record($info,$node);
            return 200;
        }
        $this->record($info,$node,0);
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
     * @param array $info   ip信息
     * @param string $node  访问的节点
     * @param number $type  处理类型
     */
    private function record($info,$node,$type=1){
        $nodearr=explode('/', $node);
        $action=$nodearr[1].'/'.$nodearr[2];
        $action=='index/index' && $action='index';//简化访问主页的表述
        $data=[
            'ip'=>$info['ip'],
            'module'=>$nodearr[0],
            'node'=>$action,
            'adr'=>"{$info['country']}-{$info['region']}-{$info['city']}",
            'isp'=>$info['isp'],    
        ];    
        $type==1 && $data['status']=200;
        $type==0 && $data['status']=404;
        
        $this->save($data);
    }
}