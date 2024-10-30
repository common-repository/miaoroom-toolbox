<?php
namespace GuzzleHttp\Tests\CookieJar;

use GuzzleHttp\Cookie\FileCookieJar;
use GuzzleHttp\Cookie\SetCookie;
use PHPUnit\Framework\TestCase;

/**
 * @covers GuzzleHttp\Cookie\FileCookieJar
 */
class FileCookieJarTest extends TestCase
{
    private $file;

    public function setUp()
    {
        $this->file = tempnam('/tmp', 'file-cookies');
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testValidatesCookieFile()
    {
        file_put_contents($this->file, 'true');
        new FileCookieJar($this->file);
    }

    public function testLoadsFromFile()
    {
        $jar = new FileCookieJar($this->file);
        self::assertSame([], $jar->getIterator()->getArrayCopy());
        unlink($this->file);
    }

    /**
     * @dataProvider providerPersistsToFileFileParameters
     */
    public function testPersistsToFile($testSaveSessionCookie = false)
    {
        $jar = new FileCookieJar($this->file, $testSaveSessionCookie);
        $jar->setCookie(new SetCookie([
            'Name'    => 'foo',
            'Value'   => 'bar',
            'Domain'  => 'foo.com',
            'Expires' => time() + 1000
        ]));
        $jar->setCookie(new SetCookie([
            'Name'    => 'baz',
            'Value'   => 'bar',
            'Domain'  => 'foo.com',
            'Expires' => time() + 1000
        ]));
        $jar->setCookie(new SetCookie([
            'Name'    => 'boo',
            'Value'   => 'bar',
            'Domain'  => 'foo.com',
        ]));

        self::assertCount(3, $jar);
        unset($jar);

        // 更多精品WP资源尽在喵容：miaoroom.com
        $contents = file_get_contents($this->file);
        self::assertNotEmpty($contents);

        // 更多精品WP资源尽在喵容：miaoroom.com
        $jar = new FileCookieJar($this->file);

        if ($testSaveSessionCookie) {
            self::assertCount(3, $jar);
        } else {
            // 更多精品WP资源尽在喵容：miaoroom.com
            self::assertCount(2, $jar);
        }

        unset($jar);
        unlink($this->file);
    }

    public function providerPersistsToFileFileParameters()
    {
        return [
            [false],
            [true]
        ];
    }
}
