<?php

namespace donatj\P9p2000;

use donatj\P9p2000\Exception\ProtocolException;

/**
 * Helper class for encoding/decoding 9P2000 messages
 */
final class Message {

	private function __construct() { }

	/**
	 * Encode a string with its length prefix (2 bytes)
	 */
	public static function encodeString( string $str ): string {
		return pack('v', strlen($str)) . $str;
	}

	/**
	 * Decode a string with length prefix
	 * Returns: [string, newOffset]
	 *
	 * @throws ProtocolException If string decoding fails
	 * @return array{0: string, 1: int}
	 */
	public static function decodeString( string $data, int $offset ): array {
		if( $offset < 0 || strlen($data) - $offset < 2 ) {
			throw new ProtocolException('Failed to unpack string length');
		}
		$unpacked = unpack('v', substr($data, $offset, 2));
		if( $unpacked === false ) {
			throw new ProtocolException('Failed to unpack string length');
		}
		$len = $unpacked[1];
		if( strlen($data) - $offset - 2 < $len ) {
			throw new ProtocolException('Truncated string');
		}
		$str = substr($data, $offset + 2, $len);

		return [ $str, $offset + 2 + $len ];
	}

	/**
	 * Encode a Qid (13 bytes: type[1] version[4] path[8])
	 */
	public static function encodeQid( int $type, int $version, int $path ): string {
		return pack('CVQ', $type, $version, $path);
	}

	/**
	 * Decode a Qid
	 * Returns: [qid, newOffset]
	 *
	 * @throws ProtocolException If Qid decoding fails
	 * @return array{0: array{type: int, version: int, path: int}, 1: int}
	 */
	public static function decodeQid( string $data, int $offset ): array {
		if( $offset < 0 || strlen($data) - $offset < 13 ) {
			throw new ProtocolException('Failed to unpack Qid');
		}
		$qid = unpack('Ctype/Vversion/Qpath', substr($data, $offset, 13));
		if( $qid === false ) {
			throw new ProtocolException('Failed to unpack Qid');
		}

		/** @var array{type: int, version: int, path: int} $qid */
		return [ $qid, $offset + 13 ];
	}

	/**
	 * Encode a message header (size[4] type[1] tag[2])
	 */
	public static function encodeHeader( MessageType $type, int $tag, string $body ): string {
		$size = 4 + 1 + 2 + strlen($body);

		return pack('VCv', $size, $type->value, $tag) . $body;
	}

	/**
	 * Decode message header
	 *
	 * @throws ProtocolException If header decoding fails
	 * @throws \TypeError If message type has wrong type
	 * @throws \ValueError If message type value is invalid
	 * @return array{size: int, type: MessageType, tag: int, body: string}
	 */
	public static function decodeHeader( string $data ): array {
		if( strlen($data) < 7 ) {
			throw new ProtocolException('Failed to unpack message header');
		}
		$unpacked = unpack('Vsize/Ctype/vtag', substr($data, 0, 7));
		if( $unpacked === false ) {
			throw new ProtocolException('Failed to unpack message header');
		}
		if( $unpacked['size'] < 7 || $unpacked['size'] > strlen($data) ) {
			throw new ProtocolException('Invalid message size');
		}

		return [
			'size' => $unpacked['size'],
			'type' => MessageType::from($unpacked['type']),
			'tag'  => $unpacked['tag'],
			'body' => substr($data, 7, $unpacked['size'] - 7),
		];
	}
}
