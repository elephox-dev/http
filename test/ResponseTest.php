<?php
declare(strict_types=1);

namespace Elephox\Http;

use Elephox\Collection\ArrayList;
use Elephox\Stream\StringStream;
use Elephox\Support\MimeType;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Elephox\Http\Response
 * @covers \Elephox\Collection\ArrayList
 * @covers \Elephox\Http\HeaderMap
 * @covers \Elephox\Collection\ArrayMap
 * @covers \Elephox\Collection\ObjectMap
 * @covers \Elephox\Http\ResponseHeaderMap
 * @covers \Elephox\Text\Regex
 * @covers \Elephox\Http\ResponseCode
 * @covers \Elephox\Http\HeaderName
 * @covers \Elephox\Support\MimeType
 * @covers \Elephox\Http\CustomResponseCode
 * @covers \Elephox\Http\InvalidResponseCodeMessageException
 * @covers \Elephox\Stream\StringStream
 * @covers \Elephox\Stream\EmptyStream
 * @covers \Elephox\Http\AbstractMessage
 */
class ResponseTest extends TestCase
{
	public function testConstructor(): void
	{
		$response = new Response();
		self::assertEquals(200, $response->getResponseCode()->getCode());
		self::assertEquals('OK', $response->getResponseCode()->getMessage());
		self::assertEmpty($response->getHeaderMap()->asArray());
	}

	public function testFromString(): void
	{
		$response = Response::fromString("HTTP/1.1 200 OK\n\n");
		self::assertEquals(ResponseCode::OK, $response->getResponseCode());
		self::assertEquals('1.1', $response->getProtocolVersion());
		self::assertEmpty($response->getHeaderMap()->asArray());

		$responseNotFound = $response->withResponseCode(ResponseCode::NotFound);

		self::assertEquals(ResponseCode::NotFound, $responseNotFound->getResponseCode());
	}

	public function testFromStringInvalidFormat(): void
	{
		$this->expectException(InvalidArgumentException::class);

		Response::fromString("No HTTP Message");
	}

	public function testWithResponseCode(): void
	{
		$response = new Response();
		$responseNotFound = $response->withResponseCode(ResponseCode::NotFound);

		self::assertEquals(ResponseCode::NotFound, $responseNotFound->getResponseCode());
		self::assertNotSame($response->getBody(), $responseNotFound->getBody());
	}

	public function testCustomResponseCode(): void
	{
		$response = Response::fromString("HTTP/1.1 420 Blaze it\n\n");
		self::assertEquals(420, $response->getResponseCode()->getCode());
		self::assertEquals("Blaze it", $response->getResponseCode()->getMessage());
	}

	public function testInvalidCustomResponseCodeMessage(): void
	{
		$this->expectException(InvalidResponseCodeMessageException::class);

		Response::fromString("HTTP/1.1 999  \n\n");
	}

	public function testInvalidCustomResponseCode(): void
	{
		$this->expectException(InvalidArgumentException::class);

		Response::fromString("HTTP/1.1  test\n\n");
	}

	public function testRequestOnlyHeader(): void
	{
		$headers = new HeaderMap([
			HeaderName::Host
		], [
			ArrayList::fromValue('localhost')
		]);

		$this->expectException(InvalidArgumentException::class);

		new Response(headers: $headers->asResponseHeaders());
	}

	public function testWithProtocolVersion(): void
	{
		$response = new Response();
		$responseWithProtocolVersion = $response->withProtocolVersion('1.0');

		self::assertEquals('1.0', $responseWithProtocolVersion->getProtocolVersion());
	}

	public function testWithBody(): void
	{
		$response = new Response();
		$responseWithBody = $response->withBody(new StringStream('test'));

		self::assertNotSame($response->getBody(), $responseWithBody->getBody());
	}

	public function testGetContentType(): void
	{
		$response = new Response();

		self::assertNull($response->getContentType());

		$emptyContentType = ResponseHeaderMap::fromIterable(['Content-Type' => []]);
		$responseWithEmptyContentType = $response->withHeaderMap($emptyContentType);

		self::assertNull($responseWithEmptyContentType->getContentType());

		$contentType = ResponseHeaderMap::fromIterable(['Content-Type' => ['text/html']]);
		$responseWithContentType = $response->withHeaderMap($contentType);

		self::assertEquals(MimeType::Texthtml, $responseWithContentType->getContentType());
	}

	public function testWithContentType(): void
	{
		$response = new Response();
		$responseWithContentType = $response->withContentType(MimeType::Texthtml);

		self::assertEquals(MimeType::Texthtml, $responseWithContentType->getContentType());

		$headers = $responseWithContentType->getHeaderMap();

		self::assertTrue($headers->has(HeaderName::ContentType));
		self::assertEquals(['text/html'], $headers->get(HeaderName::ContentType)->asArray());

		$responseWithoutContentType = $responseWithContentType->withContentType(null);

		self::assertNull($responseWithoutContentType->getContentType());
		self::assertFalse($responseWithoutContentType->getHeaderMap()->has(HeaderName::ContentType));
	}
}
