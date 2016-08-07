<?php

namespace WechatOP\Foundation\ServiceProviders;

use Pimple\Container;
use Pimple\ServiceProviderInterface;
use WechatOP\Server\Server;

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
        $pimple['server'] = function ($pimple) {
            $server = new Server($pimple['config'], $pimple['request']);

            $server->debug($pimple['config']['debug']);

            return $server;
        };
    }
}