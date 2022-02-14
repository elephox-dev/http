<?php
declare(strict_types=1);

namespace Elephox\Http;

use Elephox\Http\Contract\UploadedFileMap as UploadedFileMapContract;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Elephox\Http\UploadedFileMap
 * @covers \Elephox\Http\UploadedFile
 * @covers \Elephox\Collection\ArrayMap
 * @covers \Elephox\Files\File
 * @covers \Elephox\Stream\ResourceStream
 * @covers \Elephox\Http\UploadError
 * @covers \Elephox\Support\CustomMimeType
 * @uses \Elephox\Collection\IsKeyedEnumerable
 */
class UploadedFileMapTest extends TestCase
{
	public function testFromGlobals(): void
	{
		$tmp = tempnam(sys_get_temp_dir(), 'tmp');
		fclose(fopen($tmp, 'wb+'));

		$map = UploadedFileMap::fromGlobals([
			'test' => [
				'name' => 'test.txt',
				'type' => 'text/plain',
				'size' => 12,
				'tmp_name' => pathinfo($tmp, PATHINFO_BASENAME),
				'full_path' => $tmp,
				'error' => UPLOAD_ERR_OK,
			],
			'custom-mime' => [
				'name' => 'strange.abc',
				'type' => 'application/x-abc',
				'size' => 1,
				'tmp_name' => pathinfo($tmp, PATHINFO_BASENAME),
				'full_path' => $tmp,
				'error' => UPLOAD_ERR_OK,
			],
		]);

		self::assertInstanceOf(UploadedFileMapContract::class, $map);
		self::assertCount(2, $map);
		self::assertInstanceOf(UploadedFile::class, $map->get('test'));
		self::assertInstanceOf(UploadedFile::class, $map->get('custom-mime'));

		unlink($tmp);
	}
}