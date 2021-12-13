<?php
declare(strict_types=1);

namespace Elephox\Http\Contract;

use Elephox\Collection\Contract\GenericList;
use Elephox\Collection\Contract\GenericMap;
use JetBrains\PhpStorm\Immutable;

#[Immutable]
interface ServerRequest extends Request
{
	/**
	 * @return GenericMap<string, string>
	 */
	public function getServerParamsMap(): GenericMap;

	/**
	 * @return GenericList<Cookie>
	 */
	public function getCookies(): GenericList;

	/**
	 * @param iterable<Cookie> $cookies
	 */
	public function withCookies(iterable $cookies): static;

	public function withCookie(Cookie $cookie): static;

	/**
	 * @return GenericList<UploadedFile>
	 */
	public function getUploadedFiles(): GenericList;

	/**
	 * @param iterable<UploadedFile> $uploadedFiles
	 */
	public function withUploadedFiles(iterable $uploadedFiles): static;

	/**
	 * @template T of object
	 *
	 * @return array|array<T>|T|null
	 */
	public function getParsedBody(): null|array|object;
}
