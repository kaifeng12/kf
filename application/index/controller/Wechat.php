<?php
namespace app\index\controller;

use think\Db;
use think\App;
use think\Controller;
class Wechat extends Controller
{
    
    public function getToken(){
        
        $signature=$this->request->get('signature');//进行对比用
        $timestamp=$this->request->get('timestamp');//时间戳
        $nonce=$this->request->get('nonce');//随机数
        $echostr=$this->request->get('echostr','');//最后要输出的内容，第一次验证才有
        
        $token=sys_config('token', 'wxtest');//用户自己定义的token
        $tmpArr=[$timestamp,$nonce,$token];//组装

        sort($tmpArr,SORT_STRING);//字典序排序
        $tmpStr=implode('',$tmpArr);
        $tmpStr=sha1($tmpStr);//sha1加密
        
        if($signature==$tmpStr && $echostr){
            echo $echostr;
            exit;
        }else{
            
            $this->responeMsg();//不是第一次验证接口，执行其他业务逻辑
        }
        
    }
    
    public function responeMsg(){
        
        $postArr=file_get_contents("php://input");//接受微信post过来的xml数据
        //Db::name('test')->insert(['name'=>'xml','value'=>$postArr]);
        $xml=simplexml_load_string($postArr,'SimpleXMLElement', LIBXML_NOCDATA);//把xml转成对象
        $eJSON = json_encode($xml);//借助json转换带有CDATA的xml成对象
        $postObj = json_decode($eJSON);
        /*微信关注或取消事件xml格式
        <xml>
            <ToUserName>< ![CDATA[toUser] ]></ToUserName>
            <FromUserName>< ![CDATA[FromUser] ]></FromUserName>
            <CreateTime>123456789</CreateTime>
            <MsgType>< ![CDATA[event] ]></MsgType>
            <Event>< ![CDATA[subscribe] ]></Event>
        </xml>*/
        //判断是否为订阅事件类型      
        $msgType=strtolower($postObj->MsgType);
        if($msgType=='event'){           
            if(strtolower($postObj->Event)=='subscribe'){
                
                //用户订阅事件
                //需要返回信息，那么原来的发送者和接收者角色调换
                $fromUser=$postObj->ToUserName;
                $toUser=$postObj->FromUserName;
                
                $time=time();
                $msgType='text';
                $content="欢迎关注KF，更多分享请查看历史消息";
                $send="<xml><ToUserName><![CDATA[%s]]></ToUserName><FromUserName><![CDATA[%s]]></FromUserName><CreateTime>%s</CreateTime><MsgType><![CDATA[%s]]></MsgType><Content><![CDATA[%s]]></Content></xml>";
                $str=sprintf($send,$toUser,$fromUser,$time,$msgType,$content);//把信息填装进上面xml模版
                
                echo $str; 
            }
        }elseif ($msgType=='text'){
            
            //这一段不应在这配置
            $msg=$postObj->Content;
            $ASCmsg=$this->ascii($msg);
            $ASCtu=$this->ascii('图片');
            $ASCtu2=$this->ascii('美图');
            $name=['凯峰','李凯峰','开疯'];
            
            
            if(in_array($msg, $name)){
                $respone='叫我干嘛';        
            }elseif (strpos($ASCmsg,$ASCtu)!==false or strpos($ASCmsg,$ASCtu2)!==false){
                $respone='收藏美图可以到我博客相册收藏哦：<a href="http://www.likaifeng.xyz/index/Photo/photo">凯峰的博客</a>';
            }else{
                $respone="不知道你在说啥，请输入特定的词语哦";
            }
            
            $fromUser=$postObj->ToUserName;
            $toUser=$postObj->FromUserName;
            
            $time=time();
            $msgType='text';
            $content=$respone;
            $send="<xml><ToUserName><![CDATA[%s]]></ToUserName><FromUserName><![CDATA[%s]]></FromUserName><CreateTime>%s</CreateTime><MsgType><![CDATA[%s]]></MsgType><Content><![CDATA[%s]]></Content></xml>";
            $str=sprintf($send,$toUser,$fromUser,$time,$msgType,$content);//把信息填装进上面xml模版
            echo $str;
        }
        
    }
    
