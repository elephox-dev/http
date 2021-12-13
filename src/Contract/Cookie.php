<?php
declare(strict_types=1);

namespace Elephox\Http\Contract;

use DateTime;
use Elephox\Http\CookieSameSite;
use JetBrains\PhpStorm\Pure;
use Stringable;

interface Cookie extends Stringable
{
	public function setName(string $name): void;

	#[Pure] public function getName(): string;

	public function setValue(?string $value): void;

	#[Pure] public function getValue(): ?string;

	public function setExpires(?DateTime $expires): void;

	#[Pure] public function getExpires(): ?DateTime;

	public function setPath(?string $path): void;

	#[Pure] public function getPath(): ?string;

	public function setDomain(?string $domain): void;

	#[Pure] public function getDomain(): ?string;

	public function setSecure(bool $secure): void;

	#[Pure] public function isSecure(): bool;

	public function setHttpOnly(bool $httpOnly): void;

	#[Pure] public function isHttpOnly(): bool;

	public function setSameSite(?CookieSameSite $sameSite): void;

	#[Pure] public function getSameSite(): ?CookieSameSite;

	public function setMaxAge(?int $maxAge): void;

	#[Pure] public function getMaxAge(): ?int;
}
