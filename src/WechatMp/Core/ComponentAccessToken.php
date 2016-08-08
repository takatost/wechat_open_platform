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

class ComponentAccessToken extends BaseAccessToken
{
    protected $componentRefreshToken = null;

    public function getTokenFromServer()
    {
        $componentRefreshToken = $this->getComponentRefreshToken();
        if ($componentRefreshToken === null) {
            $entFuwuhao = EnterpriseMp::where('app_id', $this->appId)->first();
            $componentRefreshToken = $entFuwuhao->component_refresh_token;
        }
        
        $wechatThird = new ThirdAuth();
        return $wechatThird->getAuthorizerToken($this->appId, $componentRefreshToken);
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