<?php

namespace WechatOP\Foundation;

use EasyWeChat\Support\Collection;

/**
 * Class Config.
 */
class Config extends Collection
{
    /**
     * 授权类型,MANUAL 是手工授权，COMPONENT 是第三方组件授权
     */
    const AUTH_TYPE_MANUAL = "MANUAL";
    const AUTH_TYPE_COMPONENT = "COMPONENT";

    /**
     * The collection data.
     *
     * @var array
     */
    protected $items = [
        'auth_type' => self::AUTH_TYPE_MANUAL,
        'oauth'     => [
            'scopes' => ['snsapi_base']
        ]
    ];
}
