<?php
/*
 * This file is part of the takatost/wechat_open_platform.
 *
 * (c) takatost <takatost@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace WechatOP\WechatMp\Foundation\ServiceProviders;

use EasyWeChat\Encryption\Encryptor;
use EasyWeChat\Message\Text;
use EasyWeChat\Staff\Staff;
use Pimple\Container;
use Pimple\ServiceProviderInterface;
use WechatOP\WechatMp\Core\TestAccessToken;
use WechatOP\WechatMp\Server\Guard;

class ServerServiceProvider implements ServiceProviderInterface
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
        $pimple['encryptor'] = function ($pimple) {
            return new Encryptor(
                $pimple['open_platform_config']['app_id'],
                $pimple['open_platform_config']['token'],
                $pimple['open_platform_config']['aes_key']
            );
        };

        $pimple['server'] = function ($pimple) {
            $server = new Guard($pimple['open_platform_config']['token']);
            $server->setAppId($pimple['config']['app_id']);

            $server->debug($pimple['config']['debug']);

            $server->setEncryptor($pimple['encryptor']);

            $server->setTestMessageHandler($this->getTestMessageHandler($pimple));

            return $server;
        };
    }

    /**
     * @param Container $pimple
     * @return \Closure
     */
    protected function getTestMessageHandler(Container $pimple)
    {
        return function ($message) use ($pimple) {
            if (in_array($message->MsgType, ['text', 'image', 'voice'], false)) {
                if ($message->Content === 'TESTCOMPONENT_MSG_TYPE_TEXT') {
                    return $message->Content . '_callback';
                } else {
                    $queryAuthCode = ltrim($message->Content, 'QUERY_AUTH_CODE:');
                    $accessToken = new TestAccessToken($pimple['open_platform'], $queryAuthCode);
                    $staff = new Staff($accessToken);

                    $text = new Text(['content' => $queryAuthCode . '_from_api']);
                    $staff->message($text)->to($message->FromUserName)->send();

                    return null;
                }
            } elseif ($message->MsgType === 'event') {
                return $message['Event'] . 'from_callback';
            }
        };
    }
}