<?php
namespace WechatOP\OpenPlatform;

use EasyWeChat\Core\AbstractAPI;

class OpenPlatform extends AbstractAPI
{
    const API_GET_COMPONENT_ACCESS_TOKEN = "https://api.weixin.qq.com/cgi-bin/component/api_component_token";
    const API_GET_COMPONENT_PRE_AUTHCODE = "https://api.weixin.qq.com/cgi-bin/component/api_create_preauthcode";
    const API_GET_AUTHORIZER_AUTH_INFO   = "https://api.weixin.qq.com/cgi-bin/component/api_query_auth";
    const API_GET_AUTHORIZER_BASE_INFO   = "https://api.weixin.qq.com/cgi-bin/component/api_get_authorizer_info";
    const API_GET_AUTHORIZER_ACCESSTOKEN = "https://api.weixin.qq.com/cgi-bin/component/api_authorizer_token";
    const API_GET_AUTHORIZER_SETTING     = "https://api.weixin.qq.com/cgi-bin/component/api_get_authorizer_option";
    const API_SET_AUTHORIZER_SETTING     = "https://api.weixin.qq.com/cgi-bin/component/api_set_authorizer_option";

    /**
     * 获取第三方平台预授权码
     * @return \EasyWeChat\Support\Collection
     */
    public function getPreAuthCode()
    {
        $body = $this->parseJSON('json', [
            self::API_GET_COMPONENT_PRE_AUTHCODE,
            [
                'component_appid' => $this->getAccessToken()->getAppId()
            ]
        ]);

        //处理内容，返回预授权码
        return $body;
    }

    /**
     * 使用第三方平台预授权码换取公众号的授权信息
     * @param $code
     * @return \EasyWeChat\Support\Collection
     */
    public function getAuthorizerAuthInfo($code)
    {
        $body = $this->parseJSON('json', [
            self::API_GET_AUTHORIZER_AUTH_INFO,
            [
                'component_appid'    => $this->getAccessToken()->getAppId(),
                'authorization_code' => $code,
            ]
        ]);

        return $body;
    }

    /**
     * 获取授权方的账户信息
     * @param $authorizerAppId
     * @return \EasyWeChat\Support\Collection
     */
    public function getAuthorizerBaseInfo($authorizerAppId)
    {
        $body = $this->parseJSON('json', [
            self::API_GET_AUTHORIZER_BASE_INFO,
            [
                'component_appid'  => $this->getAccessToken()->getAppId(),
                'authorizer_appid' => $authorizerAppId,
            ]
        ]);

        return $body;
    }

    /**
     * 根据刷新令牌获取最新 OAuthToken
     * @param $authorizerAppId
     * @param $refreshToken
     * @return \EasyWeChat\Support\Collection
     */
    public function getTokenByRefreshToken($authorizerAppId, $refreshToken)
    {
        $body = $this->parseJSON('json', [
            self::API_GET_AUTHORIZER_ACCESSTOKEN,
            [
                'component_appid'          => $this->getAccessToken()->getAppId(),
                'authorizer_appid'         => $authorizerAppId,
                'authorizer_refresh_token' => $refreshToken
            ]
        ]);

        return $body;
    }

    /**
     * 获取授权方的选项设置信息
     * @param $authorizerAppId
     * @param $optionName
     * @return \EasyWeChat\Support\Collection
     */
    public function getAuthorizerSetting($authorizerAppId, $key)
    {
        $body = $this->parseJSON('json', [
            self::API_GET_AUTHORIZER_SETTING,
            [
                'component_appid'  => $this->getAccessToken()->getAppId(),
                'authorizer_appid' => $authorizerAppId,
                'option_name'      => $key
            ]
        ]);

        return $body;
    }

    /**
     * 设置授权方的选项信息
     * @param $authorizerAppId
     * @param $key
     * @param $value
     * @return \EasyWeChat\Support\Collection
     */
    public function setAuthorizerSetting($authorizerAppId, $key, $value)
    {
        $body = $this->parseJSON('json', [
            self::API_SET_AUTHORIZER_SETTING,
            [
                'component_appid'  => $this->getAccessToken()->getAppId(),
                'authorizer_appid' => $authorizerAppId,
                'option_name'      => $key,
                'option_value'     => $value
            ]
        ]);

        return $body;
    }
}