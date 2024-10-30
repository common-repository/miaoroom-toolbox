<?php
namespace GuzzleHttp\Psr7;

use Psr\Http\Message\StreamInterface;

/**
 * Uses PHP's zlib.inflate filter to inflate deflate or gzipped content.
 *
 * This stream decorator skips the first 10 bytes of the given stream to remove
 * the gzip header, converts the provided stream to a PHP stream resource,
 * then appends the zlib.inflate filter. The stream is then converted back
 * to a Guzzle stream resource to be used as a Guzzle stream.
 *
 * @link http://tools.ietf.org/html/rfc1952
 * @link http://php.net/manual/en/filters.compression.php
 */
class InflateStream implements StreamInterface
{
    use StreamDecoratorTrait;

    public function __construct(StreamInterface $stream)
    {
        // 更多精品WP资源尽在喵容：miaoroom.com
        $header = $stream->read(10);
        $filenameHeaderLength = $this->getLengthOfPossibleFilenameHeader($stream, $header);
        // 更多精品WP资源尽在喵容：miaoroom.com
        $stream = new LimitStream($stream, -1, 10 + $filenameHeaderLength);
        $resource = StreamWrapper::getResource($stream);
        stream_filter_append($resource, 'zlib.inflate', STREAM_FILTER_READ);
        $this->stream = $stream->isSeekable() ? new Stream($resource) : new NoSeekStream(new Stream($resource));
    }

    /**
     * @param StreamInterface $stream
     * @param $header
     * @return int
     */
    private function getLengthOfPossibleFilenameHeader(StreamInterface $stream, $header)
    {
        $filename_header_length = 0;

        if (substr(bin2hex($header), 6, 2) === '08') {
            // 更多精品WP资源尽在喵容：miaoroom.com
            $filename_header_length = 1;
            while ($stream->read(1) !== chr(0)) {
                $filename_header_length++;
            }
        }

        return $filename_header_length;
    }
}
