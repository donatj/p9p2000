<?php

namespace donatj\P9p2000\Tests;

use donatj\P9p2000\StreamWrapper;
use PHPUnit\Framework\TestCase;

class StreamWrapperTest extends TestCase {

	private const TEST_HOST = 'localhost';
	private const TEST_PORT = 9999;

	private static function getBaseUrl() : string {
		return '9p://' . self::TEST_HOST . ':' . self::TEST_PORT;
	}

	protected function setUp() : void {
		StreamWrapper::register();
	}

	protected function tearDown() : void {
		StreamWrapper::unregister();
	}

	public function testRegisterAndUnregister() : void {
		$this->assertContains('9p', stream_get_wrappers());

		StreamWrapper::unregister();
		$this->assertNotContains('9p', stream_get_wrappers());

		// Re-register for tearDown
		StreamWrapper::register();
	}

	public function testFileGetContents() : void {
		$testFileName = 'stream-test-' . uniqid('', true) . '.txt';
		$testContent  = 'Stream wrapper test content ' . time();
		$url          = self::getBaseUrl() . '/' . $testFileName;

		// Write using file_put_contents
		$written = file_put_contents($url, $testContent);
		$this->assertEquals(strlen($testContent), $written);

		// Read using file_get_contents
		$content = file_get_contents($url);
		$this->assertEquals($testContent, $content);
	}

	public function testFilePutContents() : void {
		$testFileName = 'put-test-' . uniqid('', true) . '.txt';
		$testContent  = 'Testing file_put_contents';
		$url          = self::getBaseUrl() . '/' . $testFileName;

		$result = file_put_contents($url, $testContent);

		$this->assertIsInt($result);
		$this->assertEquals(strlen($testContent), $result);

		// Verify by reading back
		$content = file_get_contents($url);
		$this->assertEquals($testContent, $content);
	}

	public function testFopenReadMode() : void {
		$testFileName = 'fopen-read-' . uniqid('', true) . '.txt';
		$testContent  = 'Testing fopen read mode';
		$url          = self::getBaseUrl() . '/' . $testFileName;

		// Create the file first
		file_put_contents($url, $testContent);

		// Open for reading
		$handle = fopen($url, 'r');
		$this->assertIsResource($handle);

		$content = fread($handle, 1024);
		$this->assertEquals($testContent, $content);

		fclose($handle);
	}

	public function testFopenWriteMode() : void {
		$testFileName = 'fopen-write-' . uniqid('', true) . '.txt';
		$testContent  = 'Testing fopen write mode';
		$url          = self::getBaseUrl() . '/' . $testFileName;

		$handle = fopen($url, 'w');
		$this->assertIsResource($handle);

		$written = fwrite($handle, $testContent);
		$this->assertEquals(strlen($testContent), $written);

		fclose($handle);

		// Verify
		$content = file_get_contents($url);
		$this->assertEquals($testContent, $content);
	}

	public function testFgets() : void {
		$testFileName = 'fgets-test-' . uniqid('', true) . '.txt';
		$testContent  = "Line 1\nLine 2\nLine 3";
		$url          = self::getBaseUrl() . '/' . $testFileName;

		file_put_contents($url, $testContent);

		$handle = fopen($url, 'r');
		$line1  = fgets($handle); // @phpstan-ignore argument.type

		$this->assertEquals("Line 1\n", $line1);

		fclose($handle); // @phpstan-ignore argument.type
	}

	public function testFtell() : void {
		$testFileName = 'ftell-test-' . uniqid('', true) . '.txt';
		$testContent  = '0123456789';
		$url          = self::getBaseUrl() . '/' . $testFileName;

		file_put_contents($url, $testContent);

		$handle = fopen($url, 'r');

		$this->assertEquals(0, ftell($handle)); // @phpstan-ignore argument.type

		fread($handle, 5); // @phpstan-ignore argument.type
		$this->assertEquals(5, ftell($handle)); // @phpstan-ignore argument.type

		fread($handle, 3); // @phpstan-ignore argument.type
		$this->assertEquals(8, ftell($handle)); // @phpstan-ignore argument.type

		fclose($handle); // @phpstan-ignore argument.type
	}

