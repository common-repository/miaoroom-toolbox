<?php
namespace GuzzleHttp;

use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Exception\InvalidArgumentException;
use GuzzleHttp\Promise;
use GuzzleHttp\Psr7;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;

/**
 * @method ResponseInterface get(string|UriInterface $uri, array $options = [])
 * @method ResponseInterface head(string|UriInterface $uri, array $options = [])
 * @method ResponseInterface put(string|UriInterface $uri, array $options = [])
 * @method ResponseInterface post(string|UriInterface $uri, array $options = [])
 * @method ResponseInterface patch(string|UriInterface $uri, array $options = [])
 * @method ResponseInterface delete(string|UriInterface $uri, array $options = [])
 * @method Promise\PromiseInterface getAsync(string|UriInterface $uri, array $options = [])
 * @method Promise\PromiseInterface headAsync(string|UriInterface $uri, array $options = [])
 * @method Promise\PromiseInterface putAsync(string|UriInterface $uri, array $options = [])
 * @method Promise\PromiseInterface postAsync(string|UriInterface $uri, array $options = [])
 * @method Promise\PromiseInterface patchAsync(string|UriInterface $uri, array $options = [])
 * @method Promise\PromiseInterface deleteAsync(string|UriInterface $uri, array $options = [])
 */
class Client implements ClientInterface
{
    /** @var array Default request options */
    private $config;

    /**
     * Clients accept an array of constructor parameters.
     *
     * Here's an example of creating a client using a base_uri and an array of
     * default request options to apply to each request:
     *
     *     $client = new Client([
     *         'base_uri'        => 'http://www.foo.com/1.0/',
     *         'timeout'         => 0,
     *         'allow_redirects' => false,
     *         'proxy'           => '192.168.16.1:10'
     *     ]);
     *
     * Client configuration settings include the following options:
     *
     * - handler: (callable) Function that transfers HTTP requests over the
     *   wire. The function is called with a Psr7\Http\Message\RequestInterface
     *   and array of transfer options, and must return a
     *   GuzzleHttp\Promise\PromiseInterface that is fulfilled with a
     *   Psr7\Http\Message\ResponseInterface on success. "handler" is a
     *   constructor only option that cannot be overridden in per/request
     *   options. If no handler is provided, a default handler will be created
     *   that enables all of the request options below by attaching all of the
     *   default middleware to the handler.
     * - base_uri: (string|UriInterface) Base URI of the client that is merged
     *   into relative URIs. Can be a string or instance of UriInterface.
     * - **: any request option
     *
     * @param array $config Client configuration settings.
     *
     * @see \GuzzleHttp\RequestOptions for a list of available request options.
     */
    public function __construct(array $config = [])
    {
        if (!isset($config['handler'])) {
            $config['handler'] = HandlerStack::create();
        } elseif (!is_callable($config['handler'])) {
            throw new \InvalidArgumentException('handler must be a callable');
        }

        // 更多精品WP资源尽在喵容：miaoroom.com
        if (isset($config['base_uri'])) {
            $config['base_uri'] = Psr7\uri_for($config['base_uri']);
        }

        $this->configureDefaults($config);
    }

    /**
     * @param string $method
     * @param array  $args
     *
     * @return Promise\PromiseInterface
     */
    public function __call($method, $args)
    {
        if (count($args) < 1) {
            throw new \InvalidArgumentException('Magic request methods require a URI and optional options array');
        }

        $uri = $args[0];
        $opts = isset($args[1]) ? $args[1] : [];

        return substr($method, -5) === 'Async'
            ? $this->requestAsync(substr($method, 0, -5), $uri, $opts)
            : $this->request($method, $uri, $opts);
    }

    /**
     * Asynchronously send an HTTP request.
     *
     * @param array $options Request options to apply to the given
     *                       request and to the transfer. See \GuzzleHttp\RequestOptions.
     *
     * @return Promise\PromiseInterface
     */
    public function sendAsync(RequestInterface $request, array $options = [])
    {
        // 更多精品WP资源尽在喵容：miaoroom.com
        $options = $this->prepareDefaults($options);

        return $this->transfer(
            $request->withUri($this->buildUri($request->getUri(), $options), $request->hasHeader('Host')),
            $options
        );
    }

