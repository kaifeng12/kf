<?php
namespace app\common\model;

use think\Model;
use think\db;

class Qq extends Model
{
    
    public function callback($code){        
        $url="https://graph.qq.com/oauth2.0/token?grant_type=authorization_code&client_id=101450092&client_secret=df8d25dfe66beebd1f78e8367c0deda2&code={$code}&redirect_uri=http://www.likaifeng.xyz/index/Msg/callback.html";
        $Access_Token=file_get_contents($url);
        if($Access_Token){
            $Openidurl="https://graph.qq.com/oauth2.0/me?{$Access_Token}";
            $Openid = file_get_contents($Openidurl);
            $Openid = substr($Openid,45,32);
            
            $getInfoUrl = "https://graph.qq.com/user/get_user_info?{$Access_Token}&oauth_consumer_key=101450092&openid={$Openid}";
            
            $json = file_get_contents($getInfoUrl);
            $QQUserInfo = json_decode($json,true);
            if(isset($QQUserInfo['figureurl_qq_2']) && isset($QQUserInfo['nickname'])){
                $qinfo=[
                    'name' => $QQUserInfo['nickname'],
                    'head' => $QQUserInfo['figureurl_qq_2'],
                    'openid' => $Openid
                    
                ];
                
                if($this->where("openid='{$Openid}'")->find()){
                    $this->save($qinfo,['openid'=>$Openid]);                    
                    return $qinfo;
                }else{                               
                    if($this->save($qinfo)){
                        return $qinfo; 
                    }else{
                        return false;
                    }
                }
                
            }
        }
        return false;
        
    }
    
}
