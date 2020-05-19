<?php
namespace PHPPackageX\net\http\client;

use PHPPackageX\net\http\client\contracts\HandlerInterface;
use PHPPackageX\net\http\client\strategy\Curl;
use PHPPackageX\net\http\client\strategy\Stream;
use PHPPackageX\net\http\message\factory\RequestFactory;
use PHPPackageX\net\http\message\factory\StreamFactory;
use PHPPackageX\net\media\application\Json;
use PHPPackageX\net\media\application\XWwwFormUrlencoded;
use PHPPackageX\net\media\MediaInterface;
use PHPPackagex\net\media\multipart\FormData;

use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\ResponseInterface;

class Client implements ClientInterface
{
    const STRATEGY_CURL   = Curl::class;
    const STRATEGY_STREAM = Stream::class;

    const MEDIA_APPLICATION_JSON     = Json::class;
    const MEDIA_APPLICATION_WWW_FORM = XWwwFormUrlencoded::class;
    const MEDIA_MULTIPART_FORM_DATA  = FormData::class;

    /**
     * @var HandlerInterface
     */
    protected $strategy;

    /**
     * @var RequestFactoryInterface
     */
    protected $requestFactory;

    /**
     * @var StreamFactoryInterface
     */
    protected $streamFactory;

    public function __construct(
        $strategy       = null,
        $requestFactory = null,
        $streamFactory  = null)
    {
        $this->strategy       = $strategy;
        $this->requestFactory = $requestFactory;
        $this->streamFactory  = $streamFactory;
    }

    /**
     * @param  $method
     * @param  $uri
     * @param  $headers
     * @param  $body
     * @param  string $version
     * @return RequestInterface
     */
    public function createRequest($method, $uri, $headers, $body, $version = '1.1'):RequestInterface
    {
        $request = $this->getRequestFactory()->createRequest($method, $uri);
        $request = $request->withProtocolVersion($version);

        foreach($headers as $headerName => $headerValue) {
            $request = $request->withAddedHeader($headerName, $headerValue);
        }

        if (!$body instanceof StreamInterface) {
            if (is_resource($body)) {
                $body = $this->getStreamFactory()->createStreamFromResource($body);
            } else if(is_array($body)) {
                list($mediaType, $content) = $body;
                /**
                 * @var $mediaType MediaInterface
                 */
                $mediaType = new $mediaType($content);
                $mediaType->setContent($content);
                $body = $this->getStreamFactory()->createStream((string)($mediaType));
                $request = $request->withAddedHeader('Content-Type', $mediaType->getContentType());
            } else  {
                $body = $this->getStreamFactory()->createStream($body);
            }
        }

        $request = $request->withBody($body);

        return $request;
    }

    /**
     * @param  RequestInterface  $request
     * @return ResponseInterface
     */
    public function sendRequest(RequestInterface $request): ResponseInterface
    {
        if (empty($this->strategy)) {
            $strategy = $this->selectStrategy();
        } else if(is_string($this->strategy)) {
            $strategy = new ($this->strategy);
        }

        if (!$strategy instanceof HandlerInterface) {
            //TODO 策略必须实现 StrategyInterface
        }

        if ($strategy->contextIsEnable(true)) {
            //TODO 上下文不支持指定策略
        }

        return $strategy->sendRequest($request);
    }

    /**
     * @return HandlerInterface
     */
    private function selectStrategy():HandlerInterface
    {
        $strategyList = [self::STRATEGY_CURL, self::STRATEGY_STREAM];

        foreach ($strategyList as $strategyClass) {
            /**
             * @var $strategy HandlerInterface
             */
            $strategy = new $strategyClass;
            if ($strategy->contextIsEnable(false)) {
                return $strategy;
            }
        }

        //TODO 环境不支持没有可用策略
    }

    /**
     * @return RequestFactoryInterface
     */
    public function getRequestFactory():RequestFactoryInterface
    {
        if ($this->requestFactory instanceof RequestFactoryInterface) {
            return $this->requestFactory;
        }

        if (is_string($this->requestFactory) && !empty($this->requestFactory)) {
            $this->requestFactory = new ($this->requestFactory);
            if ($this->requestFactory instanceof RequestFactoryInterface) {
                throw new \InvalidArgumentException('requestFactory Class must be implements '.RequestFactoryInterface::class);
            }
        } else {
            $this->requestFactory = new RequestFactory();
        }

        return $this->requestFactory;
    }

    /**
     * @return StreamFactoryInterface
     */
    public function getStreamFactory():StreamFactoryInterface
    {
        if ($this->streamFactory instanceof StreamFactoryInterface) {
            return $this->streamFactory;
        }

        if (is_string($this->streamFactory) && !empty($this->streamFactory)) {
            $this->streamFactory = new ($this->streamFactory);
            if ($this->streamFactory instanceof StreamFactoryInterface) {
                throw new \InvalidArgumentException('streamFactory Class must be implements '.StreamFactoryInterface::class);
            }
        } else {
            $this->streamFactory = new StreamFactory();
        }

        return $this->streamFactory;
    }
}