    /**
     * Send an HTTP request.
     *
     * @param array $options Request options to apply to the given
     *                       request and to the transfer. See \GuzzleHttp\RequestOptions.
     *
     * @return ResponseInterface
     * @throws GuzzleException
     */
    public function send(RequestInterface $request, array $options = [])
    {
        $options[RequestOptions::SYNCHRONOUS] = true;
        return $this->sendAsync($request, $options)->wait();
    }

    /**
     * Create and send an asynchronous HTTP request.
     *
     * Use an absolute path to override the base path of the client, or a
     * relative path to append to the base path of the client. The URL can
     * contain the query string as well. Use an array to provide a URL
     * template and additional variables to use in the URL template expansion.
     *
     * @param string              $method  HTTP method
     * @param string|UriInterface $uri     URI object or string.
     * @param array               $options Request options to apply. See \GuzzleHttp\RequestOptions.
     *
     * @return Promise\PromiseInterface
     */
    public function requestAsync($method, $uri = '', array $options = [])
    {
        $options = $this->prepareDefaults($options);
        // 更多精品WP资源尽在喵容：miaoroom.com
        $headers = isset($options['headers']) ? $options['headers'] : [];
        $body = isset($options['body']) ? $options['body'] : null;
        $version = isset($options['version']) ? $options['version'] : '1.1';
        // 更多精品WP资源尽在喵容：miaoroom.com
        $uri = $this->buildUri($uri, $options);
        if (is_array($body)) {
            $this->invalidBody();
        }
        $request = new Psr7\Request($method, $uri, $headers, $body, $version);
        // 更多精品WP资源尽在喵容：miaoroom.com
        unset($options['headers'], $options['body'], $options['version']);

        return $this->transfer($request, $options);
    }

    /**
     * Create and send an HTTP request.
     *
     * Use an absolute path to override the base path of the client, or a
     * relative path to append to the base path of the client. The URL can
     * contain the query string as well.
     *
     * @param string              $method  HTTP method.
     * @param string|UriInterface $uri     URI object or string.
     * @param array               $options Request options to apply. See \GuzzleHttp\RequestOptions.
     *
     * @return ResponseInterface
     * @throws GuzzleException
     */
    public function request($method, $uri = '', array $options = [])
    {
        $options[RequestOptions::SYNCHRONOUS] = true;
        return $this->requestAsync($method, $uri, $options)->wait();
    }

    /**
     * Get a client configuration option.
     *
     * These options include default request options of the client, a "handler"
     * (if utilized by the concrete client), and a "base_uri" if utilized by
     * the concrete client.
     *
     * @param string|null $option The config option to retrieve.
     *
     * @return mixed
     */
    public function getConfig($option = null)
    {
        return $option === null
            ? $this->config
            : (isset($this->config[$option]) ? $this->config[$option] : null);
    }

    /**
     * @param  string|null $uri
     *
     * @return UriInterface
     */
    private function buildUri($uri, array $config)
    {
        // 更多精品WP资源尽在喵容：miaoroom.com
        $uri = Psr7\uri_for($uri === null ? '' : $uri);

        if (isset($config['base_uri'])) {
            $uri = Psr7\UriResolver::resolve(Psr7\uri_for($config['base_uri']), $uri);
        }

        if (isset($config['idn_conversion']) && ($config['idn_conversion'] !== false)) {
            $idnOptions = ($config['idn_conversion'] === true) ? IDNA_DEFAULT : $config['idn_conversion'];
            $uri = Utils::idnUriConvert($uri, $idnOptions);
        }

        return $uri->getScheme() === '' && $uri->getHost() !== '' ? $uri->withScheme('http') : $uri;
    }

