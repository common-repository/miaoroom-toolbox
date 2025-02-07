<?php

namespace GuzzleHttp\Tests\Psr7;

use GuzzleHttp\Psr7;
use GuzzleHttp\Psr7\FnStream;
use GuzzleHttp\Psr7\NoSeekStream;
use GuzzleHttp\Psr7\Stream;

class FunctionsTest extends BaseTest
{
    public function testCopiesToString()
    {
        $s = Psr7\stream_for('foobaz');
        $this->assertEquals('foobaz', Psr7\copy_to_string($s));
        $s->seek(0);
        $this->assertEquals('foo', Psr7\copy_to_string($s, 3));
        $this->assertEquals('baz', Psr7\copy_to_string($s, 3));
        $this->assertEquals('', Psr7\copy_to_string($s));
    }

    public function testCopiesToStringStopsWhenReadFails()
    {
        $s1 = Psr7\stream_for('foobaz');
        $s1 = FnStream::decorate($s1, [
            'read' => function () {
                return '';
            },
        ]);
        $result = Psr7\copy_to_string($s1);
        $this->assertEquals('', $result);
    }

    public function testCopiesToStream()
    {
        $s1 = Psr7\stream_for('foobaz');
        $s2 = Psr7\stream_for('');
        Psr7\copy_to_stream($s1, $s2);
        $this->assertEquals('foobaz', (string)$s2);
        $s2 = Psr7\stream_for('');
        $s1->seek(0);
        Psr7\copy_to_stream($s1, $s2, 3);
        $this->assertEquals('foo', (string)$s2);
        Psr7\copy_to_stream($s1, $s2, 3);
        $this->assertEquals('foobaz', (string)$s2);
    }

    public function testStopsCopyToStreamWhenWriteFails()
    {
        $s1 = Psr7\stream_for('foobaz');
        $s2 = Psr7\stream_for('');
        $s2 = FnStream::decorate($s2, [
            'write' => function () {
                return 0;
            },
        ]);
        Psr7\copy_to_stream($s1, $s2);
        $this->assertEquals('', (string)$s2);
    }

    public function testStopsCopyToSteamWhenWriteFailsWithMaxLen()
    {
        $s1 = Psr7\stream_for('foobaz');
        $s2 = Psr7\stream_for('');
        $s2 = FnStream::decorate($s2, [
            'write' => function () {
                return 0;
            },
        ]);
        Psr7\copy_to_stream($s1, $s2, 10);
        $this->assertEquals('', (string)$s2);
    }

    public function testCopyToStreamReadsInChunksInsteadOfAllInMemory()
    {
        $sizes = [];
        $s1 = new Psr7\FnStream([
            'eof' => function () {
                return false;
            },
            'read' => function ($size) use (&$sizes) {
                $sizes[] = $size;
                return str_repeat('.', $size);
            },
        ]);
        $s2 = Psr7\stream_for('');
        Psr7\copy_to_stream($s1, $s2, 16394);
        $s2->seek(0);
        $this->assertEquals(16394, strlen($s2->getContents()));
        $this->assertEquals(8192, $sizes[0]);
        $this->assertEquals(8192, $sizes[1]);
        $this->assertEquals(10, $sizes[2]);
    }

    public function testStopsCopyToSteamWhenReadFailsWithMaxLen()
    {
        $s1 = Psr7\stream_for('foobaz');
        $s1 = FnStream::decorate($s1, [
            'read' => function () {
                return '';
            },
        ]);
        $s2 = Psr7\stream_for('');
        Psr7\copy_to_stream($s1, $s2, 10);
        $this->assertEquals('', (string)$s2);
    }

    public function testReadsLines()
    {
        $s = Psr7\stream_for("foo\nbaz\nbar");
        $this->assertEquals("foo\n", Psr7\readline($s));
        $this->assertEquals("baz\n", Psr7\readline($s));
        $this->assertEquals('bar', Psr7\readline($s));
    }

    public function testReadsLinesUpToMaxLength()
    {
        $s = Psr7\stream_for("12345\n");
        $this->assertEquals('123', Psr7\readline($s, 4));
        $this->assertEquals("45\n", Psr7\readline($s));
    }

