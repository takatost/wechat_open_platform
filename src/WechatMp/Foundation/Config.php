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

use EasyWeChat\Support\Collection;

/**
 * Class Config.
 */
class Config extends Collection
{
    /**
     * 授权类型,MANUAL 是手工授权，COMPONENT 是第三方组件授权
     */
    const AUTH_TYPE_MANUAL    = "MANUAL";
    const AUTH_TYPE_COMPONENT = "COMPONENT";

    /**
     * The collection data.
     *
     * @var array
     */
    protected $items = [

        /**
         * 账号基本信息，请从微信公众平台/开放平台获取
         */
        'app_id'  => '',                    // AppID
        'secret'  => '',                    // AppSecret
        'token'   => '',                    // Token
        'aes_key' => '',                    // EncodingAESKey，安全模式下请一定要填写！！！

        'auth_type' => self::AUTH_TYPE_MANUAL,
        'oauth'     => [
            'scopes'   => ['snsapi_userinfo'],
            'callback' => '',
        ],

        /**
         * 微信支付
         */
        'payment'   => [
            'merchant_id' => '',
            'key'         => '',
            'cert_path'   => '', // XXX: 绝对路径！！！！
            'key_path'    => '',      // XXX: 绝对路径！！！！
            'notify_url'  => '',
            // 'device_info'     => '',
            // 'sub_app_id'      => '',
            // 'sub_merchant_id' => '',
            // ...
        ],

        /**
         * 日志配置
         *
         * level: 日志级别, 可选为：
         *         debug/info/notice/warning/error/critical/alert/emergency
         * file：日志文件位置(绝对路径!!!)，要求可写权限
         */
//        'log'     => [
//            'level' => 'debug',
//            'file'  => '/tmp/wechat_op.log',
//        ],
    ];
}