	public function testFseek() : void {
		$testFileName = 'fseek-test-' . uniqid('', true) . '.txt';
		$testContent  = '0123456789ABCDEFGHIJ';
		$url          = self::getBaseUrl() . '/' . $testFileName;

		file_put_contents($url, $testContent);

		$handle = fopen($url, 'r');

		// Seek to position 5
		$result = fseek($handle, 5); // @phpstan-ignore argument.type
		$this->assertEquals(0, $result); // fseek returns 0 on success

		$data = fread($handle, 5); // @phpstan-ignore argument.type
		$this->assertEquals('56789', $data);

		// Seek relative to current position (currently at 10 after reading 5 bytes)
		fseek($handle, 2, SEEK_CUR); // Now at position 12 // @phpstan-ignore argument.type
		$data = fread($handle, 2); // @phpstan-ignore argument.type
		$this->assertEquals('CD', $data);

		fclose($handle); // @phpstan-ignore argument.type
	}

	public function testMultipleReadsWrites() : void {
		$testFileName = 'multi-rw-' . uniqid('', true) . '.txt';
		$url          = self::getBaseUrl() . '/' . $testFileName;

		$handle = fopen($url, 'w');

		fwrite($handle, 'Hello'); // @phpstan-ignore argument.type
		fwrite($handle, ' '); // @phpstan-ignore argument.type
		fwrite($handle, 'World'); // @phpstan-ignore argument.type

		fclose($handle); // @phpstan-ignore argument.type

		// Read back
		$handle = fopen($url, 'r');

		$part1 = fread($handle, 5); // @phpstan-ignore argument.type
		$part2 = fread($handle, 1); // @phpstan-ignore argument.type
		$part3 = fread($handle, 5); // @phpstan-ignore argument.type

		$this->assertEquals('Hello', $part1);
		$this->assertEquals(' ', $part2);
		$this->assertEquals('World', $part3);

		fclose($handle); // @phpstan-ignore argument.type
	}

	public function testEmptyFile() : void {
		$testFileName = 'empty-stream-' . uniqid('', true) . '.txt';
		$url          = self::getBaseUrl() . '/' . $testFileName;

		file_put_contents($url, '');

		$content = file_get_contents($url);
		$this->assertEquals('', $content);
	}

	public function testLargeContent() : void {
		$testFileName = 'large-' . uniqid('', true) . '.txt';
		$url          = self::getBaseUrl() . '/' . $testFileName;

		// Create 10KB of content
		$testContent = str_repeat('0123456789', 1024);

		file_put_contents($url, $testContent);

		$content = file_get_contents($url);
		$this->assertEquals(strlen($testContent), strlen($content)); // @phpstan-ignore argument.type
		$this->assertEquals($testContent, $content);
	}

	public function testReadNonexistentFile() : void {
		$url = self::getBaseUrl() . '/nonexistent-file-' . uniqid('', true) . '.txt';

		// Suppress warnings for this test
		$content = @file_get_contents($url);

		$this->assertFalse($content);
	}

	public function testCustomProtocolName() : void {
		StreamWrapper::unregister();
		StreamWrapper::register('ninep');

		$this->assertContains('ninep', stream_get_wrappers());

		$testFileName = 'custom-proto-' . uniqid('', true) . '.txt';
		$testContent  = 'Custom protocol test';
		$url          = 'ninep://' . self::TEST_HOST . ':' . self::TEST_PORT . '/' . $testFileName;

		file_put_contents($url, $testContent);
		$content = file_get_contents($url);

		$this->assertEquals($testContent, $content);

		StreamWrapper::unregister('ninep');

		// Re-register 9p for other tests
		StreamWrapper::register();
	}

	public function testUrlWithUsername() : void {
		$testFileName = 'username-test-' . uniqid('', true) . '.txt';
		$testContent  = 'Testing with username in URL';
		$url          = '9p://glenda@' . self::TEST_HOST . ':' . self::TEST_PORT . '/' . $testFileName;

		file_put_contents($url, $testContent);
		$content = file_get_contents($url);

		$this->assertEquals($testContent, $content);
	}

	public function testRootPath() : void {
		// Access root directory
		$url = self::getBaseUrl() . '/';

		$handle = @fopen($url, 'r');

		// Opening root should work (it's a directory)
		$this->assertIsResource($handle);

		fclose($handle);
	}
}
