<?php
// +----------------------------------------------------------------------
// | Niucloud-admin 企业快速开发的saas管理平台
// +----------------------------------------------------------------------
// | 官方网址：https://www.niucloud.com
// +----------------------------------------------------------------------
// | niucloud团队 版权所有 开源版本可自由商用
// +----------------------------------------------------------------------
// | Author: Niucloud Team
// +----------------------------------------------------------------------

namespace app\adminapi\controller\login;

use app\service\admin\auth\ConfigService;
use app\service\admin\auth\LoginService;
use app\service\core\addon\WapTrait;
use core\base\BaseAdminController;
use think\Response;

class Login extends BaseAdminController
{
    use WapTrait;

    /**
     * 微信一键登录/无用
     */
    public function loginWchat()
    {
        $data = $this->request->params([
            [ 'openId', '' ],
            [ 'nickName', '' ],
            [ 'mobile', '' ]
        ]);
        return success($data);
        $result = ( new LoginService() )->loginWchat($app_id,$app_secret,$code);
        return success($result);

    }


    /**
     * 登录
     * @return Response
     * login_type 1手机密码登录 2邮箱密码登录   3:手机验证码登录 4邮箱验证码登录 5 一键登录，不存在自动注册  
     * 6微信一键登录，不存在自动注册 7支付宝一键登录，不存在自动注册 8苹果账号一键登录 
     */
    public function login($app_type)
    {

        $data = $this->request->params([
            [ 'username', '' ],
            [ 'password', '' ],
            [ 'login_type', '0' ],
            [ 'wx_openid', '' ],
            [ 'real_name', '' ],
            [ 'wx_image', '' ],
            [ 'user_id', '' ],
            [ 'ail_image', '' ],
            [ 'google_openid', '' ],
            [ 'google_image', '' ],
            [ 'ail_code', '' ],
            [ 'wx_code', '' ],
            [ 'wx_ali_mobile', '' ],
            [ 'mobile', '' ],
        ]);
        
        //获取微信登录信息
        if($data['login_type'] == "6" and $data['wx_openid'] == ""){
            if(!$data['wx_code']){
                return fail('wx_code_error/缺少微信授权code');
            }
            $wx_info = $this->getWxInfo($data['wx_code']);
            $data['wx_openid'] = $wx_info['openid'];
            $data['real_name'] = $wx_info['nickname'];
            $data['wx_image'] = $wx_info['headimgurl'];
        }


        //获取支付宝登录信息
        if($data['login_type'] == "7" and $data['user_id'] == ""){
            if(!$data['ail_code']){
                return fail('ail_code_error/缺少支付宝授权code');
            }
            $ail_info = $this->getAilInfo($data['ail_code']);
            $data['user_id'] = $ail_info['user_id'];
            $data['real_name'] = $ail_info['nick_name'];
            $data['ail_image'] = $ail_info['avatar'];
        }  

        
        $login_type = strval($data['login_type']);
        if($login_type== "3" or $login_type == "4" or $login_type == "5" or $login_type == "6" or $login_type == "7" or $login_type == "8"){
            $result = ( new LoginService() )->login($data[ 'username' ], "0-01", $app_type,$login_type,$data);
        }else{
            $result = ( new LoginService() )->login($data[ 'username' ], $data[ 'password' ], $app_type,$login_type,$data);
        }
        if (array_key_exists('msg', $result)) {
            return fail($result['msg']);
        }
        return success($result);

    }


