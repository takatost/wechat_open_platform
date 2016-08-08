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

use Overtrue\Socialite\AccessTokenInterface;
use Overtrue\Socialite\Providers\AbstractProvider;
use Overtrue\Socialite\User;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use WechatOP\Core\AuthAccessToken;
use WechatOP\Foundation\Config;
use WechatOP\OpenPlatform\OpenPlatform;

class WechatOpenPlatformServiceProvider extends AbstractProvider
{
    /**
     * @var OpenPlatform
     */
    protected $openPlatform;

    /**
     * @var Config
     */
    protected $config;

    public function __construct(Request $request, OpenPlatform $openPlatform, Config $config)
    {
        parent::__construct(
            $request,
            $config->get('app_id'),
            $config->get('app_secret'),
            $config->get('redirect_url')
        );

        $session = new Session();
        $request->setSession($session);

        $this->request = $request;
        $this->openPlatform = $openPlatform;
        $this->config = $config;
    }

    /**
     * {@inheritdoc}.
     */
    protected function getAuthUrl($state)
    {
        $preCode = $this->openPlatform->getPreAuthCode();

        return $this->buildAuthUrl(
            "https://mp.weixin.qq.com/cgi-bin/componentloginpage",
            $preCode->get('pre_auth_code')
        );
    }

    /**
     * {@inheritdoc}.
     */
    protected function buildAuthUrl($url, $preCode)
    {
        $query = http_build_query($this->getPreCodeFields($preCode), '', '&', PHP_QUERY_RFC1738);

        return $url . '?' . $query;
    }

    /**
     * {@inheritdoc}.
     */
    protected function getPreCodeFields($preCode)
    {
        return [
            'component_appid' => $this->config->get('app_id'),
            'pre_auth_code'   => $preCode,
            'redirect_uri'    => $this->config->get('redirect_url')
        ];
    }

    /**
     * {@inheritdoc}.
     */
    public function getAccessToken($code)
    {
        $authInfo = $this->openPlatform->getAuthorizerAuthInfo($code);

        return new AuthAccessToken($authInfo);
    }

    /**
     * {@inheritdoc}.
     */
    protected function getUserByToken(AccessTokenInterface $token)
    {
        $response = $this->openPlatform->getAuthorizerBaseInfo($token->get('authorizer_appid'));

        return $response;
    }

    /**
     * {@inheritdoc}.
     */
    protected function mapUserToObject(array $user)
    {
        return new User([
            'id'                 => $this->arrayItem($user, 'authorization_info.appid'),
            'name'               => $this->arrayItem($user, 'authorizer_info.nick_name'),
            'nickname'           => $this->arrayItem($user, 'authorizer_info.nick_name'),
            'avatar'             => $this->arrayItem($user, 'authorizer_info.head_img'),
            'email'              => null,
            'authorizer_info'    => $this->arrayItem($user, 'authorization_info'),
            'qrcode_url'         => $this->arrayItem($user, 'qrcode_url'),
            'authorization_info' => $this->arrayItem($user, 'authorization_info'),
        ]);
    }

    /**
     * Get the token URL for the provider.
     *
     * @return string
     */
    protected function getTokenUrl()
    {
        return null;
    }

    /**
     * Get the code from the request.
     *
     * @return string
     */
    protected function getCode()
    {
        return $this->request->get('auth_code');
    }
}