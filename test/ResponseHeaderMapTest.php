<?php
declare(strict_types=1);

namespace Elephox\Http;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Elephox\Http\ResponseHeaderMap
 * @covers \Elephox\Collection\ArrayList
 * @covers \Elephox\Text\Regex
 * @covers \Elephox\Collection\ArrayMap
 * @covers \Elephox\Collection\ObjectMap
 * @covers \Elephox\Http\HeaderMap
 * @covers \Elephox\Http\InvalidHeaderNameTypeException
 * @covers \Elephox\Collection\KeyValuePair
 * @covers \Elephox\Http\InvalidHeaderNameException
 * @covers \Elephox\Http\CustomHeaderName
 * @covers \Elephox\Http\HeaderName
 * @covers \Elephox\Http\InvalidHeaderTypeException
 */
class ResponseHeaderMapTest extends TestCase
{
	public function testVariousHeaderNames(): void
	{
		$map = ResponseHeaderMap::fromArray([
			'Host' => 'localhost',
			'x-custom' => 'custom',
			'Set-Cookie' => [
				'name' => 'value',
				'name2' => 'value2',
			],
			'Content-Type' => ['text/html'],
		]);

		self::assertEquals(['localhost'], $map->get(HeaderName::Host)->asArray());
		self::assertEquals(['custom'], $map->get('x-custom')->asArray());
		self::assertEquals([
			'value',
			'value2',
		], $map->get(HeaderName::SetCookie)->asArray());

		$map->put('Set-Cookie', 'test');

		self::assertEquals(['test'], $map->get(HeaderName::SetCookie)->asArray());
	}

	public function testFromString(): void
	{
		$map = ResponseHeaderMap::fromString("Host: localhost\r\nContent-Type:  text/html \r\nX-Custom: test:value\r\n\r\n");

		self::assertTrue($map->has(HeaderName::Host));
		self::assertTrue($map->has(HeaderName::ContentType));
		self::assertTrue($map->has('X-Custom'));
		self::assertEquals(['localhost'], $map->get(HeaderName::Host)->asArray());
		self::assertEquals(['text/html'], $map->get(HeaderName::ContentType)->asArray());
		self::assertEquals(['test:value'], $map->get("X-Custom")->asArray());
	}

	public function testInvalidHeaderRow(): void
	{
		$this->expectException(InvalidArgumentException::class);

		ResponseHeaderMap::fromString("invalidheader");
	}

	public function testInvalidHeaderName(): void
	{
		$this->expectException(InvalidArgumentException::class);

		ResponseHeaderMap::fromString(": no name");
	}

	public function testInvalidHeaderType(): void
	{
		$this->expectException(InvalidHeaderNameTypeException::class);

		ResponseHeaderMap::fromArray([
			123 => "test"
		]);
	}

	public function testInvalidHeaderValueTypeFromArray(): void
	{
		$this->expectException(InvalidHeaderTypeException::class);

		ResponseHeaderMap::fromArray([
			'Host' => 123
		]);
	}

	public function testInvalidHeaderValueTypePut(): void
	{
		$this->expectException(InvalidHeaderTypeException::class);

		$map = ResponseHeaderMap::fromArray([
			'Host' => 'localhost'
		]);

		$map->put('Host', 123);
	}
}
