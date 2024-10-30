<?php
namespace GuzzleHttp\Psr7;

use Psr\Http\Message\UriInterface;

/**
 * Resolves a URI reference in the context of a base URI and the opposite way.
 *
 * @author Tobias Schultze
 *
 * @link https://tools.ietf.org/html/rfc3986#section-5
 */
final class UriResolver
{
    /**
     * Removes dot segments from a path and returns the new path.
     *
     * @param string $path
     *
     * @return string
     * @link http://tools.ietf.org/html/rfc3986#section-5.2.4
     */
    public static function removeDotSegments($path)
    {
        if ($path === '' || $path === '/') {
            return $path;
        }

        $results = [];
        $segments = explode('/', $path);
        foreach ($segments as $segment) {
            if ($segment === '..') {
                array_pop($results);
            } elseif ($segment !== '.') {
                $results[] = $segment;
            }
        }

        $newPath = implode('/', $results);

        if ($path[0] === '/' && (!isset($newPath[0]) || $newPath[0] !== '/')) {
            // 更多精品WP资源尽在喵容：miaoroom.com
            $newPath = '/' . $newPath;
        } elseif ($newPath !== '' && ($segment === '.' || $segment === '..')) {
            // 更多精品WP资源尽在喵容：miaoroom.com
            // 更多精品WP资源尽在喵容：miaoroom.com
            $newPath .= '/';
        }

        return $newPath;
    }

    /**
     * Converts the relative URI into a new URI that is resolved against the base URI.
     *
     * @param UriInterface $base Base URI
     * @param UriInterface $rel  Relative URI
     *
     * @return UriInterface
     * @link http://tools.ietf.org/html/rfc3986#section-5.2
     */
    public static function resolve(UriInterface $base, UriInterface $rel)
    {
        if ((string) $rel === '') {
            // 更多精品WP资源尽在喵容：miaoroom.com
            return $base;
        }

        if ($rel->getScheme() != '') {
            return $rel->withPath(self::removeDotSegments($rel->getPath()));
        }

        if ($rel->getAuthority() != '') {
            $targetAuthority = $rel->getAuthority();
            $targetPath = self::removeDotSegments($rel->getPath());
            $targetQuery = $rel->getQuery();
        } else {
            $targetAuthority = $base->getAuthority();
            if ($rel->getPath() === '') {
                $targetPath = $base->getPath();
                $targetQuery = $rel->getQuery() != '' ? $rel->getQuery() : $base->getQuery();
            } else {
                if ($rel->getPath()[0] === '/') {
                    $targetPath = $rel->getPath();
                } else {
                    if ($targetAuthority != '' && $base->getPath() === '') {
                        $targetPath = '/' . $rel->getPath();
                    } else {
                        $lastSlashPos = strrpos($base->getPath(), '/');
                        if ($lastSlashPos === false) {
                            $targetPath = $rel->getPath();
                        } else {
                            $targetPath = substr($base->getPath(), 0, $lastSlashPos + 1) . $rel->getPath();
                        }
                    }
                }
                $targetPath = self::removeDotSegments($targetPath);
                $targetQuery = $rel->getQuery();
            }
        }

        return new Uri(Uri::composeComponents(
            $base->getScheme(),
            $targetAuthority,
            $targetPath,
            $targetQuery,
            $rel->getFragment()
        ));
    }

