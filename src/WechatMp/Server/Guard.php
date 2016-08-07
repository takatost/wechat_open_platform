<?php

namespace WechatOP\WechatMp\Server;

use EasyWeChat\Core\Exceptions\InvalidArgumentException;
use EasyWeChat\Server\Guard as BaseGuard;
use EasyWeChat\Support\Collection;
use EasyWeChat\Support\Log;

/**
 * Class Guard.
 */
class Guard extends BaseGuard
{
    const TEST_APP_ID = 'wx570bc396a51b8ff8';

    /**
     * @var string|callable
     */
    protected $testMessageHandler;

    /**
     * Handle request.
     *
     * @return array
     *
     * @throws \EasyWeChat\Core\Exceptions\RuntimeException
     * @throws \EasyWeChat\Server\BadRequestException
     */
    protected function handleRequest()
    {
        $message = $this->getMessage();

        if ($message['FromUserName'] == self::TEST_APP_ID) {
            $response = $this->handleTestMessage($message);
        } else {
            $response = $this->handleMessage($message);
        }

        return [
            'to' => $message['FromUserName'],
            'from' => $message['ToUserName'],
            'response' => $response,
        ];
    }

    /**
     * @param $message
     * @return mixed|null
     */
    protected function handleTestMessage($message)
    {
        $handler = $this->testMessageHandler;

        if (!is_callable($handler)) {
            Log::debug('No handler enabled.');

            return null;
        }

        Log::debug('Test Message detail:', $message);

        $message = new Collection($message);

        return call_user_func_array($handler, [$message]);
    }

    /**
     * Add a event listener.
     *
     * @param callable $callback
     *
     * @return Guard
     *
     * @throws InvalidArgumentException
     */
    public function setTestMessageHandler($callback = null)
    {
        if (!is_callable($callback)) {
            throw new InvalidArgumentException('Argument #2 is not callable.');
        }

        $this->testMessageHandler = $callback;

        return $this;
    }
}
