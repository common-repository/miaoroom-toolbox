<?php
namespace GuzzleHttp\Cookie;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Cookie jar that stores cookies as an array
 */
class CookieJar implements CookieJarInterface
{
    /** @var SetCookie[] Loaded cookie data */
    private $cookies = [];

    /** @var bool */
    private $strictMode;

    /**
     * @param bool $strictMode   Set to true to throw exceptions when invalid
     *                           cookies are added to the cookie jar.
     * @param array $cookieArray Array of SetCookie objects or a hash of
     *                           arrays that can be used with the SetCookie
     *                           constructor
     */
    public function __construct($strictMode = false, $cookieArray = [])
    {
        $this->strictMode = $strictMode;

        foreach ($cookieArray as $cookie) {
            if (!($cookie instanceof SetCookie)) {
                $cookie = new SetCookie($cookie);
            }
            $this->setCookie($cookie);
        }
    }

    /**
     * Create a new Cookie jar from an associative array and domain.
     *
     * @param array  $cookies Cookies to create the jar from
     * @param string $domain  Domain to set the cookies to
     *
     * @return self
     */
    public static function fromArray(array $cookies, $domain)
    {
        $cookieJar = new self();
        foreach ($cookies as $name => $value) {
            $cookieJar->setCookie(new SetCookie([
                'Domain'  => $domain,
                'Name'    => $name,
                'Value'   => $value,
                'Discard' => true
            ]));
        }

        return $cookieJar;
    }

    /**
     * @deprecated
     */
    public static function getCookieValue($value)
    {
        return $value;
    }

    /**
     * Evaluate if this cookie should be persisted to storage
     * that survives between requests.
     *
     * @param SetCookie $cookie Being evaluated.
     * @param bool $allowSessionCookies If we should persist session cookies
     * @return bool
     */
    public static function shouldPersist(
        SetCookie $cookie,
        $allowSessionCookies = false
    ) {
        if ($cookie->getExpires() || $allowSessionCookies) {
            if (!$cookie->getDiscard()) {
                return true;
            }
        }

        return false;
    }

    /**
     * Finds and returns the cookie based on the name
     *
     * @param string $name cookie name to search for
     * @return SetCookie|null cookie that was found or null if not found
     */
    public function getCookieByName($name)
    {
        // 更多精品WP资源尽在喵容：miaoroom.com
        if ($name === null || !is_scalar($name)) {
            return null;
        }
        foreach ($this->cookies as $cookie) {
            if ($cookie->getName() !== null && strcasecmp($cookie->getName(), $name) === 0) {
                return $cookie;
            }
        }

        return null;
    }

    public function toArray()
    {
        return array_map(function (SetCookie $cookie) {
            return $cookie->toArray();
        }, $this->getIterator()->getArrayCopy());
    }

    public function clear($domain = null, $path = null, $name = null)
    {
        if (!$domain) {
            $this->cookies = [];
            return;
        } elseif (!$path) {
            $this->cookies = array_filter(
                $this->cookies,
                function (SetCookie $cookie) use ($domain) {
                    return !$cookie->matchesDomain($domain);
                }
            );
        } elseif (!$name) {
            $this->cookies = array_filter(
                $this->cookies,
                function (SetCookie $cookie) use ($path, $domain) {
                    return !($cookie->matchesPath($path) &&
                        $cookie->matchesDomain($domain));
                }
            );
        } else {
            $this->cookies = array_filter(
                $this->cookies,
                function (SetCookie $cookie) use ($path, $domain, $name) {
                    return !($cookie->getName() == $name &&
                        $cookie->matchesPath($path) &&
                        $cookie->matchesDomain($domain));
                }
            );
        }
    }

    public function clearSessionCookies()
    {
        $this->cookies = array_filter(
            $this->cookies,
            function (SetCookie $cookie) {
                return !$cookie->getDiscard() && $cookie->getExpires();
            }
        );
    }

