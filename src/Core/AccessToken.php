<?php
/*
 * This file is part of the takatost/wechat_open_platform.
 *
 * (c) takatost <takatost@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace WechatOP\Core;


use Doctrine\Common\Cache\Cache;
use EasyWeChat\Core\AccessToken as BaseAccessToken;
use EasyWeChat\Core\Exceptions\HttpException;
use WechatOP\Foundation\Config;

class AccessToken extends BaseAccessToken
{
    /**
     * Query name.
     *
     * @var string
     */
    protected $queryName = 'component_access_token';

    /**
     * Cache key prefix.
     *
     * @var string
     */
    protected $prefix = 'wechat_op.common.access_token.';

    /**
     * @var Ticket
     */
    protected $ticket;

    // API
    const API_TOKEN_GET = 'https://api.weixin.qq.com/cgi-bin/component/api_component_token';


    public function __construct(Config $config, Ticket $ticket, Cache $cache = null)
    {
        parent::__construct($config->get('app_id'), $config->get('secret'), $cache);

        $this->ticket = $ticket;
    }

    /**
     * Get the access token from WeChat server.
     *
     * @throws \EasyWeChat\Core\Exceptions\HttpException
     *
     * @return array|bool
     */
    public function getTokenFromServer()
    {
        $params = [
            'component_appid' => $this->appId,
            'component_appsecret' => $this->secret,
            'component_verify_ticket' => $this->ticket->getTicket(),
        ];

        $http = $this->getHttp();

        $token = $http->parseJSON($http->get(self::API_TOKEN_GET, $params));

        if (empty($token['component_access_token'])) {
            throw new HttpException('Request AccessToken fail. response: '.json_encode($token, JSON_UNESCAPED_UNICODE));
        }

        return $token;
    }
}