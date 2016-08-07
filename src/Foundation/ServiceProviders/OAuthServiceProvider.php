<?php
/**
 * Created by PhpStorm.
 * User: JohnWang <takato@vip.qq.com>
 * Date: 2016/8/7
 * Time: 21:35
 */

namespace WechatOP\Foundation\ServiceProviders;

use Pimple\Container;
use Pimple\ServiceProviderInterface;
use WechatOP\Socialite\Providers\WechatOpenPlatformServiceProvider;

class OAuthServiceProvider implements ServiceProviderInterface
{
    /**
     * Registers services on the given container.
     *
     * This method should only be used to configure services and parameters.
     * It should not get services.
     *
     * @param Container $pimple A container instance
     */
    public function register(Container $pimple)
    {
        $pimple['oauth'] = function ($pimple) {
            $callback = $this->prepareCallbackUrl($pimple);
            $pimple['config']['redirect_url'] = $callback;
            $socialite = new WechatOpenPlatformServiceProvider(
                $pimple['request'],
                $pimple['open_platform'],
                $pimple['config']
            );

            return $socialite;
        };
    }

    /**
     * Prepare the OAuth callback url for wechat.
     *
     * @param Container $pimple
     *
     * @return string
     */
    private function prepareCallbackUrl($pimple)
    {
        $callback = $pimple['config']->get('oauth.callback');
        if (0 === stripos($callback, 'http')) {
            return $callback;
        }
        $baseUrl = $pimple['request']->getSchemeAndHttpHost();

        return $baseUrl.'/'.ltrim($callback, '/');
    }
}