<?php
namespace WechatOP\Server;

use EasyWeChat\Core\Exceptions\InvalidArgumentException;
use EasyWeChat\Encryption\Encryptor;
use EasyWeChat\Server\BadRequestException;
use EasyWeChat\Support\Collection;
use EasyWeChat\Support\Log;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use WechatOP\Foundation\Config;

class Server
{
    /**
     * Empty string.
     */
    const SUCCESS_EMPTY_RESPONSE = 'success';
    const FAILED_EMPTY_RESPONSE  = 'failed';

    /**
     * @var Request
     */
    protected $request;

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var string|callable
     */
    protected $messageHandler;

    /**
     * @var array
     */
    protected $messageTypeMapping = [
        'component_verify_ticket' => 2,
        'authorized'              => 4,
        'unauthorized'            => 8,
        'updateauthorized'        => 16
    ];

    /**
     * @var bool
     */
    protected $debug = false;

    /**
     * Constructor.
     *
     * @param Config       $config
     * @param Request|null $request
     */
    public function __construct(Config $config, Request $request = null)
    {
        $this->config = $config;
        $this->request = $request ?: Request::createFromGlobals();
    }

    /**
     * Enable/Disable debug mode.
     *
     * @param bool $debug
     *
     * @return $this
     */
    public function debug($debug = true)
    {
        $this->debug = $debug;

        return $this;
    }

    /**
     * Handle and return response.
     *
     * @return Response
     *
     * @throws BadRequestException
     */
    public function serve()
    {
        Log::debug('Request received:', [
            'Method'   => $this->request->getMethod(),
            'URI'      => $this->request->getRequestUri(),
            'Query'    => $this->request->getQueryString(),
            'Protocal' => $this->request->server->get('SERVER_PROTOCOL'),
            'Content'  => $this->request->getContent(),
        ]);

        $result = $this->handleRequest();

        $response = $this->buildResponse($result['response']);

        Log::debug('Server response created:', compact('response'));

        return new Response($response);
    }

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
        $response = $this->handleMessage($message);

        return [
            'message'  => $message,
            'response' => $response,
        ];
    }

    /**
     * Get request message.
     *
     * @return Collection
     */
    public function getMessage()
    {
        $message = $this->parseMessageFromRequest($this->request->getContent(false));

        if ($message->count() == 0) {
            throw new BadRequestException('Invalid request.');
        }

        return $message;
    }

    /**
     * Handle message.
     *
     * @param Collection $message
     *
     * @return mixed
     */
    protected function handleMessage($message)
    {
        $handler = $this->messageHandler;

        if (!is_callable($handler)) {
            Log::debug('No handler enabled.');

            return null;
        }

        Log::debug('Message detail:', $message);

        $type = $this->messageTypeMapping[ $message->get('InfoType') ];

        $response = null;

        if ($type) {
            $response = call_user_func_array($handler, [$message]);
        }

        return $response;
    }

    /**
     * Parse message array from raw php input.
     *
     * @param string|resource $content
     *
     * @throws \EasyWeChat\Core\Exceptions\RuntimeException
     * @throws \EasyWeChat\Encryption\EncryptionException
     *
     * @return Collection
     */
    protected function parseMessageFromRequest($content)
    {
        $content = (string)$content;

        $encryptor = new Encryptor(
            $this->config->get('app_id'),
            $this->config->get('token'),
            $this->config->get('encoding_aes_key')
        );

        $message = $encryptor->decryptMsg(
            $this->request->get('msg_signature'),
            $this->request->get('nonce'),
            $this->request->get('timestamp'),
            $content
        );

        return new Collection($message);
    }

    /**
     * Add a event listener.
     *
     * @param callable $callback
     *
     * @return Server
     *
     * @throws InvalidArgumentException
     */
    public function setMessageHandler($callback = null)
    {
        if (!is_callable($callback)) {
            throw new InvalidArgumentException('Argument #2 is not callable.');
        }

        $this->messageHandler = $callback;

        return $this;
    }

    /**
     * Return the message listener.
     *
     * @return string
     */
    public function getMessageHandler()
    {
        return $this->messageHandler;
    }

    /**
     * Build response.
     *
     * @param mixed $message
     *
     * @return string
     *
     * @throws \EasyWeChat\Core\Exceptions\InvalidArgumentException
     */
    protected function buildResponse($message)
    {
        if (empty($message) || $message === self::SUCCESS_EMPTY_RESPONSE) {
            return self::SUCCESS_EMPTY_RESPONSE;
        }

        return self::FAILED_EMPTY_RESPONSE;
    }
}