    /**
     * 根据code获取微信用户openid,昵称，头像等信息
     */
    public function getWxInfo($code){

        $appID     = "wx7508cabf4516b560";
        $appSecret = file_get_contents(__DIR__ . '/appsecret.txt'); // 你的应用私钥
        /**
         * 第一步：通过 code 获取 access_token 和 openid
         */
        $tokenUrl = "https://api.weixin.qq.com/sns/oauth2/access_token?appid={$appID}&secret={$appSecret}&code={$code}&grant_type=authorization_code";

        $tokenData = json_decode(file_get_contents($tokenUrl), true);

        if (!isset($tokenData['openid'])) {
            exit(json_encode(['error' => '获取 access_token 失败', 'detail' => $tokenData]));
        }

        $accessToken = $tokenData['access_token'];
        $openid      = $tokenData['openid'];
        $unionid     = isset($tokenData['unionid']) ? $tokenData['unionid'] : null;

        /**
         * 第二步：拉取用户信息
         */
        $userInfoUrl = "https://api.weixin.qq.com/sns/userinfo?access_token={$accessToken}&openid={$openid}&lang=zh_CN";

        $userInfo = json_decode(file_get_contents($userInfoUrl), true);

        if (!isset($userInfo['openid'])) {
            exit(json_encode(['error' => '获取用户信息失败', 'detail' => $userInfo]));
        }

        /**
         * 返回结果
         */
        return $userInfo;

    }




    /**
     * 根据code获取支付宝user_id,昵称,头像
     */
    public function getAilInfo($code){

        $app_id = "2021005110684954";  
        $app_private_key = file_get_contents(__DIR__ . '/app_private_key.pem'); // 你的应用私钥
        $alipay_gateway = "https://openapi.alipay.com/gateway.do";  
        
        // 2. 封装请求参数（换取 access_token）
        $params = [
            "app_id"     => $app_id,
            "method"     => "alipay.system.oauth.token",
            "format"     => "JSON",
            "charset"    => "utf-8",
            "sign_type"  => "RSA2",
            "timestamp"  => date("Y-m-d H:i:s"),
            "version"    => "1.0",
            "grant_type" => "authorization_code",
            "code"       => $code,
        ];
        
        // / 3. 生成签名
        ksort($params);
        $signStr = "";
        foreach ($params as $k => $v) {
            $signStr .= $k . "=" . $v . "&";
        }
        $signStr = rtrim($signStr, "&");
        openssl_sign($signStr, $sign, $app_private_key, OPENSSL_ALGO_SHA256);
        $params["sign"] = base64_encode($sign);

        // 4. 请求支付宝接口
        $url = $alipay_gateway . "?" . http_build_query($params);
        $response = file_get_contents($url);
        $result = json_decode($response, true);

        $tokenResp = $result["alipay_system_oauth_token_response"] ?? null;
        if (!$tokenResp || empty($tokenResp["access_token"])) {
            exit(json_encode(["error" => "获取 access_token 失败", "resp" => $result]));
        }

        $access_token = $tokenResp["access_token"];

        // 5. 获取用户信息
        $params = [
            "app_id"     => $app_id,
            "method"     => "alipay.user.info.share",
            "format"     => "JSON",
            "charset"    => "utf-8",
            "sign_type"  => "RSA2",
            "timestamp"  => date("Y-m-d H:i:s"),
            "version"    => "1.0",
            "auth_token" => $access_token,
        ];

        // 签名
        ksort($params);
        $signStr = "";
        foreach ($params as $k => $v) {
            $signStr .= $k . "=" . $v . "&";
        }
        $signStr = rtrim($signStr, "&");
        openssl_sign($signStr, $sign, $app_private_key, OPENSSL_ALGO_SHA256);
        $params["sign"] = base64_encode($sign);

        $url = $alipay_gateway . "?" . http_build_query($params);
        $response = file_get_contents($url);
        $result = json_decode($response, true);

        $userResp = $result["alipay_user_info_share_response"] ?? null;
        if (!$userResp || $userResp["code"] !== "10000") {
            exit(json_encode(["error" => "获取用户信息失败", "resp" => $result]));
        }

        // 6. 处理用户信息（比如存数据库）
        $user = [
            "user_id"   => $userResp["user_id"],   // 支付宝唯一ID
            "nick_name" => $userResp["nick_name"] ?? "",
            "avatar"    => $userResp["avatar"] ?? "",
            "gender"    => $userResp["gender"] ?? "",
        ];

        return $user;
    }

    /**
     * 登出
     * @return Response
     */
    public function logout()
    {
        ( new LoginService )->logout();
        return success('LOGOUT');
    }

    /**
     * 获取登录设置
     * @return Response
     */
    public function getConfig()
    {
        return success(( new ConfigService() )->getConfig());
    }
}
