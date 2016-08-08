<?php

namespace WechatOP\Foundation;

use EasyWeChat\Support\Collection;

/**
 * Class Config.
 */
class Config extends Collection
{
    /**
     * The collection data.
     *
     * @var array
     */
    protected $items = [
        /**
         * Debug 模式，bool 值：true/false
         *
         * 当值为 false 时，所有的日志都不会记录
         */
        'debug'   => false,

        /**
         * 日志配置
         *
         * level: 日志级别, 可选为：
         *         debug/info/notice/warning/error/critical/alert/emergency
         * file：日志文件位置(绝对路径!!!)，要求可写权限
         */
        'log'     => [
            'level' => 'debug',
            'file'  => '/tmp/wechat_op.log',
        ],

        /**
         * 账号基本信息，请从微信公众平台/开放平台获取
         */
        'app_id'  => '',                    // AppID
        'secret'  => '',                    // AppSecret
        'token'   => '',                    // Token
        'aes_key' => '',                    // EncodingAESKey，安全模式下请一定要填写！！！

        // 授权
        'oauth'   => [
            'callback' => '',
        ],

        // 缓存
        'cache'   => [
            'driver' => 'redis',    // redis, filesystem
//            'dir' => ''
        ],

        /**
         * Guzzle 全局设置
         *
         * 更多请参考： http://docs.guzzlephp.org/en/latest/request-options.html
         */
        'guzzle'  => [
            'timeout' => 5.0, // 超时时间（秒）
            'verify'  => true, // 关掉 SSL 认证（强烈不建议！！！）
        ]
    ];
}
