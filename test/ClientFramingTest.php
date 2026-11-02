<?php

use donatj\P9p2000\Client;
use donatj\P9p2000\Exception\ProtocolException;
use PHPUnit\Framework\TestCase;

final class ClientFramingTest extends TestCase {

	public function testTruncatedResponseDoesNotHang(): void {
		$sockets = stream_socket_pair(STREAM_PF_UNIX, STREAM_SOCK_STREAM, STREAM_IPPROTO_IP);
		$this->assertIsArray($sockets);
		fwrite($sockets[0], pack('V', 10) . "\x65");
		fclose($sockets[0]);

		$reflection = new ReflectionClass(Client::class);
		$client = $reflection->newInstanceWithoutConstructor();
		$constructor = $reflection->getConstructor();
		$this->assertNotNull($constructor);
		$constructor->invoke($client, $sockets[1], 8192);

		$this->expectException(ProtocolException::class);
		$reflection->getMethod('readMessage')->invoke($client);
	}
}
