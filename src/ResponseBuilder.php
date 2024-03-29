<?php
declare(strict_types=1);

namespace Elephox\Http;

use Elephox\Files\Contract\File as FileContract;
use Elephox\Files\File;
use Elephox\Mimey\MimeType;
use Elephox\Mimey\MimeTypeInterface;
use Elephox\Stream\EmptyStream;
use Elephox\Stream\StringStream;
use JetBrains\PhpStorm\Language;
use JetBrains\PhpStorm\Pure;
use JsonException;
use LogicException;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;
use Throwable;

/**
 * @psalm-consistent-constructor
 */
class ResponseBuilder extends AbstractMessageBuilder implements Contract\ResponseBuilder
{
	use DerivesContentTypeFromHeaderMap;

	#[Pure]
	public function __construct(
		?string $protocolVersion = null,
		?Contract\HeaderMap $headers = null,
		?StreamInterface $body = null,
		protected ?ResponseCode $responseCode = null,
		protected ?Throwable $exception = null,
	) {
		parent::__construct($protocolVersion, $headers, $body);
	}

	public function responseCode(ResponseCode $responseCode): static
	{
		$this->responseCode = $responseCode;

		return $this;
	}

	public function ok(): static
	{
		return $this->responseCode(ResponseCode::OK);
	}

	public function notFound(): static
	{
		return $this->responseCode(ResponseCode::NotFound);
	}

	public function redirect(string|UriInterface $location, bool $permanent = false, bool $preserveMethod = true): static
	{
		$this->header(HeaderName::Location, (string) $location);

		if ($permanent) {
			if ($preserveMethod) {
				return $this->responseCode(ResponseCode::PermanentRedirect);
			}

			return $this->responseCode(ResponseCode::MovedPermanently);
		}

		if ($preserveMethod) {
			return $this->responseCode(ResponseCode::TemporaryRedirect);
		}

		return $this->responseCode(ResponseCode::Found);
	}

	public function getResponseCode(): ?ResponseCode
	{
		return $this->responseCode;
	}

	public function contentType(?MimeTypeInterface $mimeType): static
	{
		if ($this->headers === null && $mimeType !== null) {
			$this->addedHeader(HeaderName::ContentType, $mimeType->getValue());
		} elseif ($this->headers !== null) {
			$headerSet = $this->headers->containsKey(HeaderName::ContentType);
			if ($headerSet && $mimeType === null) {
				$this->headers->remove(HeaderName::ContentType);
			} elseif ($mimeType !== null) {
				$this->headers->put(HeaderName::ContentType, [$mimeType->getValue()]);
			}
		}

		return $this;
	}

	public function getContentType(): ?MimeTypeInterface
	{
		if ($this->headers === null) {
			return null;
		}

		return $this->getContentTypeFromHeaders($this->headers);
	}

	public function exception(?Throwable $exception, ?ResponseCode $responseCode = ResponseCode::InternalServerError): static
	{
		$this->exception = $exception;

		if ($responseCode) {
			$this->responseCode($responseCode);
		}

		return $this;
	}

	public function getException(): ?Throwable
	{
		return $this->exception;
	}

	public function textBody(#[Language('TEXT')] string $content, ?MimeTypeInterface $mimeType = MimeType::TextPlain): static
	{
		$this->body(new StringStream($content));

		if ($mimeType) {
			$this->contentType($mimeType);
		}

		return $this;
	}

	/**
	 * @throws JsonException
	 *
	 * @param null|MimeTypeInterface $mimeType
	 * @param array|object $data
	 */
	public function jsonBody(array|object $data, ?MimeTypeInterface $mimeType = MimeType::ApplicationJson): static
	{
		$json = json_encode($data, JSON_THROW_ON_ERROR);

		return $this->textBody($json, $mimeType);
	}

	public function htmlBody(#[Language('HTML')] string $content, ?MimeTypeInterface $mimeType = MimeType::TextHtml): static
	{
		return $this->textBody($content, $mimeType);
	}

	public function fileBody(string|FileContract $path, ?MimeTypeInterface $mimeType = null): static
	{
		$this->body(File::openStream($path));

		if ($mimeType === null) {
			if ($path instanceof FileContract) {
				$mimeType = $path->mimeType();
			} else {
				$mimeType = MimeType::ApplicationOctetStream;
			}
		}

		$this->contentType($mimeType);

		return $this;
	}

	public function get(): Contract\Response
	{
		return new Response(
			$this->protocolVersion ?? self::DefaultProtocolVersion,
			$this->headers ?? new HeaderMap(),
			$this->body ?? new EmptyStream(),
			$this->responseCode ?? throw new LogicException('Response code is not set.'),
			$this->exception,
		);
	}
}
