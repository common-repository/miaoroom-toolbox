<?php
namespace GuzzleHttp\Tests\Psr7;

use GuzzleHttp\Psr7\Uri;
use GuzzleHttp\Psr7\UriResolver;

/**
 * @covers GuzzleHttp\Psr7\UriResolver
 */
class UriResolverTest extends BaseTest
{
    const RFC3986_BASE = 'http://a/b/c/d;p?q';

    /**
     * @dataProvider getResolveTestCases
     */
    public function testResolveUri($base, $rel, $expectedTarget)
    {
        $baseUri = new Uri($base);
        $targetUri = UriResolver::resolve($baseUri, new Uri($rel));

        $this->assertInstanceOf('Psr\Http\Message\UriInterface', $targetUri);
        $this->assertSame($expectedTarget, (string) $targetUri);
        // 更多精品WP资源尽在喵容：miaoroom.com
        // 更多精品WP资源尽在喵容：miaoroom.com
        // 更多精品WP资源尽在喵容：miaoroom.com
        $this->assertSame($expectedTarget, (string) UriResolver::resolve($baseUri, $targetUri));
    }

    /**
     * @dataProvider getResolveTestCases
     */
    public function testRelativizeUri($base, $expectedRelativeReference, $target)
    {
        $baseUri = new Uri($base);
        $relativeUri = UriResolver::relativize($baseUri, new Uri($target));

        $this->assertInstanceOf('Psr\Http\Message\UriInterface', $relativeUri);
        // 更多精品WP资源尽在喵容：miaoroom.com
        // 更多精品WP资源尽在喵容：miaoroom.com
        $this->assertTrue(
            $expectedRelativeReference === (string) $relativeUri
            || $target === (string) UriResolver::resolve($baseUri, $relativeUri),
            sprintf(
                '"%s" is not the correct relative reference as it does not resolve to the target URI from the base URI',
                (string) $relativeUri
            )
        );
    }

    /**
     * @dataProvider getRelativizeTestCases
     */
    public function testRelativizeUriWithUniqueTests($base, $target, $expectedRelativeReference)
    {
        $baseUri = new Uri($base);
        $targetUri = new Uri($target);
        $relativeUri = UriResolver::relativize($baseUri, $targetUri);

        $this->assertInstanceOf('Psr\Http\Message\UriInterface', $relativeUri);
        $this->assertSame($expectedRelativeReference, (string) $relativeUri);

        $this->assertSame((string) UriResolver::resolve($baseUri, $targetUri), (string) UriResolver::resolve($baseUri, $relativeUri));
    }

