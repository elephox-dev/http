<?php
declare(strict_types=1);

namespace Elephox\Http\PSR7;

use Elephox\Http\Url;
use Http\Psr7Test\UriIntegrationTest;
use Psr\Http\Message\UriInterface;

/**
 * @covers \Elephox\Http\Url
 * @covers \Elephox\Http\UrlBuilder
 * @covers \Elephox\Http\UrlScheme
 * @covers \Elephox\OOR\Casing
 * @covers \Elephox\Collection\ArrayMap
 * @covers \Elephox\Http\QueryMap
 *
 * @internal
 */
final class UriTest extends UriIntegrationTest
{
	protected $skippedTests = [
		'testWithSchemeInvalidArguments' => 'Type coercion prevents tests from failing',
	];

	public function createUri($uri): UriInterface|Url
	{
		return Url::fromString($uri);
	}
}
