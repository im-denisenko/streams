<?php
namespace GuzzleHttp\Tests\Stream;

use GuzzleHttp\Stream\InflateStream;
use GuzzleHttp\Stream\Stream;

class InflateStreamtest extends \PHPUnit_Framework_TestCase
{
    public function testInflatesStreams()
    {
        $content = gzencode('test');
        $a = Stream::factory($content);
        $b = new InflateStream($a);
        $this->assertEquals('test', (string) $b);
    }

    public function testInflatesWithNameFlag()
    {
        $content = hex2bin('1f8b080808c81c560003666f6f6261722e747874004bcbcf4f4a2c0200951ff69e06000000');
        $a = Stream::factory($content);
        $b = new InflateStream($a);
        $this->assertEquals('foobar', (string) $b);
    }
}
