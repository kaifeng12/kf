<?php
namespace app\common\model;
use think\Model;
use think\Request;
use think\Db;
use service\Http;

class Visit extends Model
{
    
    protected $type=[
        'date' => 'timestamp'
    ];
    
    public function visitList($limit,$page,$filter){
        $field='v.id,v.ip,adr,isp,forbidden,module,node,status,date,time';
        $db=$this->where('v.id','<>','1')->alias('v')->join('ip_info p','v.ip=p.ip')->field($field);
        //搜索筛选
        foreach (['v.ip','is_china','adr','node','forbidden','timerand'] as $k=>$f){
            if(isset($filter[$f]) && $filter[$f]!=''){
                if($k==1 || $k==4){
                    $db->where($f,$filter[$f]);
                }elseif ($k==5){
                    $date=explode('~',$filter[$f]);
                    $db->whereBetween('date',strtotime($date[0]).','.strtotime($date[1]));
                }elseif($k==3 && $filter[$f]=='admin') {
                    $db->whereLike('module',"%{$filter[$f]}%");
                }else{
                    $db->whereLike($f,"%{$filter[$f]}%");
                }
                
            }
        }
        $todb=clone $db; 
        $total=$todb->count();
        $data=$db->order('v.id desc')->limit($limit*($page-1),$limit)->select()->toArray();
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
        try {
            if(!$info=$this->get_info($ip)){
                $this->where('id',1)->update(['status',501]);
                throw new \Exception('淘宝接口出错');
            }
        } catch (\Exception $e) {
            try {
                //备用ip查询接口
                $ret = $this->getIPInfo($ip);
                if($ret){
                    $forbidden=($ret['countryCode']=='CN' || $ret['country']=='中国')?0:1;
                    $is_china=$forbidden ? 0 : 1;
                    Db::name('ip_info')->insert([
                        'ip'=>$ip,
                        'adr'=>"{$ret['country']}-{$ret['regionName']}-{$ret['city']}",
                        'isp'=>$ret['isp'],
                        'forbidden'=>$forbidden,
                        'is_china'=>$is_china
                    ]);
                    if(!$forbidden){
                        $this->record($ip,$nodearr[0],$action);
                    }else {
                        return 404;
                    }
                }
            } catch (\Exception $er) {
                return 404;
            }
            
            return 200;
        }
        
        $forbidden=($info['country_id']=='CN' || $info['country']=='中国')?0:1;
        $is_china=$forbidden ? 0 : 1;
        Db::name('ip_info')->insert([
           'ip'=>$ip,
            'adr'=>"{$info['country']}-{$info['region']}-{$info['city']}",
            'isp'=>$info['isp'],
            'forbidden'=>$forbidden,
            'is_china'=>$is_china
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
        $ret = Http::get($url);
        $arr = json_decode($ret,true);
        ($arr['code']==0 && $arr['data']) || $arr=['data'=>[]];
        return $arr['data'];
    }
    
    private function getIPInfo($ip){
        $url = "http://ip-api.com/json/{$ip}?lang=zh-CN";
        $ret = Http::get($url);
        $arr = json_decode($ret,true);
        (!empty($arr['status']) && $arr['status']=='success') || $arr=[];
        return $arr;
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