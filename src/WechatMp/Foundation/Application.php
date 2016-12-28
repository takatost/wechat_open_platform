<?php
/*
 * This file is part of the takatost/wechat_open_platform.
 *
 * (c) takatost <takatost@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace WechatOP\WechatMp\Foundation;

use Doctrine\Common\Cache\Cache as CacheInterface;
use Doctrine\Common\Cache\FilesystemCache;
use EasyWeChat\Core\AccessToken;
use EasyWeChat\Core\Http;
use EasyWeChat\Foundation\Application as EasyWechatApplication;
use EasyWeChat\Support\Log;
use Monolog\Handler\NullHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Symfony\Component\HttpFoundation\Request;
use WechatOP\Foundation\Config as OpConfig;
use WechatOP\OpenPlatform\OpenPlatform;
use WechatOP\WechatMp\Core\ComponentAccessToken;


class Application extends EasyWechatApplication
{
    /**
     * @var array
     */
    private $defaultProviders;

    /**
     * Application constructor.
     *
     * @param OpenPlatform $openPlatform
     * @param OpConfig     $openPlatformConfig
     * @param array        $config
     */
    public function __construct(OpenPlatform $openPlatform, OpConfig $openPlatformConfig, $config)
    {
        $this['config'] = function () use ($config) {
            return new Config($config);
        };

        $this['open_platform'] = $openPlatform;
        $this['open_platform_config'] = $openPlatformConfig;

        if ($this['config']['debug']) {
            error_reporting(E_ALL);
        }

        $this->defaultProviders = $this->providers;

        $this->registerProviders();
        $this->registerBase();
        $this->initializeLogger();

        Http::setDefaultOptions($this['config']->get('guzzle', ['timeout' => 5.0]));

        foreach (['app_id', 'secret'] as $key) {
            !isset($config[$key]) || $config[$key] = '***'.substr($config[$key], -5);
        }

        Log::debug('Current config:', $config);
    }

    /**
     * Register providers.
     */
    private function registerProviders()
    {
        $this->providers = $this->defaultProviders;

        $providers = [];
        foreach($this->providers as $provider) {
            if ($provider === 'EasyWeChat\Foundation\ServiceProviders\OpenPlatformServiceProvider') {
                continue;
            }

            $providers[] = $provider;
        }

        $this->providers = $providers;

        if ($this['config']['auth_type'] === Config::AUTH_TYPE_COMPONENT) {
            $providers = [];
            foreach($this->providers as $provider) {
                if ($provider === 'EasyWeChat\Foundation\ServiceProviders\ServerServiceProvider'
                    || $provider === 'EasyWeChat\Foundation\ServiceProviders\OAuthServiceProvider') {
                    continue;
                }

                $providers[] = $provider;
            }

            $this->providers = $providers;

            array_push($this->providers, ServiceProviders\OAuthServiceProvider::class);
            array_push($this->providers, ServiceProviders\ServerServiceProvider::class);
        }

        foreach ($this->providers as $provider) {
            $this->register(new $provider());
        }
    }

    /**
     * Register basic providers.
     */
    private function registerBase()
    {
        $this['request'] = function () {
            return Request::createFromGlobals();
        };

        if (!empty($this['config']['cache']) && $this['config']['cache'] instanceof CacheInterface) {
            $this['cache'] = $this['config']['cache'];
        } else {
            $this['cache'] = function () {
                return new FilesystemCache(sys_get_temp_dir());
            };
        }

        $this['access_token'] = $this->getAccessToken();
    }

    /**
     * Initialize logger.
     */
    private function initializeLogger()
    {
        if (Log::hasLogger()) {
            return;
        }

        $logger = new Logger('wechat_op');

        if (!$this['config']['debug'] || defined('PHPUNIT_RUNNING')) {
            $logger->pushHandler(new NullHandler());
        } elseif ($logFile = $this['config']['log.file']) {
            $logger->pushHandler(new StreamHandler($logFile, $this['config']->get('log.level', Logger::WARNING)));
        }

        Log::setLogger($logger);
    }

    /**
     * @return AccessToken
     */
    protected function getAccessToken()
    {
        if ($this['config']['auth_type'] === Config::AUTH_TYPE_COMPONENT) {
            $accessToken = new ComponentAccessToken(
                $this['config']['app_id'],
                $this['open_platform'],
                $this['config']['component_refresh_token']
            );
        } else {
            $accessToken = new AccessToken(
                $this['config']['app_id'],
                $this['config']['secret'],
                $this['cache']
            );
        }

        return $accessToken;
    }
}