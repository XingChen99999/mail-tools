<?php
/**
 * Author: vring
 * Date: 2024/4/1
 * Email: <971626354@qq.com>
 */

namespace vring\wechat\OfficialAccount;

//use EasyWeChat\OfficialAccount\Application;
use vring\util\Url;

class Oauth2
{
    /**
     * @param EasyWeChat\OfficialAccount\Application $app
     * @param string $url 获取到openid后的跳转url，如果有带参数，可先对整个url做下urlencode编码
     * @param string $scopes
     * @return array|void
     * @throws \Exception
     */
    static public function oauth2( $app,string $url,string $scopes = 'snsapi_userinfo')
    {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        ;
//        $aUrl = self::getOauth2Url($url,$flag);
        $auth_user  = $_SESSION["wechat_user"]??null;
        if ($auth_user){
            return $auth_user;
        }
        if (!$url){
            $url = Url::instance()->url([],true,'',true);
        }
        if (!isset($_GET['code'])){
            $app->oauth->scopes([$scopes])
//                ->setRequest($request)
                ->redirect($url)->sendHeaders();
            exit;
//            return ($rep);
//            header('location: '.$aUrl);
//            exit;
        }
        $code = $_GET['code'] ?? '';
        if ($code){
            /*  if (isset($_GET['userinfo'])){
                  $_GET['userinfo'] = json_decode(base64_decode($_GET['userinfo']),true);
              }*/
            $user = $app->oauth->user()->toArray();
            $_SESSION["wechat_user"] = $user;
            return $user;
        }
        throw new \Exception("授权异常，".Url::instance()->url());
    }

    /**
     * 需要安装 "overtrue/wechat": "~5.0",
     * @param EasyWeChat\OfficialAccount\Application $app
     * @param string $url
     * @param string $scopes
     * @return void
     * @throws \Exception
     */
    static public function mpJump( $app,callable $doneBack = null,string $url = '', string $scopes = 'snsapi_userinfo')
    {

        $user = self::oauth2($app,$url,$scopes);
        if ($user){
            $wechat_openid = $user['id'];
            $doneBack($user);
            require __DIR__.'/view/oauth.html';
            exit;
        }
    }
}