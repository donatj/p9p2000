<?php

namespace donatj\P9p2000\Tests;

use donatj\P9p2000\Client;
use donatj\P9p2000\OpenMode;
use PHPUnit\Framework\TestCase;

class ClientTest extends TestCase {

	private const TEST_HOST = 'localhost';
	private const TEST_PORT = 9999;

	private static function createClient() : Client {
		return Client::connect(self::TEST_HOST, self::TEST_PORT);
	}

	public function testConnect() : void {
		$client = self::createClient();
		$this->assertInstanceOf(Client::class, $client); // @phpstan-ignore method.alreadyNarrowedType
		$client->close();
	}

	public function testVersion() : void {
		$client = self::createClient();

		$result = $client->version();

		$this->assertIsArray($result);
		$this->assertArrayHasKey('msize', $result);
		$this->assertArrayHasKey('version', $result);
		$this->assertIsInt($result['msize']);
		$this->assertIsString($result['version']);
		$this->assertGreaterThan(0, $result['msize']);
		$this->assertEquals('9P2000', $result['version']);

		$client->close();
	}

	public function testAttach() : void {
		$client = self::createClient();
		$client->version();

		$fid = $client->attach('glenda', '');

		$this->assertIsInt($fid);
		$this->assertGreaterThanOrEqual(0, $fid);

		$client->clunk($fid);
		$client->close();
	}

	public function testWalkToRoot() : void {
		$client = self::createClient();
		$client->version();
		$rootFid = $client->attach('glenda', '');

		// Walk with empty path should return a new fid to root
		$newFid = $client->walk($rootFid, []);

		$this->assertIsInt($newFid);
		$this->assertNotEquals($rootFid, $newFid);

		$client->clunk($newFid);
		$client->clunk($rootFid);
		$client->close();
	}

	public function testOpenRootDirectory() : void {
		$client = self::createClient();
		$client->version();
		$rootFid = $client->attach('glenda', '');

		$result = $client->open($rootFid, OpenMode::OREAD);

		$this->assertIsArray($result);
		$this->assertArrayHasKey('qid', $result);
		$this->assertArrayHasKey('iounit', $result);
		$this->assertIsArray($result['qid']);
		$this->assertArrayHasKey('type', $result['qid']);
		$this->assertArrayHasKey('version', $result['qid']);
		$this->assertArrayHasKey('path', $result['qid']);

		$client->clunk($rootFid);
		$client->close();
	}

	public function testWriteAndReadFile() : void {
		$client = self::createClient();
		$client->version();
		$rootFid = $client->attach('glenda', '');

		$testFileName = 'test-' . uniqid() . '.txt';
		$testContent  = 'Hello, 9P2000! ' . time();

		// Clone the root fid for creating the file
		$createFid = $client->walk($rootFid, []);

		// Create the file (0666 permissions)
		$client->create($createFid, $testFileName, 0666, OpenMode::OWRITE);

		// Write to the file (createFid is now pointing to the new file)
		$written = $client->write($createFid, 0, $testContent);
		$this->assertEquals(strlen($testContent), $written);

		$client->clunk($createFid);

		// Now read it back
		$readFid = $client->walk($rootFid, [ $testFileName ]);
		$client->open($readFid, OpenMode::OREAD);

		$data = $client->read($readFid, 0, 1024);
		$this->assertEquals($testContent, $data);

		$client->clunk($readFid);
		$client->clunk($rootFid);
		$client->close();
	}

	public function testReadAtOffset() : void {
		$client = self::createClient();
		$client->version();
		$rootFid = $client->attach('glenda', '');

		$testFileName = 'offset-test-' . uniqid() . '.txt';
		$testContent  = '0123456789ABCDEFGHIJ';

		// Create and write file
		$fileFid = $client->walk($rootFid, []);
		$client->create($fileFid, $testFileName, 0666, OpenMode::OWRITE);
		$client->write($fileFid, 0, $testContent);
		$client->clunk($fileFid);

		// Read from offset
		$readFid = $client->walk($rootFid, [ $testFileName ]);
		$client->open($readFid, OpenMode::OREAD);

		$data = $client->read($readFid, 5, 5);
		$this->assertEquals('56789', $data);

		$client->clunk($readFid);
		$client->clunk($rootFid);
		$client->close();
	}

	public function testMultipleWrites() : void {
		$client = self::createClient();
		$client->version();
		$rootFid = $client->attach('glenda', '');

		$testFileName = 'multi-write-' . uniqid() . '.txt';

		// Create file
		$fileFid = $client->walk($rootFid, []);
		$client->create($fileFid, $testFileName, 0666, OpenMode::OWRITE);

		// Write at different offsets
		$client->write($fileFid, 0, 'Hello');
		$client->write($fileFid, 5, ' ');
		$client->write($fileFid, 6, 'World');

		$client->clunk($fileFid);

		// Read back the complete file
		$readFid = $client->walk($rootFid, [ $testFileName ]);
		$client->open($readFid, OpenMode::OREAD);

		$data = $client->read($readFid, 0, 1024);
		$this->assertEquals('Hello World', $data);

		$client->clunk($readFid);
		$client->clunk($rootFid);
		$client->close();
	}

	public function testReadEmptyFile() : void {
		$client = self::createClient();
		$client->version();
		$rootFid = $client->attach('glenda', '');

		$testFileName = 'empty-' . uniqid() . '.txt';

		// Create empty file
		$fileFid = $client->walk($rootFid, []);
		$client->create($fileFid, $testFileName, 0666, OpenMode::OWRITE);
		$client->clunk($fileFid);

		// Read empty file
		$readFid = $client->walk($rootFid, [ $testFileName ]);
		$client->open($readFid, OpenMode::OREAD);

		$data = $client->read($readFid, 0, 1024);
		$this->assertEquals('', $data);

		$client->clunk($readFid);
		$client->clunk($rootFid);
		$client->close();
	}

	public function testCloseConnection() : void {
		$client = self::createClient();
		$client->version();
		$rootFid = $client->attach('glenda', '');
		$client->clunk($rootFid);

		// Close should be idempotent - no exception thrown
		$client->close();
		$client->close();

		// If we got here without exception, test passed
		$this->expectNotToPerformAssertions();
	}

	public function testInvalidHostThrowsException() : void {
		$this->expectException(\donatj\P9p2000\Exception\ConnectionException::class);
		$this->expectExceptionMessage('Failed to connect');

		Client::connect('invalid-host-that-does-not-exist.local', 9999);
	}
}
