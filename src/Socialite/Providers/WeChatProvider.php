<?php
/*
 * This file is part of the takatost/wechat_open_platform.
 *
 * (c) takatost <takatost@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace WechatOP\Socialite\Providers;

use Symfony\Component\HttpFoundation\Request;
use Overtrue\Socialite\Providers\WeChatProvider as BaseWeChatProvider;
use Symfony\Component\HttpFoundation\Session\Session;
use WechatOP\Core\AccessToken;

/**
 * Class WeChatProvider
 * @package WechatOP\Socialite\Providers
 */
class WeChatProvider extends BaseWeChatProvider
{
    /**
     * Open Platform APP ID
     *
     * @var string
     */
    protected $openPlatformAppId;

    /**
     * Open Platform
     *
     * @var AccessToken
     */
    protected $openPlatformToken;

    public function __construct(Request $request, $openPlatformAppId, $openPlatformToken, $clientId, $clientSecret, $redirectUrl = null)
    {
        $session = new Session();
        $request->setSession($session);

        parent::__construct($request, new \Overtrue\Socialite\Config([]), $clientId, $clientSecret, $redirectUrl);

        $this->openPlatformAppId = $openPlatformAppId;
        $this->openPlatformToken = $openPlatformToken;
    }

    /**
     * {@inheritdoc}.
     */
    protected function getAuthUrl($state)
    {
        $path = 'oauth2/authorize';

        if (in_array('snsapi_login', $this->scopes, false)) {
            $path = 'qrconnect';
        }

        return $this->buildAuthUrlFromBase("https://open.weixin.qq.com/connect/{$path}", $state);
    }

    /**
     * {@inheritdoc}.
     */
    protected function getCodeFields($state = null)
    {
        return [
            'appid'         => $this->clientId,
            'redirect_uri'  => $this->redirectUrl,
            'response_type' => 'code',
            'scope'         => $this->formatScopes($this->scopes, $this->scopeSeparator),
            'state'         => $state,
            'component_appid' => $this->openPlatformAppId
        ];
    }

    /**
     * {@inheritdoc}.
     */
    protected function getTokenUrl()
    {
        return $this->baseUrl.'/oauth2/component/access_token';
    }

    /**
     * {@inheritdoc}.
     */
    protected function getTokenFields($code)
    {
        return [
            'appid'      => $this->clientId,
            'code'       => $code,
            'grant_type' => 'authorization_code',
            'component_appid' => $this->openPlatformAppId,
            'component_access_token' => $this->openPlatformToken->getToken()
        ];
    }
}