    public function setItem(){

        $access_token=$this->getAccessToken();
        $url='https://api.weixin.qq.com/cgi-bin/menu/create?access_token='.$access_token;
 
        $itemArr=[
            'button' =>[
                [
                    'name'=>urlencode('图片收藏'),
                    'sub_button'=>[
                        [
                            'name'=>urlencode('每日一图'),
                            'type'=>'click',
                            'key'=>'dayphoto'
                        ],//第一个一级菜单的第一个二级菜单
                        [
                            'name'=>urlencode('更多图片'),
                            'type'=>'view',
                            'url'=>'http://www.likaifeng.xyz/index/Photo/index'
                        ]//第一个一级菜单的第二个二级菜单
                    ],

                ],//第一个一级菜单
                [
                    'name'=>urlencode('访问博客'),
                    'type'=>'view',
                    'url'=>'http://www.likaifeng.xyz'
                ],//第二个一级菜单
                [
                    'name'=>urlencode('欢迎留言'),
                    'type'=>'view',
                    'url'=>'http://www.likaifeng.xyz/index/Msg/msg'
                ]//第三个一级菜单
            ]
        ];//菜单内容
        $itemJson=urldecode(json_encode($itemArr));//对中文数据进行urldecode
        
        $res=$this->curl($url,'post','json',$itemJson);
        dump($res);
    }
    
    private function getAccessToken(){
        $access_token=cache('access_token');
        if(!$access_token){
            
            $appid=sys_config('appid', 'wxtest');
            $appsecret=sys_config('appsecret', 'wxtest');
            $url='https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid='.$appid.'&secret='.$appsecret;
            
            $arr=$this->curl($url);
            
            $access_token=$arr['access_token'];
            cache('access_token',$access_token,['expires'=>$arr['expires_in']]);
            
        }
        return $access_token;
    }
    
    
    /**
     * curl获取接口数据
     * @param string $url
     * @param string $type
     * @param string $res
     * @param string $arr
     */
    private function curl($url,$type='get',$res='json',$arr=''){
        $ch=curl_init();        
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        if ($type=='post'){
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $arr);
        }
        
        $output=curl_exec($ch);
        
        if($res='json'){
            if(curl_errno($ch)){
                //出错
                return curl_errno($ch);
            }else{
                curl_close($ch);
                return json_decode($output,true);
            }
        }
    }
    

    
    //转换ASCII码，用于匹配查询中文
    private function ascii($str){
        
        $str=mb_convert_encoding($str, 'gbk');
        $change_after='';
        for($i=0;$i<strlen($str);$i++){
            $temp_str=dechex(ord($str[$i]));
            $change_after.=$temp_str[1].$temp_str[0];
        }
        return strtoupper($change_after);
    }
    
    
    public function getUserInfo(){
        $is_wx=strpos($_SERVER["HTTP_USER_AGENT"], 'MicroMessenger');
        dump($_SERVER["HTTP_USER_AGENT"]);
        if($is_wx){
            halt('yes');
        }else halt('no');
        $appid=sys_config('appid', 'wxtest');
        $redirect_uri=url('oauth_callback','','html',true);
        $url="https://open.weixin.qq.com/connect/oauth2/authorize?appid={$appid}&redirect_uri={$redirect_uri}&response_type=code&scope=snsapi_userinfo&state=1012#wechat_redirect";
        $this->redirect($url);
    }
    
    public function oauth_callback(){
        $code=$this->request->param('code','');
        $state=$this->request->param('state','');
        if(empty($code) || empty($state)) return false;
        $appid=sys_config('appid', 'wxtest');
        $appsecret=sys_config('appsecret', 'wxtest');
        $url="https://api.weixin.qq.com/sns/oauth2/access_token?appid={$appid}&secret={$appsecret}&code={$code}&grant_type=authorization_code";
        $res=$this->curl($url);
        if(isset($res['errcode'])) return 'error';
        
        $url="https://api.weixin.qq.com/sns/userinfo?access_token={$res['access_token']}&openid={$res['openid']}&lang=zh_CN";
        $userInfo=$this->curl($url);
        halt($userInfo);
    }
    

}