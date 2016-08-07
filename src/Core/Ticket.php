<?php
/**
 * Created by PhpStorm.
 * User: JohnWang <takato@vip.qq.com>
 * Date: 2016/8/7
 * Time: 22:36
 */

namespace WechatOP\Core;


use Doctrine\Common\Cache\Cache;
use Doctrine\Common\Cache\FilesystemCache;

class Ticket
{
    /**
     * App ID.
     *
     * @var string
     */
    protected $appId;

    /**
     * Cache key prefix.
     *
     * @var string
     */
    protected $prefix = 'wechat_op.common.ticket.';

    /**
     * Cache.
     *
     * @var Cache
     */
    protected $cache;

    /**
     * Constructor.
     *
     * @param string                       $appId
     * @param string                       $secret
     * @param \Doctrine\Common\Cache\Cache $cache
     */
    public function __construct($appId, Cache $cache = null)
    {
        $this->appId = $appId;
        $this->cache = $cache;
    }

    /**
     * @return mixed
     */
    public function getTicket()
    {
        return $this->getCache()->fetch($this->prefix . $this->appId);
    }

    /**
     * @param $ticket
     * @return bool
     */
    public function setTicket($ticket)
    {
        return $this->getCache()->save($this->prefix . $this->appId, $ticket);
    }

    /**
     * Return the app id.
     *
     * @return string
     */
    public function getAppId()
    {
        return $this->appId;
    }

    /**
     * Set cache instance.
     *
     * @param \Doctrine\Common\Cache\Cache $cache
     *
     * @return Ticket
     */
    public function setCache(Cache $cache)
    {
        $this->cache = $cache;

        return $this;
    }

    /**
     * Return the cache manager.
     *
     * @return \Doctrine\Common\Cache\Cache
     */
    public function getCache()
    {
        return $this->cache ?: $this->cache = new FilesystemCache(sys_get_temp_dir());
    }
}