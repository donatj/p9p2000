<?php

use donatj\P9p2000\Client;
use donatj\P9p2000\Exception\ProtocolException;
use PHPUnit\Framework\TestCase;

final class ClientFramingTest extends TestCase {

	public function testOversizedResponseClosesConnection(): void {
		$sockets = stream_socket_pair(STREAM_PF_UNIX, STREAM_SOCK_STREAM, STREAM_IPPROTO_IP);
		$this->assertIsArray($sockets);
		fwrite($sockets[0], pack('V', 8193));
		fclose($sockets[0]);

		$reflection = new ReflectionClass(Client::class);
		$client = $reflection->newInstanceWithoutConstructor();
		$constructor = $reflection->getConstructor();
		$this->assertNotNull($constructor);
		$constructor->invoke($client, $sockets[1], 8192);

		$this->expectException(ProtocolException::class);
		$this->expectExceptionMessage('Invalid message size');

		try {
			$reflection->getMethod('readMessage')->invoke($client);
		} finally {
			$this->assertFalse(is_resource($sockets[1]));
		}
	}
}