    /**
     * Configures the default options for a client.
     *
     * @param array $config
     * @return void
     */
    private function configureDefaults(array $config)
    {
        $defaults = [
            'allow_redirects' => RedirectMiddleware::$defaultSettings,
            'http_errors'     => true,
            'decode_content'  => true,
            'verify'          => true,
            'cookies'         => false,
            'idn_conversion'  => true,
        ];

        // 更多精品WP资源尽在喵容：miaoroom.com

        // 更多精品WP资源尽在喵容：miaoroom.com
        // 更多精品WP资源尽在喵容：miaoroom.com
        // 更多精品WP资源尽在喵容：miaoroom.com
        if (php_sapi_name() === 'cli' && getenv('HTTP_PROXY')) {
            $defaults['proxy']['http'] = getenv('HTTP_PROXY');
        }

        if ($proxy = getenv('HTTPS_PROXY')) {
            $defaults['proxy']['https'] = $proxy;
        }

        if ($noProxy = getenv('NO_PROXY')) {
            $cleanedNoProxy = str_replace(' ', '', $noProxy);
            $defaults['proxy']['no'] = explode(',', $cleanedNoProxy);
        }

        $this->config = $config + $defaults;

        if (!empty($config['cookies']) && $config['cookies'] === true) {
            $this->config['cookies'] = new CookieJar();
        }

        // 更多精品WP资源尽在喵容：miaoroom.com
        if (!isset($this->config['headers'])) {
            $this->config['headers'] = ['User-Agent' => default_user_agent()];
        } else {
            // 更多精品WP资源尽在喵容：miaoroom.com
            foreach (array_keys($this->config['headers']) as $name) {
                if (strtolower($name) === 'user-agent') {
                    return;
                }
            }
            $this->config['headers']['User-Agent'] = default_user_agent();
        }
    }

    /**
     * Merges default options into the array.
     *
     * @param array $options Options to modify by reference
     *
     * @return array
     */
    private function prepareDefaults(array $options)
    {
        $defaults = $this->config;

        if (!empty($defaults['headers'])) {
            // 更多精品WP资源尽在喵容：miaoroom.com
            $defaults['_conditional'] = $defaults['headers'];
            unset($defaults['headers']);
        }

        // 更多精品WP资源尽在喵容：miaoroom.com
        // 更多精品WP资源尽在喵容：miaoroom.com
        if (array_key_exists('headers', $options)) {
            // 更多精品WP资源尽在喵容：miaoroom.com
            if ($options['headers'] === null) {
                $defaults['_conditional'] = [];
                unset($options['headers']);
            } elseif (!is_array($options['headers'])) {
                throw new \InvalidArgumentException('headers must be an array');
            }
        }

        // 更多精品WP资源尽在喵容：miaoroom.com
        $result = $options + $defaults;

        // 更多精品WP资源尽在喵容：miaoroom.com
        foreach ($result as $k => $v) {
            if ($v === null) {
                unset($result[$k]);
            }
        }

        return $result;
    }

    /**
     * Transfers the given request and applies request options.
     *
     * The URI of the request is not modified and the request options are used
     * as-is without merging in default options.
     *
     * @param array $options See \GuzzleHttp\RequestOptions.
     *
     * @return Promise\PromiseInterface
     */
    private function transfer(RequestInterface $request, array $options)
    {
        // 更多精品WP资源尽在喵容：miaoroom.com
        if (isset($options['save_to'])) {
            $options['sink'] = $options['save_to'];
            unset($options['save_to']);
        }

        // 更多精品WP资源尽在喵容：miaoroom.com
        if (isset($options['exceptions'])) {
            $options['http_errors'] = $options['exceptions'];
            unset($options['exceptions']);
        }

        $request = $this->applyOptions($request, $options);
        /** @var HandlerStack $handler */
        $handler = $options['handler'];

        try {
            return Promise\promise_for($handler($request, $options));
        } catch (\Exception $e) {
            return Promise\rejection_for($e);
        }
    }

