<?php
declare(strict_types=1);

namespace Elephox\Http;

use Elephox\Collection\ArrayMap;
use Elephox\Collection\OffsetNotFoundException;
use Elephox\OOR\Casing;

/**
 * @extends ArrayMap<string, string|list<string>>
 */
class HeaderMap extends ArrayMap implements Contract\HeaderMap
{
	/**
	 * @param array<string, string|list<string>>|null $server
	 */
	public static function fromGlobals(?array $server = null): Contract\HeaderMap
	{
		$server ??= $_SERVER;

		$map = new self();

		/**
		 * @var string|list<string> $value
		 */
		foreach ($server as $name => $value) {
			if (!str_starts_with($name, 'HTTP_')) {
				continue;
			}

			$name = Casing::toHttpHeader(substr($name, 5));

			$map->put($name, $value);
		}

		return $map;
	}

	public static function compareHeaderNames(string $a, string $b): bool
	{
		return $a === $b || Casing::toHttpHeader($a) === Casing::toHttpHeader($b);
	}

	public function containsKey(mixed $key, ?callable $comparer = null): bool
	{
		return parent::containsKey($key, $comparer ?? self::compareHeaderNames(...));
	}

	public function get(mixed $key): mixed
	{
		$validKey = $this->validateKey($key);

		if (!$this->has($validKey)) {
			throw new OffsetNotFoundException($key);
		}

		foreach ($this->items as $k => $v) {
			if (self::compareHeaderNames($k, $validKey)) {
				return $v;
			}
		}

		return null;
	}

	public function put(mixed $key, mixed $value): bool
	{
		$validKey = $this->validateKey($key);

		$existed = $this->has($validKey);
		if ($existed) {
			foreach (array_keys($this->items) as $k) {
				if (self::compareHeaderNames($k, $validKey)) {
					$this->items[$k] = $value;
				}
			}
		} else {
			$this->items[$key] = $value;
		}

		return $existed;
	}

	public function has(mixed $key): bool
	{
		if (parent::has($key)) {
			return true;
		}

		return $this->containsKey($key);
	}

	public function remove(mixed $key): bool
	{
		$validKey = $this->validateKey($key);

		if (!$this->has($validKey)) {
			return false;
		}

		$anyUnset = false;
		foreach (array_keys($this->items) as $k) {
			if (self::compareHeaderNames($k, $validKey)) {
				unset($this->items[$k]);

				$anyUnset = true;
			}
		}

		return $anyUnset;
	}
}