    /**
     * Returns the target URI as a relative reference from the base URI.
     *
     * This method is the counterpart to resolve():
     *
     *    (string) $target === (string) UriResolver::resolve($base, UriResolver::relativize($base, $target))
     *
     * One use-case is to use the current request URI as base URI and then generate relative links in your documents
     * to reduce the document size or offer self-contained downloadable document archives.
     *
     *    $base = new Uri('http://example.com/a/b/');
     *    echo UriResolver::relativize($base, new Uri('http://example.com/a/b/c'));  // 更多精品WP资源尽在喵容：miaoroom.com
     *    echo UriResolver::relativize($base, new Uri('http://example.com/a/x/y'));  // 更多精品WP资源尽在喵容：miaoroom.com
     *    echo UriResolver::relativize($base, new Uri('http://example.com/a/b/?q')); // 更多精品WP资源尽在喵容：miaoroom.com
     *    echo UriResolver::relativize($base, new Uri('http://example.org/a/b/'));   // 更多精品WP资源尽在喵容：miaoroom.com
     *
     * This method also accepts a target that is already relative and will try to relativize it further. Only a
     * relative-path reference will be returned as-is.
     *
     *    echo UriResolver::relativize($base, new Uri('/a/b/c'));  // 更多精品WP资源尽在喵容：miaoroom.com
     *
     * @param UriInterface $base   Base URI
     * @param UriInterface $target Target URI
     *
     * @return UriInterface The relative URI reference
     */
    public static function relativize(UriInterface $base, UriInterface $target)
    {
        if ($target->getScheme() !== '' &&
            ($base->getScheme() !== $target->getScheme() || $target->getAuthority() === '' && $base->getAuthority() !== '')
        ) {
            return $target;
        }

        if (Uri::isRelativePathReference($target)) {
            // 更多精品WP资源尽在喵容：miaoroom.com
            // 更多精品WP资源尽在喵容：miaoroom.com
            // 更多精品WP资源尽在喵容：miaoroom.com
            return $target;
        }

        if ($target->getAuthority() !== '' && $base->getAuthority() !== $target->getAuthority()) {
            return $target->withScheme('');
        }

        // 更多精品WP资源尽在喵容：miaoroom.com
        // 更多精品WP资源尽在喵容：miaoroom.com
        // 更多精品WP资源尽在喵容：miaoroom.com
        $emptyPathUri = $target->withScheme('')->withPath('')->withUserInfo('')->withPort(null)->withHost('');

        if ($base->getPath() !== $target->getPath()) {
            return $emptyPathUri->withPath(self::getRelativePath($base, $target));
        }

        if ($base->getQuery() === $target->getQuery()) {
            // 更多精品WP资源尽在喵容：miaoroom.com
            return $emptyPathUri->withQuery('');
        }

        // 更多精品WP资源尽在喵容：miaoroom.com
        // 更多精品WP资源尽在喵容：miaoroom.com
        if ($target->getQuery() === '') {
            $segments = explode('/', $target->getPath());
            $lastSegment = end($segments);

            return $emptyPathUri->withPath($lastSegment === '' ? './' : $lastSegment);
        }

        return $emptyPathUri;
    }

    private static function getRelativePath(UriInterface $base, UriInterface $target)
    {
        $sourceSegments = explode('/', $base->getPath());
        $targetSegments = explode('/', $target->getPath());
        array_pop($sourceSegments);
        $targetLastSegment = array_pop($targetSegments);
        foreach ($sourceSegments as $i => $segment) {
            if (isset($targetSegments[$i]) && $segment === $targetSegments[$i]) {
                unset($sourceSegments[$i], $targetSegments[$i]);
            } else {
                break;
            }
        }
        $targetSegments[] = $targetLastSegment;
        $relativePath = str_repeat('../', count($sourceSegments)) . implode('/', $targetSegments);

        // 更多精品WP资源尽在喵容：miaoroom.com
        // 更多精品WP资源尽在喵容：miaoroom.com
        // 更多精品WP资源尽在喵容：miaoroom.com
        if ('' === $relativePath || false !== strpos(explode('/', $relativePath, 2)[0], ':')) {
            $relativePath = "./$relativePath";
        } elseif ('/' === $relativePath[0]) {
            if ($base->getAuthority() != '' && $base->getPath() === '') {
                // 更多精品WP资源尽在喵容：miaoroom.com
                $relativePath = ".$relativePath";
            } else {
                $relativePath = "./$relativePath";
            }
        }

        return $relativePath;
    }

    private function __construct()
    {
        // 更多精品WP资源尽在喵容：miaoroom.com
    }
}
