<?php

use donatj\P9p2000\Client;
use donatj\P9p2000\Exception\ProtocolException;
use donatj\P9p2000\StreamWrapper;
use PHPUnit\Framework\TestCase;

final class ClientFramingTest extends TestCase {

	public function testUnknownResponseTypePropagatesThroughStreamRead(): void {
		$sockets = stream_socket_pair(STREAM_PF_UNIX, STREAM_SOCK_STREAM, STREAM_IPPROTO_IP);
		$this->assertIsArray($sockets);
		fwrite($sockets[0], pack('VCv', 7, 255, 0));

		$reflection = new ReflectionClass(Client::class);
		$client = $reflection->newInstanceWithoutConstructor();
		$constructor = $reflection->getConstructor();
		$this->assertNotNull($constructor);
		$constructor->invoke($client, $sockets[1], 8192);
		$wrapper = new StreamWrapper();
		$wrapperReflection = new ReflectionClass(StreamWrapper::class);
		$wrapperReflection->getProperty('client')->setValue($wrapper, $client);
		$wrapperReflection->getProperty('fid')->setValue($wrapper, 1);

		$this->expectException(ProtocolException::class);

		try {
			$wrapper->stream_read(1);
		} finally {
			fclose($sockets[0]);
		}
	}

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

	public function testIncompleteFramesCloseConnection(): void {
		$script = <<<'PHP'
			require $argv[1];
			$sockets = stream_socket_pair(STREAM_PF_UNIX, STREAM_SOCK_STREAM, STREAM_IPPROTO_IP);
			fwrite($sockets[0], base64_decode($argv[2]));
			fclose($sockets[0]);
			$reflection = new ReflectionClass(donatj\P9p2000\Client::class);
			$client = $reflection->newInstanceWithoutConstructor();
			$reflection->getConstructor()->invoke($client, $sockets[1], 8192);
			try {
			    $reflection->getMethod('readMessage')->invoke($client);
			} catch (donatj\P9p2000\Exception\ProtocolException $e) {
			    exit(is_resource($sockets[1]) ? 2 : 0);
			}
			exit(3);
			PHP;
		foreach( [ "\x0a\x00", pack('V', 10) . "\x65" ] as $frame ) {
			$process = proc_open([ PHP_BINARY, '-r', $script, __DIR__ . '/../vendor/autoload.php', base64_encode($frame) ], [ 0 => [ 'pipe', 'r' ], 1 => [ 'pipe', 'w' ], 2 => [ 'pipe', 'w' ] ], $pipes);
			$this->assertIsResource($process);
			foreach( $pipes as $pipe ) {
				fclose($pipe);
			}
			$deadline = microtime(true) + 5;

			do {
				$status = proc_get_status($process);
				if( !$status['running'] ) {
					break;
				}
				usleep(10000);
			} while( microtime(true) < $deadline );
			if( $status['running'] ) {
				proc_terminate($process, 9);
			}
			proc_close($process);
			$this->assertFalse($status['running'], 'Frame read timed out');
			$this->assertSame(0, $status['exitcode']);
		}
	}

}
