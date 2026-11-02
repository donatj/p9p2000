<?php

use donatj\P9p2000\Exception\ProtocolException;
use donatj\P9p2000\Message;
use donatj\P9p2000\MessageType;
use PHPUnit\Framework\TestCase;

final class MessageTest extends TestCase {

	public function testRejectsTruncatedString(): void {
		$this->expectException(ProtocolException::class);
		Message::decodeString("\x04\x00ab", 0);
	}

	public function testRejectsTruncatedQid(): void {
		$this->expectException(ProtocolException::class);
		Message::decodeQid(str_repeat("\0", 12), 0);
	}

	public function testRejectsTruncatedHeader(): void {
		$this->expectException(ProtocolException::class);
		Message::decodeHeader("\x07\x00");
	}

	public function testRejectsTruncatedMessage(): void {
		$this->expectException(ProtocolException::class);
		Message::decodeHeader(pack('VCv', 8, MessageType::Rversion->value, 1));
	}

	public function testRejectsUnknownMessageType(): void {
		$this->expectException(ProtocolException::class);
		$this->expectExceptionMessage('Unknown message type');
		Message::decodeHeader(pack('VCv', 7, 255, 1));
	}

	public function testDecodesCompleteString(): void {
		$this->assertSame([ 'abc', 5 ], Message::decodeString("\x03\x00abc", 0));
	}
}
