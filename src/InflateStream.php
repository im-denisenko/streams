<?php
namespace GuzzleHttp\Stream;

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

    const FLAG_FTEXT = 0b00000001;
    const FLAG_FHCRC = 0b00000010;
    const FLAG_FEXTRA = 0b00000100;
    const FLAG_FNAME = 0b00001000;
    const FLAG_FCOMMENT = 0b00010000;

    public function __construct(StreamInterface $stream)
    {
        $skip = 10;

        $header = str_split(bin2hex($stream->read(10)), 2);
        $flags = hexdec($header[3]);

        if ($flags & static::FLAG_FEXTRA) {
            $xlen = bindec($stream->read(2));
            $skip += $xlen;
            $stream->read($xlen);
        }

        if ($flags & static::FLAG_FNAME) {
            do {
                ++$skip;
            } while ($stream->read(1) !== "\0");
        }

        if ($flags & static::FLAG_FCOMMENT) {
            do {
                ++$skip;
            } while ($stream->read(1) !== "\0");
        }

        if ($flags & static::FLAG_FHCRC) {
            $skip += 2;
            $stream->read(2);
        }

        $stream = new LimitStream($stream, -1, $skip);
        $resource = GuzzleStreamWrapper::getResource($stream);
        stream_filter_append($resource, 'zlib.inflate', STREAM_FILTER_READ);
        $this->stream = new Stream($resource);
    }
}
