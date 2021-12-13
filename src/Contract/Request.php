<?php
declare(strict_types=1);

namespace Elephox\Http\Contract;

use JetBrains\PhpStorm\Immutable;
use JetBrains\PhpStorm\Pure;

#[Immutable]
interface Request extends Message
{
	public function getHeaderMap(): RequestHeaderMap;

	#[Pure] public function getRequestMethod(): RequestMethod;

	public function withRequestMethod(RequestMethod $method): static;

	public function withUrl(Url $url, bool $preserveHost = false): static;

	#[Pure] public function getUrl(): Url;
}
