<?php
namespace app\common\model;

use think\Model;
use think\db;

class Wechat extends Model
{
    
    protected $table = 'wechat_fans';
    
    /**
     * 发起微信网页授权
     * @param number $type 0：获取openid，静默授权；1：获取用户的基本信息
     */
    public function oauth2($type=0){
        $appid=sys_config('appid', 'wxtest');
        $redirect_uri=request()->url(true);
        $snsapi=$type?'snsapi_userinfo':'snsapi_base';
        $url="https://open.weixin.qq.com/connect/oauth2/authorize?appid={$appid}&redirect_uri={$redirect_uri}&response_type=code&scope={$snsapi}&state=1012#wechat_redirect";
        redirect($url)->send();
    }
    
    /**
     * 网页授权回调
     * @param number $type 0：获取openid，静默授权；1：获取用户的基本信息
     * @return array
     */
    public function callback($type=0){
        $appid=sys_config('appid', 'wxtest');
        $appsecret=sys_config('appsecret', 'wxtest');
        $state=request()->get('state','');
        $code=request()->get('code','');
        if(empty($code) || empty($state)) return '';
        $url="https://api.weixin.qq.com/sns/oauth2/access_token?appid={$appid}&secret={$appsecret}&code={$code}&grant_type=authorization_code";
        $res=curl($url);
        if(isset($res['errcode'])) return $res;
        if(!$type){
            return $res;//{"access_token":"ACCESS_TOKEN","expires_in":7200,"refresh_token":"REFRESH_TOKEN","openid":"OPENID","scope":"SCOPE" }
        }elseif ($type=='oauth'){
            $url="https://api.weixin.qq.com/sns/userinfo?access_token={$res['access_token']}&openid={$res['openid']}&lang=zh_CN";
            $userInfo=curl($url);
            if(isset($userInfo['errcode'])) return $userInfo;
            $userInfo['nickname']=emojiEncode($userInfo['nickname']);
            if($id=$this->where('openid',$userInfo['openid'])->value('id')){
                $this->allowField(true)->save($userInfo,['id'=>$id]);
            }else{
                $this->allowField(true)->save($userInfo);
            }
            return $userInfo;
        }
    }
    
    
    /**
     * 获取用户基本信息(UnionID机制)
     * @param unknown $openid
     * @return number|mixed
     */
    public function getUserInfo($openid){
        $access_token=$this->getAccessToken();
        $url="https://api.weixin.qq.com/cgi-bin/user/info?access_token={$access_token}&openid={$openid}&lang=zh_CN";
        $userInfo=curl($url);
        $userInfo['nickname']=emojiEncode($userInfo['nickname']);
        if(isset($userInfo['errcode'])) return $userInfo;
        if($id=$this->where('openid',$userInfo['openid'])->value('id')){
            $this->allowField(true)->save($userInfo,['id'=>$id]);
        }else{
            $this->allowField(true)->save($userInfo);
        }
        return $userInfo;
    }
    
    /**
     * 获取调用接口AccessToken
     * @return unknown|mixed|\think\cache\Driver|boolean
     */
    private function getAccessToken(){
        $access_token=cache('access_token');
        if(!$access_token){
            $appid=sys_config('appid', 'wxtest');
            $appsecret=sys_config('appsecret', 'wxtest');
            $url='https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid='.$appid.'&secret='.$appsecret;
            $arr=curl($url);
            $access_token=$arr['access_token'];
            cache('access_token',$access_token,$arr['expires_in']);
            
        }
        return $access_token;
    }
    
    
}
