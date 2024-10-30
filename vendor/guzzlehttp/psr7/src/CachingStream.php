<?php
namespace GuzzleHttp\Psr7;

use Psr\Http\Message\StreamInterface;

/**
 * Stream decorator that can cache previously read bytes from a sequentially
 * read stream.
 */
class CachingStream implements StreamInterface
{
    use StreamDecoratorTrait;

    /** @var StreamInterface Stream being wrapped */
    private $remoteStream;

    /** @var int Number of bytes to skip reading due to a write on the buffer */
    private $skipReadBytes = 0;

    /**
     * We will treat the buffer object as the body of the stream
     *
     * @param StreamInterface $stream Stream to cache
     * @param StreamInterface $target Optionally specify where data is cached
     */
    public function __construct(
        StreamInterface $stream,
        StreamInterface $target = null
    ) {
        $this->remoteStream = $stream;
        $this->stream = $target ?: new Stream(fopen('php://temp', 'r+'));
    }

    public function getSize()
    {
        return max($this->stream->getSize(), $this->remoteStream->getSize());
    }

    public function rewind()
    {
        $this->seek(0);
    }

    public function seek($offset, $whence = SEEK_SET)
    {
        if ($whence == SEEK_SET) {
            $byte = $offset;
        } elseif ($whence == SEEK_CUR) {
            $byte = $offset + $this->tell();
        } elseif ($whence == SEEK_END) {
            $size = $this->remoteStream->getSize();
            if ($size === null) {
                $size = $this->cacheEntireStream();
            }
            $byte = $size + $offset;
        } else {
            throw new \InvalidArgumentException('Invalid whence');
        }

        $diff = $byte - $this->stream->getSize();

        if ($diff > 0) {
            // 更多精品WP资源尽在喵容：miaoroom.com
            // 更多精品WP资源尽在喵容：miaoroom.com
            while ($diff > 0 && !$this->remoteStream->eof()) {
                $this->read($diff);
                $diff = $byte - $this->stream->getSize();
            }
        } else {
            // 更多精品WP资源尽在喵容：miaoroom.com
            $this->stream->seek($byte);
        }
    }

    public function read($length)
    {
        // 更多精品WP资源尽在喵容：miaoroom.com
        $data = $this->stream->read($length);
        $remaining = $length - strlen($data);

        // 更多精品WP资源尽在喵容：miaoroom.com
        if ($remaining) {
            // 更多精品WP资源尽在喵容：miaoroom.com
            // 更多精品WP资源尽在喵容：miaoroom.com
            // 更多精品WP资源尽在喵容：miaoroom.com
            // 更多精品WP资源尽在喵容：miaoroom.com
            $remoteData = $this->remoteStream->read(
                $remaining + $this->skipReadBytes
            );

            if ($this->skipReadBytes) {
                $len = strlen($remoteData);
                $remoteData = substr($remoteData, $this->skipReadBytes);
                $this->skipReadBytes = max(0, $this->skipReadBytes - $len);
            }

            $data .= $remoteData;
            $this->stream->write($remoteData);
        }

        return $data;
    }

    public function write($string)
    {
        // 更多精品WP资源尽在喵容：miaoroom.com
        // 更多精品WP资源尽在喵容：miaoroom.com
        // 更多精品WP资源尽在喵容：miaoroom.com
        // 更多精品WP资源尽在喵容：miaoroom.com
        $overflow = (strlen($string) + $this->tell()) - $this->remoteStream->tell();
        if ($overflow > 0) {
            $this->skipReadBytes += $overflow;
        }

        return $this->stream->write($string);
    }

    public function eof()
    {
        return $this->stream->eof() && $this->remoteStream->eof();
    }

    /**
     * Close both the remote stream and buffer stream
     */
    public function close()
    {
        $this->remoteStream->close() && $this->stream->close();
    }

    private function cacheEntireStream()
    {
        $target = new FnStream(['write' => 'strlen']);
        copy_to_stream($this, $target);

        return $this->tell();
    }
}
