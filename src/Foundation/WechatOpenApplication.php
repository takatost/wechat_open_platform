<?php
/*
 * This file is part of the takatost/wechat_open_platform.
 *
 * (c) takatost <takatost@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace WechatOP\Foundation;

use Doctrine\Common\Cache\Cache as CacheInterface;
use Doctrine\Common\Cache\FilesystemCache;
use Doctrine\Common\Cache\RedisCache;
use EasyWeChat\Core\Http;
use EasyWeChat\Support\Collection;
use EasyWeChat\Support\Log;
use Monolog\Handler\NullHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Pimple\Container;
use Symfony\Component\HttpFoundation\Request;
use WechatOP\Core\AccessToken;
use WechatOP\Core\Ticket;
use WechatOP\OpenPlatform\OpenPlatform;
use WechatOP\WechatMp\Foundation\Application;

class WechatOpenApplication extends Container
{
    /**
     * Service Providers.
     *
     * @var array
     */
    protected $providers = [
        ServiceProviders\ServerServiceProvider::class,
        ServiceProviders\OAuthServiceProvider::class
    ];

    /**
     * Application constructor.
     *
     * @param array $config
     */
    public function __construct(Config $config)
    {
        parent::__construct();

        $this['config'] = function () use ($config) {
            return new Config($config);
        };

        if ($this['config']['debug']) {
            error_reporting(E_ALL);
        }

        $this['apps'] = new Collection();

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
     * Get Wechat App
     *
     * @param $appConfig
     * @return Application
     */
    public function app($appConfig)
    {
        $appId = $appConfig['app_id'];
        if ($this['apps']->has($appId)) {
            return $this['apps']->get($appId);
        } else {
            $appConfig = array_merge(
                $this['config']->only(['debug', 'log', 'guzzle']),
                $appConfig
            );

            $appConfig['cache'] = $this['config']['cache'];

            $app = new Application($this['open_platform'], $this['config'], $appConfig);
            $this['apps']->set($appId, $app);
            return $app;
        }
    }

    /**
     * Save Ticket
     *
     * @param $ticket
     * @return bool
     */
    public function saveTicket($ticket)
    {
        return $this['ticket']->setTicket($ticket);
    }

    /**
     * Add a provider.
     *
     * @param string $provider
     *
     * @return WechatOpenApplication
     */
    public function addProvider($provider)
    {
        array_push($this->providers, $provider);

        return $this;
    }

    /**
     * Set providers.
     *
     * @param array $providers
     */
    public function setProviders(array $providers)
    {
        $this->providers = [];

        foreach ($providers as $provider) {
            $this->addProvider($provider);
        }
    }

    /**
     * Return all providers.
     *
     * @return array
     */
    public function getProviders()
    {
        return $this->providers;
    }

    /**
     * Magic get access.
     *
     * @param string $id
     *
     * @return mixed
     */
    public function __get($id)
    {
        return $this->offsetGet($id);
    }

    /**
     * Magic set access.
     *
     * @param string $id
     * @param mixed  $value
     */
    public function __set($id, $value)
    {
        $this->offsetSet($id, $value);
    }

    /**
     * Magic isset access.
     *
     * @param $id
     * @return bool
     */
    public function __isset($id)
    {
        return $this->offsetExists($id);
    }

    /**
     * Register providers.
     */
    private function registerProviders()
    {
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
                return $this->registerCache(
                    is_array($this['config']['cache']) ? $this['config']['cache'] : ['driver' => null]
                );
            };
        }

        $this['ticket'] = function () {
            return new Ticket(
                $this['config']['app_id'],
                $this['cache']
            );
        };

        $this['access_token'] = function () {
            return new AccessToken(
                $this['config'],
                $this['ticket'],
                $this['cache']
            );
        };

        $this['open_platform'] = function () {
            return new OpenPlatform(
                $this['access_token']
            );
        };
    }

    /**
     * Register Cache Driver
     *
     * @param $cacheConfig
     * @return CacheInterface
     */
    protected function registerCache($cacheConfig)
    {
        switch ($cacheConfig['driver']) {
            case 'redis':
                $cacheDriver = new RedisCache();
                break;
            case 'filesystem':
                $cacheDriver = new FilesystemCache($cacheConfig['dir']);
                break;
            default:
                $cacheDriver = new FilesystemCache(sys_get_temp_dir());
        }

        return $cacheDriver;
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
}