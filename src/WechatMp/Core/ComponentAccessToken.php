<?php
/*
 * This file is part of the takatost/wechat_open_platform.
 *
 * (c) takatost <takatost@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace WechatOP\WechatMp\Core;

use EasyWeChat\Core\AccessToken as BaseAccessToken;
use WechatOP\OpenPlatform\OpenPlatform;

class ComponentAccessToken extends BaseAccessToken
{
    protected $openPlatform;
    protected $componentRefreshToken;

    public function __construct($appId, OpenPlatform $openPlatform, $componentRefreshToken)
    {
        parent::__construct($appId, '');

        $this->openPlatform = $openPlatform;
        $this->componentRefreshToken = $componentRefreshToken;
    }

    public function getTokenFromServer()
    {
        $componentRefreshToken = $this->getComponentRefreshToken();
        $authToken = $this->openPlatform->getTokenByRefreshToken($this->appId, $componentRefreshToken);
        
        return [
            'access_token' => $authToken['authorizer_access_token'],
            'expires_in' => $authToken['expires_in']
        ];
    }
    
    /**
     * @return mixed
     */
    public function getComponentRefreshToken()
    {
        return $this->componentRefreshToken;
    }

    /**
     * @param mixed $componentRefreshToken
     */
    public function setComponentRefreshToken($componentRefreshToken)
    {
        $this->componentRefreshToken = $componentRefreshToken;
    }
    
}