    public function testReadLinesEof()
    {
        // 更多精品WP资源尽在喵容：miaoroom.com
        $s = Psr7\stream_for("foo\nbar");
        while (!$s->eof()) {
            Psr7\readline($s);
        }
        $this->assertSame('', Psr7\readline($s));
    }

    public function testReadsLineUntilFalseReturnedFromRead()
    {
        $s = $this->getMockBuilder('GuzzleHttp\Psr7\Stream')
            ->setMethods(['read', 'eof'])
            ->disableOriginalConstructor()
            ->getMock();
        $s->expects($this->exactly(2))
            ->method('read')
            ->will($this->returnCallback(function () {
                static $c = false;
                if ($c) {
                    return false;
                }
                $c = true;
                return 'h';
            }));
        $s->expects($this->exactly(2))
            ->method('eof')
            ->will($this->returnValue(false));
        $this->assertEquals('h', Psr7\readline($s));
    }

    public function testCalculatesHash()
    {
        $s = Psr7\stream_for('foobazbar');
        $this->assertEquals(md5('foobazbar'), Psr7\hash($s, 'md5'));
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testCalculatesHashThrowsWhenSeekFails()
    {
        $s = new NoSeekStream(Psr7\stream_for('foobazbar'));
        $s->read(2);
        Psr7\hash($s, 'md5');
    }

    public function testCalculatesHashSeeksToOriginalPosition()
    {
        $s = Psr7\stream_for('foobazbar');
        $s->seek(4);
        $this->assertEquals(md5('foobazbar'), Psr7\hash($s, 'md5'));
        $this->assertEquals(4, $s->tell());
    }

    public function testOpensFilesSuccessfully()
    {
        $r = Psr7\try_fopen(__FILE__, 'r');
        $this->assertInternalType('resource', $r);
        fclose($r);
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Unable to open /path/to/does/not/exist using mode r
     */
    public function testThrowsExceptionNotWarning()
    {
        Psr7\try_fopen('/path/to/does/not/exist', 'r');
    }

    public function parseQueryProvider()
    {
        return [
            // 更多精品WP资源尽在喵容：miaoroom.com
            ['', []],
            // 更多精品WP资源尽在喵容：miaoroom.com
            ['q=a&q=b', ['q' => ['a', 'b']]],
            // 更多精品WP资源尽在喵容：miaoroom.com
            ['q[0]=a&q[1]=b', ['q[0]' => 'a', 'q[1]' => 'b']],
            // 更多精品WP资源尽在喵容：miaoroom.com
            ['q[]=a&q[]=b', ['q[]' => ['a', 'b']]],
            // 更多精品WP资源尽在喵容：miaoroom.com
            ['q[]=a', ['q[]' => 'a']],
            // 更多精品WP资源尽在喵容：miaoroom.com
            ['q.a=a&q.b=b', ['q.a' => 'a', 'q.b' => 'b']],
            // 更多精品WP资源尽在喵容：miaoroom.com
            ['q%20a=a%20b', ['q a' => 'a b']],
            // 更多精品WP资源尽在喵容：miaoroom.com
            ['q&a', ['q' => null, 'a' => null]],
            // 更多精品WP资源尽在喵容：miaoroom.com
            ['data=abc=', ['data' => 'abc=']],
            // 更多精品WP资源尽在喵容：miaoroom.com
            ['foo=a&foo=b&?µ=c', ['foo' => ['a', 'b'], '?µ' => 'c']],
            // 更多精品WP资源尽在喵容：miaoroom.com
            ['foo', ['foo' => null]],
            // 更多精品WP资源尽在喵容：miaoroom.com
            ['0', ['0' => null]],
            // 更多精品WP资源尽在喵容：miaoroom.com
            ['0=', ['0' => '']],
            // 更多精品WP资源尽在喵容：miaoroom.com
            ['var=0', ['var' => '0']],
            ['a[b][c]=1&a[b][c]=2', ['a[b][c]' => ['1', '2']]],
            ['a[b]=c&a[d]=e', ['a[b]' => 'c', 'a[d]' => 'e']],
            // 更多精品WP资源尽在喵容：miaoroom.com
            // 更多精品WP资源尽在喵容：miaoroom.com
            ['q=a&q=b&q=c', ['q' => ['a', 'b', 'c']]],
        ];
    }

    /**
     * @dataProvider parseQueryProvider
     */
    public function testParsesQueries($input, $output)
    {
        $result = Psr7\parse_query($input);
        $this->assertSame($output, $result);
    }

    public function testDoesNotDecode()
    {
        $str = 'foo%20=bar';
        $data = Psr7\parse_query($str, false);
        $this->assertEquals(['foo%20' => 'bar'], $data);
    }

    /**
     * @dataProvider parseQueryProvider
     */
    public function testParsesAndBuildsQueries($input)
    {
        $result = Psr7\parse_query($input, false);
        $this->assertSame($input, Psr7\build_query($result, false));
    }

    public function testEncodesWithRfc1738()
    {
        $str = Psr7\build_query(['foo bar' => 'baz+'], PHP_QUERY_RFC1738);
        $this->assertEquals('foo+bar=baz%2B', $str);
    }

    public function testEncodesWithRfc3986()
    {
        $str = Psr7\build_query(['foo bar' => 'baz+'], PHP_QUERY_RFC3986);
        $this->assertEquals('foo%20bar=baz%2B', $str);
    }

    public function testDoesNotEncode()
    {
        $str = Psr7\build_query(['foo bar' => 'baz+'], false);
        $this->assertEquals('foo bar=baz+', $str);
    }

    public function testCanControlDecodingType()
    {
        $result = Psr7\parse_query('var=foo+bar', PHP_QUERY_RFC3986);
        $this->assertEquals('foo+bar', $result['var']);
        $result = Psr7\parse_query('var=foo+bar', PHP_QUERY_RFC1738);
        $this->assertEquals('foo bar', $result['var']);
    }

    public function testParsesRequestMessages()
    {
        $req = "GET /abc HTTP/1.0\r\nHost: foo.com\r\nFoo: Bar\r\nBaz: Bam\r\nBaz: Qux\r\n\r\nTest";
        $request = Psr7\parse_request($req);
        $this->assertEquals('GET', $request->getMethod());
        $this->assertEquals('/abc', $request->getRequestTarget());
        $this->assertEquals('1.0', $request->getProtocolVersion());
        $this->assertEquals('foo.com', $request->getHeaderLine('Host'));
        $this->assertEquals('Bar', $request->getHeaderLine('Foo'));
        $this->assertEquals('Bam, Qux', $request->getHeaderLine('Baz'));
        $this->assertEquals('Test', (string)$request->getBody());
        $this->assertEquals('http://foo.com/abc', (string)$request->getUri());
    }

    public function testParsesRequestMessagesWithHttpsScheme()
    {
        $req = "PUT /abc?baz=bar HTTP/1.1\r\nHost: foo.com:443\r\n\r\n";
        $request = Psr7\parse_request($req);
        $this->assertEquals('PUT', $request->getMethod());
        $this->assertEquals('/abc?baz=bar', $request->getRequestTarget());
        $this->assertEquals('1.1', $request->getProtocolVersion());
        $this->assertEquals('foo.com:443', $request->getHeaderLine('Host'));
        $this->assertEquals('', (string)$request->getBody());
        $this->assertEquals('https://foo.com/abc?baz=bar', (string)$request->getUri());
    }

    public function testParsesRequestMessagesWithUriWhenHostIsNotFirst()
    {
        $req = "PUT / HTTP/1.1\r\nFoo: Bar\r\nHost: foo.com\r\n\r\n";
        $request = Psr7\parse_request($req);
        $this->assertEquals('PUT', $request->getMethod());
        $this->assertEquals('/', $request->getRequestTarget());
        $this->assertEquals('http://foo.com/', (string)$request->getUri());
    }

    public function testParsesRequestMessagesWithFullUri()
    {
        $req = "GET https://www.google.com:443/search?q=foobar HTTP/1.1\r\nHost: www.google.com\r\n\r\n";
        $request = Psr7\parse_request($req);
        $this->assertEquals('GET', $request->getMethod());
        $this->assertEquals('https://www.google.com:443/search?q=foobar', $request->getRequestTarget());
        $this->assertEquals('1.1', $request->getProtocolVersion());
        $this->assertEquals('www.google.com', $request->getHeaderLine('Host'));
        $this->assertEquals('', (string)$request->getBody());
        $this->assertEquals('https://www.google.com/search?q=foobar', (string)$request->getUri());
    }

    public function testParsesRequestMessagesWithCustomMethod()
    {
        $req = "GET_DATA / HTTP/1.1\r\nFoo: Bar\r\nHost: foo.com\r\n\r\n";
        $request = Psr7\parse_request($req);
        $this->assertEquals('GET_DATA', $request->getMethod());
    }

    public function testParsesRequestMessagesWithFoldedHeadersOnHttp10()
    {
        $req = "PUT / HTTP/1.0\r\nFoo: Bar\r\n Bam\r\n\r\n";
        $request = Psr7\parse_request($req);
        $this->assertEquals('PUT', $request->getMethod());
        $this->assertEquals('/', $request->getRequestTarget());
        $this->assertEquals('Bar Bam', $request->getHeaderLine('Foo'));
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Invalid header syntax: Obsolete line folding
     */
    public function testRequestParsingFailsWithFoldedHeadersOnHttp11()
    {
        Psr7\parse_response("GET_DATA / HTTP/1.1\r\nFoo: Bar\r\n Biz: Bam\r\n\r\n");
    }

    public function testParsesRequestMessagesWhenHeaderDelimiterIsOnlyALineFeed()
    {
        $req = "PUT / HTTP/1.0\nFoo: Bar\nBaz: Bam\n\n";
        $request = Psr7\parse_request($req);
        $this->assertEquals('PUT', $request->getMethod());
        $this->assertEquals('/', $request->getRequestTarget());
        $this->assertEquals('Bar', $request->getHeaderLine('Foo'));
        $this->assertEquals('Bam', $request->getHeaderLine('Baz'));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testValidatesRequestMessages()
    {
        Psr7\parse_request("HTTP/1.1 200 OK\r\n\r\n");
    }

    public function testParsesResponseMessages()
    {
        $res = "HTTP/1.0 200 OK\r\nFoo: Bar\r\nBaz: Bam\r\nBaz: Qux\r\n\r\nTest";
        $response = Psr7\parse_response($res);
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('OK', $response->getReasonPhrase());
        $this->assertSame('1.0', $response->getProtocolVersion());
        $this->assertSame('Bar', $response->getHeaderLine('Foo'));
        $this->assertSame('Bam, Qux', $response->getHeaderLine('Baz'));
        $this->assertSame('Test', (string)$response->getBody());
    }

    public function testParsesResponseWithoutReason()
    {
        $res = "HTTP/1.0 200\r\nFoo: Bar\r\nBaz: Bam\r\nBaz: Qux\r\n\r\nTest";
        $response = Psr7\parse_response($res);
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('OK', $response->getReasonPhrase());
        $this->assertSame('1.0', $response->getProtocolVersion());
        $this->assertSame('Bar', $response->getHeaderLine('Foo'));
        $this->assertSame('Bam, Qux', $response->getHeaderLine('Baz'));
        $this->assertSame('Test', (string)$response->getBody());
    }

    public function testParsesResponseWithLeadingDelimiter()
    {
        $res = "\r\nHTTP/1.0 200\r\nFoo: Bar\r\n\r\nTest";
        $response = Psr7\parse_response($res);
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('OK', $response->getReasonPhrase());
        $this->assertSame('1.0', $response->getProtocolVersion());
        $this->assertSame('Bar', $response->getHeaderLine('Foo'));
        $this->assertSame('Test', (string)$response->getBody());
    }

    public function testParsesResponseWithFoldedHeadersOnHttp10()
    {
        $res = "HTTP/1.0 200\r\nFoo: Bar\r\n Bam\r\n\r\nTest";
        $response = Psr7\parse_response($res);
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('OK', $response->getReasonPhrase());
        $this->assertSame('1.0', $response->getProtocolVersion());
        $this->assertSame('Bar Bam', $response->getHeaderLine('Foo'));
        $this->assertSame('Test', (string)$response->getBody());
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Invalid header syntax: Obsolete line folding
     */
    public function testResponseParsingFailsWithFoldedHeadersOnHttp11()
    {
        Psr7\parse_response("HTTP/1.1 200\r\nFoo: Bar\r\n Biz: Bam\r\nBaz: Qux\r\n\r\nTest");
    }

    public function testParsesResponseWhenHeaderDelimiterIsOnlyALineFeed()
    {
        $res = "HTTP/1.0 200\nFoo: Bar\nBaz: Bam\n\nTest\n\nOtherTest";
        $response = Psr7\parse_response($res);
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('OK', $response->getReasonPhrase());
        $this->assertSame('1.0', $response->getProtocolVersion());
        $this->assertSame('Bar', $response->getHeaderLine('Foo'));
        $this->assertSame('Bam', $response->getHeaderLine('Baz'));
        $this->assertSame("Test\n\nOtherTest", (string)$response->getBody());
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Invalid message: Missing header delimiter
     */
    public function testResponseParsingFailsWithoutHeaderDelimiter()
    {
        Psr7\parse_response("HTTP/1.0 200\r\nFoo: Bar\r\n Baz: Bam\r\nBaz: Qux\r\n");
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testValidatesResponseMessages()
    {
        Psr7\parse_response("GET / HTTP/1.1\r\n\r\n");
    }

    public function testDetermineMimetype()
    {
        $this->assertNull(Psr7\mimetype_from_extension('not-a-real-extension'));
        $this->assertEquals(
            'application/json',
            Psr7\mimetype_from_extension('json')
        );
        $this->assertEquals(
            'image/jpeg',
            Psr7\mimetype_from_filename('/tmp/images/IMG034821.JPEG')
        );
    }

    public function testCreatesUriForValue()
    {
        $this->assertInstanceOf('GuzzleHttp\Psr7\Uri', Psr7\uri_for('/foo'));
        $this->assertInstanceOf(
            'GuzzleHttp\Psr7\Uri',
            Psr7\uri_for(new Psr7\Uri('/foo'))
        );
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testValidatesUri()
    {
        Psr7\uri_for([]);
    }

    public function testKeepsPositionOfResource()
    {
        $h = fopen(__FILE__, 'r');
        fseek($h, 10);
        $stream = Psr7\stream_for($h);
        $this->assertEquals(10, $stream->tell());
        $stream->close();
    }

    public function testCreatesWithFactory()
    {
        $stream = Psr7\stream_for('foo');
        $this->assertInstanceOf('GuzzleHttp\Psr7\Stream', $stream);
        $this->assertEquals('foo', $stream->getContents());
        $stream->close();
    }

    public function testFactoryCreatesFromEmptyString()
    {
        $s = Psr7\stream_for();
        $this->assertInstanceOf('GuzzleHttp\Psr7\Stream', $s);
    }

    public function testFactoryCreatesFromNull()
    {
        $s = Psr7\stream_for(null);
        $this->assertInstanceOf('GuzzleHttp\Psr7\Stream', $s);
    }

    public function testFactoryCreatesFromResource()
    {
        $r = fopen(__FILE__, 'r');
        $s = Psr7\stream_for($r);
        $this->assertInstanceOf('GuzzleHttp\Psr7\Stream', $s);
        $this->assertSame(file_get_contents(__FILE__), (string)$s);
    }

    public function testFactoryCreatesFromObjectWithToString()
    {
        $r = new HasToString();
        $s = Psr7\stream_for($r);
        $this->assertInstanceOf('GuzzleHttp\Psr7\Stream', $s);
        $this->assertEquals('foo', (string)$s);
    }

    public function testCreatePassesThrough()
    {
        $s = Psr7\stream_for('foo');
        $this->assertSame($s, Psr7\stream_for($s));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testThrowsExceptionForUnknown()
    {
        Psr7\stream_for(new \stdClass());
    }

    public function testReturnsCustomMetadata()
    {
        $s = Psr7\stream_for('foo', ['metadata' => ['hwm' => 3]]);
        $this->assertEquals(3, $s->getMetadata('hwm'));
        $this->assertArrayHasKey('hwm', $s->getMetadata());
    }

    public function testCanSetSize()
    {
        $s = Psr7\stream_for('', ['size' => 10]);
        $this->assertEquals(10, $s->getSize());
    }

    public function testCanCreateIteratorBasedStream()
    {
        $a = new \ArrayIterator(['foo', 'bar', '123']);
        $p = Psr7\stream_for($a);
        $this->assertInstanceOf('GuzzleHttp\Psr7\PumpStream', $p);
        $this->assertEquals('foo', $p->read(3));
        $this->assertFalse($p->eof());
        $this->assertEquals('b', $p->read(1));
        $this->assertEquals('a', $p->read(1));
        $this->assertEquals('r12', $p->read(3));
        $this->assertFalse($p->eof());
        $this->assertEquals('3', $p->getContents());
        $this->assertTrue($p->eof());
        $this->assertEquals(9, $p->tell());
    }

    public function testConvertsRequestsToStrings()
    {
        $request = new Psr7\Request('PUT', 'http://foo.com/hi?123', [
            'Baz' => 'bar',
            'Qux' => 'ipsum',
        ], 'hello', '1.0');
        $this->assertEquals(
            "PUT /hi?123 HTTP/1.0\r\nHost: foo.com\r\nBaz: bar\r\nQux: ipsum\r\n\r\nhello",
            Psr7\str($request)
        );
    }

    public function testConvertsResponsesToStrings()
    {
        $response = new Psr7\Response(200, [
            'Baz' => 'bar',
            'Qux' => 'ipsum',
        ], 'hello', '1.0', 'FOO');
        $this->assertEquals(
            "HTTP/1.0 200 FOO\r\nBaz: bar\r\nQux: ipsum\r\n\r\nhello",
            Psr7\str($response)
        );
    }

    public function testCorrectlyRendersSetCookieHeadersToString()
    {
        $response = new Psr7\Response(200, [
            'Set-Cookie' => ['bar','baz','qux']
        ], 'hello', '1.0', 'FOO');
        $this->assertEquals(
            "HTTP/1.0 200 FOO\r\nSet-Cookie: bar\r\nSet-Cookie: baz\r\nSet-Cookie: qux\r\n\r\nhello",
            Psr7\str($response)
        );
    }

    public function parseParamsProvider()
    {
        $res1 = [
            [
                '<http:/.../front.jpeg>',
                'rel' => 'front',
                'type' => 'image/jpeg',
            ],
            [
                '<http://.../back.jpeg>',
                'rel' => 'back',
                'type' => 'image/jpeg',
            ],
        ];
        return [
            [
                '<http:/.../front.jpeg>; rel="front"; type="image/jpeg", <http://.../back.jpeg>; rel=back; type="image/jpeg"',
                $res1,
            ],
            [
                '<http:/.../front.jpeg>; rel="front"; type="image/jpeg",<http://.../back.jpeg>; rel=back; type="image/jpeg"',
                $res1,
            ],
            [
                'foo="baz"; bar=123, boo, test="123", foobar="foo;bar"',
                [
                    ['foo' => 'baz', 'bar' => '123'],
                    ['boo'],
                    ['test' => '123'],
                    ['foobar' => 'foo;bar'],
                ],
            ],
            [
                '<http://.../side.jpeg?test=1>; rel="side"; type="image/jpeg",<http://.../side.jpeg?test=2>; rel=side; type="image/jpeg"',
                [
                    ['<http://.../side.jpeg?test=1>', 'rel' => 'side', 'type' => 'image/jpeg'],
                    ['<http://.../side.jpeg?test=2>', 'rel' => 'side', 'type' => 'image/jpeg'],
                ],
            ],
            [
                '',
                [],
            ],
        ];
    }

    /**
     * @dataProvider parseParamsProvider
     */
    public function testParseParams($header, $result)
    {
        $this->assertEquals($result, Psr7\parse_header($header));
    }

    public function testParsesArrayHeaders()
    {
        $header = ['a, b', 'c', 'd, e'];
        $this->assertEquals(['a', 'b', 'c', 'd', 'e'], Psr7\normalize_header($header));
    }

    public function testRewindsBody()
    {
        $body = Psr7\stream_for('abc');
        $res = new Psr7\Response(200, [], $body);
        Psr7\rewind_body($res);
        $this->assertEquals(0, $body->tell());
        $body->rewind();
        Psr7\rewind_body($res);
        $this->assertEquals(0, $body->tell());
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testThrowsWhenBodyCannotBeRewound()
    {
        $body = Psr7\stream_for('abc');
        $body->read(1);
        $body = FnStream::decorate($body, [
            'rewind' => function () {
                throw new \RuntimeException('a');
            },
        ]);
        $res = new Psr7\Response(200, [], $body);
        Psr7\rewind_body($res);
    }

    public function testCanModifyRequestWithUri()
    {
        $r1 = new Psr7\Request('GET', 'http://foo.com');
        $r2 = Psr7\modify_request($r1, [
            'uri' => new Psr7\Uri('http://www.foo.com'),
        ]);
        $this->assertEquals('http://www.foo.com', (string)$r2->getUri());
        $this->assertEquals('www.foo.com', (string)$r2->getHeaderLine('host'));
    }

    public function testCanModifyRequestWithUriAndPort()
    {
        $r1 = new Psr7\Request('GET', 'http://foo.com:8000');
        $r2 = Psr7\modify_request($r1, [
            'uri' => new Psr7\Uri('http://www.foo.com:8000'),
        ]);
        $this->assertEquals('http://www.foo.com:8000', (string)$r2->getUri());
        $this->assertEquals('www.foo.com:8000', (string)$r2->getHeaderLine('host'));
    }

    public function testCanModifyRequestWithCaseInsensitiveHeader()
    {
        $r1 = new Psr7\Request('GET', 'http://foo.com', ['User-Agent' => 'foo']);
        $r2 = Psr7\modify_request($r1, ['set_headers' => ['User-agent' => 'bar']]);
        $this->assertEquals('bar', $r2->getHeaderLine('User-Agent'));
        $this->assertEquals('bar', $r2->getHeaderLine('User-agent'));
    }

    public function testReturnsAsIsWhenNoChanges()
    {
        $r1 = new Psr7\Request('GET', 'http://foo.com');
        $r2 = Psr7\modify_request($r1, []);
        $this->assertInstanceOf('GuzzleHttp\Psr7\Request', $r2);

        $r1 = new Psr7\ServerRequest('GET', 'http://foo.com');
        $r2 = Psr7\modify_request($r1, []);
        $this->assertInstanceOf('Psr\Http\Message\ServerRequestInterface', $r2);
    }

    public function testReturnsUriAsIsWhenNoChanges()
    {
        $r1 = new Psr7\Request('GET', 'http://foo.com');
        $r2 = Psr7\modify_request($r1, ['set_headers' => ['foo' => 'bar']]);
        $this->assertNotSame($r1, $r2);
        $this->assertEquals('bar', $r2->getHeaderLine('foo'));
    }

    public function testRemovesHeadersFromMessage()
    {
        $r1 = new Psr7\Request('GET', 'http://foo.com', ['foo' => 'bar']);
        $r2 = Psr7\modify_request($r1, ['remove_headers' => ['foo']]);
        $this->assertNotSame($r1, $r2);
        $this->assertFalse($r2->hasHeader('foo'));
    }

    public function testAddsQueryToUri()
    {
        $r1 = new Psr7\Request('GET', 'http://foo.com');
        $r2 = Psr7\modify_request($r1, ['query' => 'foo=bar']);
        $this->assertNotSame($r1, $r2);
        $this->assertEquals('foo=bar', $r2->getUri()->getQuery());
    }

    public function testModifyRequestKeepInstanceOfRequest()
    {
        $r1 = new Psr7\Request('GET', 'http://foo.com');
        $r2 = Psr7\modify_request($r1, ['remove_headers' => ['non-existent']]);
        $this->assertInstanceOf('GuzzleHttp\Psr7\Request', $r2);

        $r1 = new Psr7\ServerRequest('GET', 'http://foo.com');
        $r2 = Psr7\modify_request($r1, ['remove_headers' => ['non-existent']]);
        $this->assertInstanceOf('Psr\Http\Message\ServerRequestInterface', $r2);
    }

    public function testMessageBodySummaryWithSmallBody()
    {
        $message = new Psr7\Response(200, [], 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.');
        $this->assertEquals('Lorem ipsum dolor sit amet, consectetur adipiscing elit.', Psr7\get_message_body_summary($message));
    }

    public function testMessageBodySummaryWithLargeBody()
    {
        $message = new Psr7\Response(200, [], 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.');
        $this->assertEquals('Lorem ipsu (truncated...)', Psr7\get_message_body_summary($message, 10));
    }

    public function testMessageBodySummaryWithSpecialUTF8Characters()
    {
        $message = new Psr7\Response(200, [], '’é€௵ဪ‱');
        self::assertEquals('’é€௵ဪ‱', Psr7\get_message_body_summary($message));
    }

    public function testMessageBodySummaryWithEmptyBody()
    {
        $message = new Psr7\Response(200, [], '');
        $this->assertNull(Psr7\get_message_body_summary($message));
    }

    public function testGetResponseBodySummaryOfNonReadableStream()
    {
        $this->assertNull(Psr7\get_message_body_summary(new Psr7\Response(500, [], new ReadSeekOnlyStream())));
    }

    public function testModifyServerRequestWithUploadedFiles()
    {
        $request = new Psr7\ServerRequest('GET', 'http://example.com/bla');
        $file = new Psr7\UploadedFile('Test', 100, \UPLOAD_ERR_OK);
        $request = $request->withUploadedFiles([$file]);

        /** @var Psr7\ServerRequest $modifiedRequest */
        $modifiedRequest = Psr7\modify_request($request, ['set_headers' => ['foo' => 'bar']]);

        $this->assertCount(1, $modifiedRequest->getUploadedFiles());

        $files = $modifiedRequest->getUploadedFiles();
        $this->assertInstanceOf('GuzzleHttp\Psr7\UploadedFile', $files[0]);
    }

    public function testModifyServerRequestWithCookies()
    {
        $request = (new Psr7\ServerRequest('GET', 'http://example.com/bla'))
            ->withCookieParams(['name' => 'value']);

        /** @var Psr7\ServerRequest $modifiedRequest */
        $modifiedRequest = Psr7\modify_request($request, ['set_headers' => ['foo' => 'bar']]);

        $this->assertEquals(['name' => 'value'], $modifiedRequest->getCookieParams());
    }

    public function testModifyServerRequestParsedBody()
    {
        $request = (new Psr7\ServerRequest('GET', 'http://example.com/bla'))
            ->withParsedBody(['name' => 'value']);

        /** @var Psr7\ServerRequest $modifiedRequest */
        $modifiedRequest = Psr7\modify_request($request, ['set_headers' => ['foo' => 'bar']]);

        $this->assertEquals(['name' => 'value'], $modifiedRequest->getParsedBody());
    }

    public function testModifyServerRequestQueryParams()
    {
        $request = (new Psr7\ServerRequest('GET', 'http://example.com/bla'))
            ->withQueryParams(['name' => 'value']);

        /** @var Psr7\ServerRequest $modifiedRequest */
        $modifiedRequest = Psr7\modify_request($request, ['set_headers' => ['foo' => 'bar']]);

        $this->assertEquals(['name' => 'value'], $modifiedRequest->getQueryParams());
    }
}

class HasToString
{
    public function __toString()
    {
        return 'foo';
    }
}

/**
 * convert it to an anonymous class on PHP7
 */
final class ReadSeekOnlyStream extends Stream
{
    public function __construct()
    {
        parent::__construct(fopen('php://memory', 'wb'));
    }

    public function isSeekable()
    {
        return true;
    }

    public function isReadable()
    {
        return false;
    }
}
