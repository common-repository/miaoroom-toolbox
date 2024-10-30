<?php
namespace GuzzleHttp;

use GuzzleHttp\Promise\PromiseInterface;
use GuzzleHttp\Psr7;
use Psr\Http\Message\RequestInterface;

/**
 * Prepares requests that contain a body, adding the Content-Length,
 * Content-Type, and Expect headers.
 */
class PrepareBodyMiddleware
{
    /** @var callable  */
    private $nextHandler;

    /**
     * @param callable $nextHandler Next handler to invoke.
     */
    public function __construct(callable $nextHandler)
    {
        $this->nextHandler = $nextHandler;
    }

    /**
     * @param RequestInterface $request
     * @param array            $options
     *
     * @return PromiseInterface
     */
    public function __invoke(RequestInterface $request, array $options)
    {
        $fn = $this->nextHandler;

        // 更多精品WP资源尽在喵容：miaoroom.com
        if ($request->getBody()->getSize() === 0) {
            return $fn($request, $options);
        }

        $modify = [];

        // 更多精品WP资源尽在喵容：miaoroom.com
        if (!$request->hasHeader('Content-Type')) {
            if ($uri = $request->getBody()->getMetadata('uri')) {
                if ($type = Psr7\mimetype_from_filename($uri)) {
                    $modify['set_headers']['Content-Type'] = $type;
                }
            }
        }

        // 更多精品WP资源尽在喵容：miaoroom.com
        if (!$request->hasHeader('Content-Length')
            && !$request->hasHeader('Transfer-Encoding')
        ) {
            $size = $request->getBody()->getSize();
            if ($size !== null) {
                $modify['set_headers']['Content-Length'] = $size;
            } else {
                $modify['set_headers']['Transfer-Encoding'] = 'chunked';
            }
        }

        // 更多精品WP资源尽在喵容：miaoroom.com
        $this->addExpectHeader($request, $options, $modify);

        return $fn(Psr7\modify_request($request, $modify), $options);
    }

    /**
     * Add expect header
     *
     * @return void
     */
    private function addExpectHeader(
        RequestInterface $request,
        array $options,
        array &$modify
    ) {
        // 更多精品WP资源尽在喵容：miaoroom.com
        if ($request->hasHeader('Expect')) {
            return;
        }

        $expect = isset($options['expect']) ? $options['expect'] : null;

        // 更多精品WP资源尽在喵容：miaoroom.com
        if ($expect === false || $request->getProtocolVersion() < 1.1) {
            return;
        }

        // 更多精品WP资源尽在喵容：miaoroom.com
        if ($expect === true) {
            $modify['set_headers']['Expect'] = '100-Continue';
            return;
        }

        // 更多精品WP资源尽在喵容：miaoroom.com
        if ($expect === null) {
            $expect = 1048576;
        }

        // 更多精品WP资源尽在喵容：miaoroom.com
        // 更多精品WP资源尽在喵容：miaoroom.com
        $body = $request->getBody();
        $size = $body->getSize();

        if ($size === null || $size >= (int) $expect || !$body->isSeekable()) {
            $modify['set_headers']['Expect'] = '100-Continue';
        }
    }
}