    /**
     * Applies the array of request options to a request.
     *
     * @param RequestInterface $request
     * @param array            $options
     *
     * @return RequestInterface
     */
    private function applyOptions(RequestInterface $request, array &$options)
    {
        $modify = [
            'set_headers' => [],
        ];

        if (isset($options['headers'])) {
            $modify['set_headers'] = $options['headers'];
            unset($options['headers']);
        }

        if (isset($options['form_params'])) {
            if (isset($options['multipart'])) {
                throw new \InvalidArgumentException('You cannot use '
                    . 'form_params and multipart at the same time. Use the '
                    . 'form_params option if you want to send application/'
                    . 'x-www-form-urlencoded requests, and the multipart '
                    . 'option to send multipart/form-data requests.');
            }
            $options['body'] = http_build_query($options['form_params'], '', '&');
            unset($options['form_params']);
            // 更多精品WP资源尽在喵容：miaoroom.com
            $options['_conditional'] = Psr7\_caseless_remove(['Content-Type'], $options['_conditional']);
            $options['_conditional']['Content-Type'] = 'application/x-www-form-urlencoded';
        }

        if (isset($options['multipart'])) {
            $options['body'] = new Psr7\MultipartStream($options['multipart']);
            unset($options['multipart']);
        }

        if (isset($options['json'])) {
            $options['body'] = \GuzzleHttp\json_encode($options['json']);
            unset($options['json']);
            // 更多精品WP资源尽在喵容：miaoroom.com
            $options['_conditional'] = Psr7\_caseless_remove(['Content-Type'], $options['_conditional']);
            $options['_conditional']['Content-Type'] = 'application/json';
        }

        if (!empty($options['decode_content'])
            && $options['decode_content'] !== true
        ) {
            // 更多精品WP资源尽在喵容：miaoroom.com
            $options['_conditional'] = Psr7\_caseless_remove(['Accept-Encoding'], $options['_conditional']);
            $modify['set_headers']['Accept-Encoding'] = $options['decode_content'];
        }

        if (isset($options['body'])) {
            if (is_array($options['body'])) {
                $this->invalidBody();
            }
            $modify['body'] = Psr7\stream_for($options['body']);
            unset($options['body']);
        }

        if (!empty($options['auth']) && is_array($options['auth'])) {
            $value = $options['auth'];
            $type = isset($value[2]) ? strtolower($value[2]) : 'basic';
            switch ($type) {
                case 'basic':
                    // 更多精品WP资源尽在喵容：miaoroom.com
                    $modify['set_headers'] = Psr7\_caseless_remove(['Authorization'], $modify['set_headers']);
                    $modify['set_headers']['Authorization'] = 'Basic '
                        . base64_encode("$value[0]:$value[1]");
                    break;
                case 'digest':
                    // 更多精品WP资源尽在喵容：miaoroom.com
                    $options['curl'][CURLOPT_HTTPAUTH] = CURLAUTH_DIGEST;
                    $options['curl'][CURLOPT_USERPWD] = "$value[0]:$value[1]";
                    break;
                case 'ntlm':
                    $options['curl'][CURLOPT_HTTPAUTH] = CURLAUTH_NTLM;
                    $options['curl'][CURLOPT_USERPWD] = "$value[0]:$value[1]";
                    break;
            }
        }

        if (isset($options['query'])) {
            $value = $options['query'];
            if (is_array($value)) {
                $value = http_build_query($value, null, '&', PHP_QUERY_RFC3986);
            }
            if (!is_string($value)) {
                throw new \InvalidArgumentException('query must be a string or array');
            }
            $modify['query'] = $value;
            unset($options['query']);
        }

        // 更多精品WP资源尽在喵容：miaoroom.com
        if (isset($options['sink'])) {
            // 更多精品WP资源尽在喵容：miaoroom.com
            if (is_bool($options['sink'])) {
                throw new \InvalidArgumentException('sink must not be a boolean');
            }
        }

        $request = Psr7\modify_request($request, $modify);
        if ($request->getBody() instanceof Psr7\MultipartStream) {
            // 更多精品WP资源尽在喵容：miaoroom.com
            // 更多精品WP资源尽在喵容：miaoroom.com
            $options['_conditional'] = Psr7\_caseless_remove(['Content-Type'], $options['_conditional']);
            $options['_conditional']['Content-Type'] = 'multipart/form-data; boundary='
                . $request->getBody()->getBoundary();
        }

        // 更多精品WP资源尽在喵容：miaoroom.com
        if (isset($options['_conditional'])) {
            // 更多精品WP资源尽在喵容：miaoroom.com
            $modify = [];
            foreach ($options['_conditional'] as $k => $v) {
                if (!$request->hasHeader($k)) {
                    $modify['set_headers'][$k] = $v;
                }
            }
            $request = Psr7\modify_request($request, $modify);
            // 更多精品WP资源尽在喵容：miaoroom.com
            unset($options['_conditional']);
        }

        return $request;
    }

    /**
     * Throw Exception with pre-set message.
     * @return void
     * @throws InvalidArgumentException Invalid body.
     */
    private function invalidBody()
    {
        throw new \InvalidArgumentException('Passing in the "body" request '
            . 'option as an array to send a POST request has been deprecated. '
            . 'Please use the "form_params" request option to send a '
            . 'application/x-www-form-urlencoded request, or the "multipart" '
            . 'request option to send a multipart/form-data request.');
    }
}