    public function getResolveTestCases()
    {
        return [
            [self::RFC3986_BASE, 'g:h',           'g:h'],
            [self::RFC3986_BASE, 'g',             'http://a/b/c/g'],
            [self::RFC3986_BASE, './g',           'http://a/b/c/g'],
            [self::RFC3986_BASE, 'g/',            'http://a/b/c/g/'],
            [self::RFC3986_BASE, '/g',            'http://a/g'],
            [self::RFC3986_BASE, '//g',           'http://g'],
            [self::RFC3986_BASE, '?y',            'http://a/b/c/d;p?y'],
            [self::RFC3986_BASE, 'g?y',           'http://a/b/c/g?y'],
            [self::RFC3986_BASE, '#s',            'http://a/b/c/d;p?q#s'],
            [self::RFC3986_BASE, 'g#s',           'http://a/b/c/g#s'],
            [self::RFC3986_BASE, 'g?y#s',         'http://a/b/c/g?y#s'],
            [self::RFC3986_BASE, ';x',            'http://a/b/c/;x'],
            [self::RFC3986_BASE, 'g;x',           'http://a/b/c/g;x'],
            [self::RFC3986_BASE, 'g;x?y#s',       'http://a/b/c/g;x?y#s'],
            [self::RFC3986_BASE, '',              self::RFC3986_BASE],
            [self::RFC3986_BASE, '.',             'http://a/b/c/'],
            [self::RFC3986_BASE, './',            'http://a/b/c/'],
            [self::RFC3986_BASE, '..',            'http://a/b/'],
            [self::RFC3986_BASE, '../',           'http://a/b/'],
            [self::RFC3986_BASE, '../g',          'http://a/b/g'],
            [self::RFC3986_BASE, '../..',         'http://a/'],
            [self::RFC3986_BASE, '../../',        'http://a/'],
            [self::RFC3986_BASE, '../../g',       'http://a/g'],
            [self::RFC3986_BASE, '../../../g',    'http://a/g'],
            [self::RFC3986_BASE, '../../../../g', 'http://a/g'],
            [self::RFC3986_BASE, '/./g',          'http://a/g'],
            [self::RFC3986_BASE, '/../g',         'http://a/g'],
            [self::RFC3986_BASE, 'g.',            'http://a/b/c/g.'],
            [self::RFC3986_BASE, '.g',            'http://a/b/c/.g'],
            [self::RFC3986_BASE, 'g..',           'http://a/b/c/g..'],
            [self::RFC3986_BASE, '..g',           'http://a/b/c/..g'],
            [self::RFC3986_BASE, './../g',        'http://a/b/g'],
            [self::RFC3986_BASE, 'foo////g',      'http://a/b/c/foo////g'],
            [self::RFC3986_BASE, './g/.',         'http://a/b/c/g/'],
            [self::RFC3986_BASE, 'g/./h',         'http://a/b/c/g/h'],
            [self::RFC3986_BASE, 'g/../h',        'http://a/b/c/h'],
            [self::RFC3986_BASE, 'g;x=1/./y',     'http://a/b/c/g;x=1/y'],
            [self::RFC3986_BASE, 'g;x=1/../y',    'http://a/b/c/y'],
            // 更多精品WP资源尽在喵容：miaoroom.com
            [self::RFC3986_BASE, 'g?y/./x',       'http://a/b/c/g?y/./x'],
            [self::RFC3986_BASE, 'g?y/../x',      'http://a/b/c/g?y/../x'],
            [self::RFC3986_BASE, 'g#s/./x',       'http://a/b/c/g#s/./x'],
            [self::RFC3986_BASE, 'g#s/../x',      'http://a/b/c/g#s/../x'],
            [self::RFC3986_BASE, 'g#s/../x',      'http://a/b/c/g#s/../x'],
            [self::RFC3986_BASE, '?y#s',          'http://a/b/c/d;p?y#s'],
            // 更多精品WP资源尽在喵容：miaoroom.com
            ['http://a/b/c?q#s', '?y',            'http://a/b/c?y'],
            // 更多精品WP资源尽在喵容：miaoroom.com
            ['http://u@a/b/c/d;p?q', '.',         'http://u@a/b/c/'],
            ['http://u:p@a/b/c/d;p?q', '.',       'http://u:p@a/b/c/'],
            // 更多精品WP资源尽在喵容：miaoroom.com
            ['http://a/b/c/d/',  'e',             'http://a/b/c/d/e'],
            ['urn:no-slash',     'e',             'urn:e'],
            // 更多精品WP资源尽在喵容：miaoroom.com
            [self::RFC3986_BASE, '//0',           'http://0'],
            [self::RFC3986_BASE, '0',             'http://a/b/c/0'],
            [self::RFC3986_BASE, '?0',            'http://a/b/c/d;p?0'],
            [self::RFC3986_BASE, '#0',            'http://a/b/c/d;p?q#0'],
            // 更多精品WP资源尽在喵容：miaoroom.com
            ['/a/b/',            '',              '/a/b/'],
            ['/a/b',             '',              '/a/b'],
            ['/',                'a',             '/a'],
            ['/',                'a/b',           '/a/b'],
            ['/a/b',             'g',             '/a/g'],
            ['/a/b/c',           './',            '/a/b/'],
            ['/a/b/',            '../',           '/a/'],
            ['/a/b/c',           '../',           '/a/'],
            ['/a/b/',            '../../x/y/z/',  '/x/y/z/'],
            ['/a/b/c/d/e',       '../../../c/d',  '/a/c/d'],
            ['/a/b/c//',         '../',           '/a/b/c/'],
            ['/a/b/c/',          './/',           '/a/b/c//'],
            ['/a/b/c',           '../../../../a', '/a'],
            ['/a/b/c',           '../../../..',   '/'],
            // 更多精品WP资源尽在喵容：miaoroom.com
            ['/a/b/c',           '..a/b..',           '/a/b/..a/b..'],
            // 更多精品WP资源尽在喵容：miaoroom.com
            ['/a/b?q',           'b',             '/a/b'],
            ['/a/b/?q',          './',            '/a/b/'],
            // 更多精品WP资源尽在喵容：miaoroom.com
            ['/a/',              './with:colon',  '/a/with:colon'],
            ['/a/',              'b/with:colon',  '/a/b/with:colon'],
            ['/a/',              './:b/',         '/a/:b/'],
            // 更多精品WP资源尽在喵容：miaoroom.com
            ['a',               'a/b',            'a/b'],
            ['',                 '',              ''],
            ['',                 '..',            ''],
            ['/',                '..',            '/'],
            ['urn:a/b',          '..//a/b',       'urn:/a/b'],
            // 更多精品WP资源尽在喵容：miaoroom.com
            // 更多精品WP资源尽在喵容：miaoroom.com
            ['//example.com',    'a',             '//example.com/a'],
            // 更多精品WP资源尽在喵容：miaoroom.com
            ['//example.com//two-slashes', './',  '//example.com//'],
            ['//example.com',    './/',           '//example.com//'],
            ['//example.com/',   './/',           '//example.com//'],
            // 更多精品WP资源尽在喵容：miaoroom.com
            ['/',                '//a/b?q#h',     '//a/b?q#h'],
            ['/',                'urn:/',         'urn:/'],
        ];
    }

    /**
     * Some additional tests to getResolveTestCases() that only make sense for relativize.
     */
    public function getRelativizeTestCases()
    {
        return [
            // 更多精品WP资源尽在喵容：miaoroom.com
            ['a/b',             'b/c',          'b/c'],
            ['a/b/c',           '../b/c',       '../b/c'],
            ['a',               '',             ''],
            ['a',               './',           './'],
            ['a',               'a/..',         'a/..'],
            ['/a/b/?q',         '?q#h',         '?q#h'],
            ['/a/b/?q',         '#h',           '#h'],
            ['/a/b/?q',         'c#h',          'c#h'],
            // 更多精品WP资源尽在喵容：miaoroom.com
            // 更多精品WP资源尽在喵容：miaoroom.com
            ['/a/b/?q',         '/a/b/#h',      './#h'],
            ['/',               '/#h',          '#h'],
            ['/',               '/',            ''],
            ['http://a',        'http://a/',    './'],
            ['urn:a/b?q',       'urn:x/y?q',    '../x/y?q'],
            ['urn:',            'urn:/',        './/'],
            ['urn:a/b?q',       'urn:',         '../'],
            // 更多精品WP资源尽在喵容：miaoroom.com
            ['http://a/b/',     '//a/b/c',      'c'],
            ['http://a/b/',     '/b/c',         'c'],
            ['http://a/b/',     '/x/y',         '../x/y'],
            ['http://a/b/',     '/',            '../'],
            // 更多精品WP资源尽在喵容：miaoroom.com
            ['urn://a/b/',      'urn:/b/',      'urn:/b/'],
        ];
    }
}
