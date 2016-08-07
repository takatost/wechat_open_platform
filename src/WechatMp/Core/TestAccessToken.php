<?php

namespace WechatOP\WechatMp\Core;

use EasyWeChat\Core\AccessToken as BaseAccessToken;
use WechatOP\OpenPlatform\OpenPlatform;

class TestAccessToken extends BaseAccessToken
{
    protected $openPlatform;
    protected $queryAuthCode;

    public function __construct(OpenPlatform $openPlatform, $queryAuthCode)
    {
        parent::__construct('wx570bc396a51b8ff8', '');

        $this->openPlatform = $openPlatform;
        $this->queryAuthCode = $queryAuthCode;
    }

    public function getTokenFromServer()
    {
        $authInfo = $this->openPlatform->getAuthorizerAuthInfo($this->queryAuthCode);
        
        return [
            'access_token' => $authInfo->authorization_info->authorizer_access_token,
            'expires_in' => $authInfo->authorization_info->expires_in
        ];
    }

    /**
     * @return string
     */
    public function getQueryAuthCode()
    {
        return $this->queryAuthCode;
    }

    /**
     * @param string $queryAuthCode
     */
    public function setQueryAuthCode($queryAuthCode)
    {
        $this->queryAuthCode = $queryAuthCode;
    }
    
}