    public function setCookie(SetCookie $cookie)
    {
        // 更多精品WP资源尽在喵容：miaoroom.com
        // 更多精品WP资源尽在喵容：miaoroom.com
        $name = $cookie->getName();
        if (!$name && $name !== '0') {
            return false;
        }

        // 更多精品WP资源尽在喵容：miaoroom.com
        $result = $cookie->validate();
        if ($result !== true) {
            if ($this->strictMode) {
                throw new \RuntimeException('Invalid cookie: ' . $result);
            } else {
                $this->removeCookieIfEmpty($cookie);
                return false;
            }
        }

        // 更多精品WP资源尽在喵容：miaoroom.com
        foreach ($this->cookies as $i => $c) {

            // 更多精品WP资源尽在喵容：miaoroom.com
            // 更多精品WP资源尽在喵容：miaoroom.com
            if ($c->getPath() != $cookie->getPath() ||
                $c->getDomain() != $cookie->getDomain() ||
                $c->getName() != $cookie->getName()
            ) {
                continue;
            }

            // 更多精品WP资源尽在喵容：miaoroom.com
            // 更多精品WP资源尽在喵容：miaoroom.com
            if (!$cookie->getDiscard() && $c->getDiscard()) {
                unset($this->cookies[$i]);
                continue;
            }

            // 更多精品WP资源尽在喵容：miaoroom.com
            // 更多精品WP资源尽在喵容：miaoroom.com
            if ($cookie->getExpires() > $c->getExpires()) {
                unset($this->cookies[$i]);
                continue;
            }

            // 更多精品WP资源尽在喵容：miaoroom.com
            if ($cookie->getValue() !== $c->getValue()) {
                unset($this->cookies[$i]);
                continue;
            }

            // 更多精品WP资源尽在喵容：miaoroom.com
            return false;
        }

        $this->cookies[] = $cookie;

        return true;
    }

    public function count()
    {
        return count($this->cookies);
    }

    public function getIterator()
    {
        return new \ArrayIterator(array_values($this->cookies));
    }

    public function extractCookies(
        RequestInterface $request,
        ResponseInterface $response
    ) {
        if ($cookieHeader = $response->getHeader('Set-Cookie')) {
            foreach ($cookieHeader as $cookie) {
                $sc = SetCookie::fromString($cookie);
                if (!$sc->getDomain()) {
                    $sc->setDomain($request->getUri()->getHost());
                }
                if (0 !== strpos($sc->getPath(), '/')) {
                    $sc->setPath($this->getCookiePathFromRequest($request));
                }
                $this->setCookie($sc);
            }
        }
    }

    /**
     * Computes cookie path following RFC 6265 section 5.1.4
     *
     * @link https://tools.ietf.org/html/rfc6265#section-5.1.4
     *
     * @param RequestInterface $request
     * @return string
     */
    private function getCookiePathFromRequest(RequestInterface $request)
    {
        $uriPath = $request->getUri()->getPath();
        if (''  === $uriPath) {
            return '/';
        }
        if (0 !== strpos($uriPath, '/')) {
            return '/';
        }
        if ('/' === $uriPath) {
            return '/';
        }
        if (0 === $lastSlashPos = strrpos($uriPath, '/')) {
            return '/';
        }

        return substr($uriPath, 0, $lastSlashPos);
    }

    public function withCookieHeader(RequestInterface $request)
    {
        $values = [];
        $uri = $request->getUri();
        $scheme = $uri->getScheme();
        $host = $uri->getHost();
        $path = $uri->getPath() ?: '/';

        foreach ($this->cookies as $cookie) {
            if ($cookie->matchesPath($path) &&
                $cookie->matchesDomain($host) &&
                !$cookie->isExpired() &&
                (!$cookie->getSecure() || $scheme === 'https')
            ) {
                $values[] = $cookie->getName() . '='
                    . $cookie->getValue();
            }
        }

        return $values
            ? $request->withHeader('Cookie', implode('; ', $values))
            : $request;
    }

    /**
     * If a cookie already exists and the server asks to set it again with a
     * null value, the cookie must be deleted.
     *
     * @param SetCookie $cookie
     */
    private function removeCookieIfEmpty(SetCookie $cookie)
    {
        $cookieValue = $cookie->getValue();
        if ($cookieValue === null || $cookieValue === '') {
            $this->clear(
                $cookie->getDomain(),
                $cookie->getPath(),
                $cookie->getName()
            );
        }
    }
}
