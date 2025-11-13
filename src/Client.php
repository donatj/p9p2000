<?php

namespace donatj\P9p2000;

use donatj\P9p2000\Exception\AttachException;
use donatj\P9p2000\Exception\ClunkException;
use donatj\P9p2000\Exception\ConnectionException;
use donatj\P9p2000\Exception\CreateException;
use donatj\P9p2000\Exception\OpenException;
use donatj\P9p2000\Exception\ProtocolException;
use donatj\P9p2000\Exception\ReadException;
use donatj\P9p2000\Exception\StatException;
use donatj\P9p2000\Exception\WalkException;
use donatj\P9p2000\Exception\WriteException;

/**
 * 9P2000 Client
 */
final class Client {

	private int $nextTag = 0;
	private int $nextFid = 0;

	/**
	 * @param resource $socket
	 */
	private function __construct(
		private mixed $socket,
		private int $msize,
	) {
	}

	private bool $closed = false;

	/**
	 * Connect to a 9P2000 server
	 *
	 * @throws ConnectionException If connection to server fails
	 */
	public static function connect( string $host, int $port, int $msize = Protocol::DEFAULT_MSIZE ) : self {
		$socket = @fsockopen($host, $port, $errno, $errstr, 30);
		if( !$socket ) {
			throw new ConnectionException($errstr, $host, $port, $errno);
		}

		stream_set_blocking($socket, true);

		return new self(socket: $socket, msize: $msize);
	}

	/**
	 * Perform version negotiation
	 *
	 * @return array{msize: int, version: string}
	 * @throws ProtocolException If version negotiation fails or response is invalid
	 * @throws \TypeError If message type has wrong type
	 * @throws \ValueError If message type value is invalid
	 */
	public function version( string $version = '9P2000' ) : array {
		$tag  = $this->nextTag++;
		$body = pack('V', $this->msize) . Message::encodeString($version);
		$msg  = Message::encodeHeader(MessageType::Tversion, $tag, $body);

		fwrite($this->socket, $msg);

		$response = $this->readMessage();
		if( $response['type'] !== MessageType::Rversion ) {
			throw new ProtocolException('Expected Rversion response');
		}

		$offset   = 0;
		$unpacked = unpack('V', substr($response['body'], $offset, 4));
		if( $unpacked === false ) {
			throw new ProtocolException('Failed to unpack server msize');
		}
		$serverMsize = $unpacked[1];
		$offset      += 4;
		[ $serverVersion, $offset ] = Message::decodeString($response['body'], $offset);

		$this->msize = min($this->msize, $serverMsize);

		return [ 'msize' => $this->msize, 'version' => $serverVersion ];
	}

	/**
	 * Attach to the file tree
	 *
	 * @throws AttachException If attach operation fails
	 * @throws ProtocolException If response is invalid
	 * @throws \TypeError If message type has wrong type
	 * @throws \ValueError If message type value is invalid
	 */
	public function attach( string $uname, string $aname, int $afid = Protocol::NOFID ) : int {
		$fid = $this->nextFid++;
		$tag = $this->nextTag++;

		$body = pack('VV', $fid, $afid) .
			Message::encodeString($uname) .
			Message::encodeString($aname);

		$msg = Message::encodeHeader(MessageType::Tattach, $tag, $body);
		fwrite($this->socket, $msg);

		$response = $this->readMessage();
		if( $response['type'] === MessageType::Rerror ) {
			throw new AttachException($this->decodeError($response['body']), $uname, $aname);
		}

		return $fid;
	}

	/**
	 * Walk to a path
	 *
	 * @param array<string> $path
	 * @throws WalkException If walk operation fails
	 * @throws ProtocolException If response is invalid
	 * @throws \TypeError If message type has wrong type
	 * @throws \ValueError If message type value is invalid
	 */
	public function walk( int $fid, array $path ) : int {
		$newFid = $this->nextFid++;
		$tag    = $this->nextTag++;

		$body = pack('VVv', $fid, $newFid, count($path));
		foreach( $path as $component ) {
			$body .= Message::encodeString($component);
		}

		$msg = Message::encodeHeader(MessageType::Twalk, $tag, $body);
		fwrite($this->socket, $msg);

		$response = $this->readMessage();
		if( $response['type'] === MessageType::Rerror ) {
			throw new WalkException($this->decodeError($response['body']), $path);
		}

		return $newFid;
	}

	/**
	 * Open a file
	 *
	 * @return array{qid: array{type: int, version: int, path: int}, iounit: int}
	 * @throws OpenException If open operation fails
	 * @throws ProtocolException If response is invalid
	 * @throws \TypeError If message type has wrong type
	 * @throws \ValueError If message type value is invalid
	 */
	public function open( int $fid, OpenMode $mode ) : array {
		$tag  = $this->nextTag++;
		$body = pack('VC', $fid, $mode->value);

		$msg = Message::encodeHeader(MessageType::Topen, $tag, $body);
		fwrite($this->socket, $msg);

		$response = $this->readMessage();
		if( $response['type'] === MessageType::Rerror ) {
			throw new OpenException($this->decodeError($response['body']), $fid, $mode);
		}

		$offset = 0;
		[ $qid, $offset ] = Message::decodeQid($response['body'], $offset);
		$unpacked = unpack('V', substr($response['body'], $offset, 4));
		if( $unpacked === false ) {
			throw new ProtocolException('Failed to unpack iounit');
		}
		$iounit = $unpacked[1];

		return [ 'qid' => $qid, 'iounit' => $iounit ];
	}

	/**
	 * Create a file or directory
	 *
	 * @return array{qid: array{type: int, version: int, path: int}, iounit: int}
	 * @throws CreateException If create operation fails
	 * @throws ProtocolException If response is invalid
	 * @throws \TypeError If message type has wrong type
	 * @throws \ValueError If message type value is invalid
	 */
	public function create( int $fid, string $name, int $perm, OpenMode $mode ) : array {
		$tag  = $this->nextTag++;
		$body = pack('V', $fid) .
			Message::encodeString($name) .
			pack('VC', $perm, $mode->value);

		$msg = Message::encodeHeader(MessageType::Tcreate, $tag, $body);
		fwrite($this->socket, $msg);

		$response = $this->readMessage();
		if( $response['type'] === MessageType::Rerror ) {
			throw new CreateException($this->decodeError($response['body']), $name, $perm, $mode);
		}

		$offset = 0;
		[ $qid, $offset ] = Message::decodeQid($response['body'], $offset);
		$unpacked = unpack('V', substr($response['body'], $offset, 4));
		if( $unpacked === false ) {
			throw new ProtocolException('Failed to unpack iounit');
		}
		$iounit = $unpacked[1];

		return [ 'qid' => $qid, 'iounit' => $iounit ];
	}

	/**
	 * Read from a file
	 *
	 * @throws ReadException If read operation fails
	 * @throws ProtocolException If response is invalid
	 * @throws \TypeError If message type has wrong type
	 * @throws \ValueError If message type value is invalid
	 */
	public function read( int $fid, int $offset, int $count ) : string {
		$tag  = $this->nextTag++;
		$body = pack('VQV', $fid, $offset, $count);

		$msg = Message::encodeHeader(MessageType::Tread, $tag, $body);
		fwrite($this->socket, $msg);

		$response = $this->readMessage();
		if( $response['type'] === MessageType::Rerror ) {
			throw new ReadException($this->decodeError($response['body']), $fid, $offset, $count);
		}

		$unpacked = unpack('V', substr($response['body'], 0, 4));
		if( $unpacked === false ) {
			throw new ProtocolException('Failed to unpack data length');
		}
		$dataLen = $unpacked[1];

		return substr($response['body'], 4, $dataLen);
	}

	/**
	 * Write to a file
	 *
	 * @throws WriteException If write operation fails
	 * @throws ProtocolException If response is invalid
	 * @throws \TypeError If message type has wrong type
	 * @throws \ValueError If message type value is invalid
	 */
	public function write( int $fid, int $offset, string $data ) : int {
		$tag  = $this->nextTag++;
		$body = pack('VQV', $fid, $offset, strlen($data)) . $data;

		$msg = Message::encodeHeader(MessageType::Twrite, $tag, $body);
		fwrite($this->socket, $msg);

		$response = $this->readMessage();
		if( $response['type'] === MessageType::Rerror ) {
			throw new WriteException($this->decodeError($response['body']), $fid, $offset, strlen($data));
		}

		$unpacked = unpack('V', $response['body']);
		if( $unpacked === false ) {
			throw new ProtocolException('Failed to unpack write count');
		}

		return $unpacked[1];
	}

	/**
	 * Get file/directory statistics
	 *
	 * @return array{type: int, dev: int, qid: array{type: int, version: int, path: int}, mode: int, atime: int, mtime: int, length: int, name: string, uid: string, gid: string, muid: string}
	 * @throws StatException If stat operation fails
	 * @throws ProtocolException If response is invalid
	 * @throws \TypeError If message type has wrong type
	 * @throws \ValueError If message type value is invalid
	 */
	public function stat( int $fid ) : array {
		$tag  = $this->nextTag++;
		$body = pack('V', $fid);

		$msg = Message::encodeHeader(MessageType::Tstat, $tag, $body);
		fwrite($this->socket, $msg);

		$response = $this->readMessage();
		if( $response['type'] === MessageType::Rerror ) {
			throw new StatException($this->decodeError($response['body']), $fid);
		}

		// Rstat response contains stat[n]
		// First 2 bytes are the size of the stat structure
		$offset   = 0;
		$unpacked = unpack('v', substr($response['body'], $offset, 2));
		if( $unpacked === false ) {
			throw new ProtocolException('Failed to unpack stat size');
		}
		$statSize = $unpacked[1];
		$offset   += 2;

		// Now decode the stat structure
		// stat: size[2] type[2] dev[4] qid[13] mode[4] atime[4] mtime[4] length[8] name[s] uid[s] gid[s] muid[s]
		$unpacked = unpack('vsize/vtype/Vdev', substr($response['body'], $offset, 8));
		if( $unpacked === false ) {
			throw new ProtocolException('Failed to unpack stat header');
		}
		$stat   = $unpacked;
		$offset += 8;

		// Decode qid
		[ $qid, $offset ] = Message::decodeQid($response['body'], $offset);
		$stat['qid'] = $qid;

		// mode, atime, mtime, length
		$unpacked = unpack('Vmode/Vatime/Vmtime/Qlength', substr($response['body'], $offset, 20));
		if( $unpacked === false ) {
			throw new ProtocolException('Failed to unpack stat times');
		}
		$stat   = array_merge($stat, $unpacked);
		$offset += 20;

		// name, uid, gid, muid (all strings)
		[ $name, $offset ] = Message::decodeString($response['body'], $offset);
		[ $uid, $offset ] = Message::decodeString($response['body'], $offset);
		[ $gid, $offset ] = Message::decodeString($response['body'], $offset);
		[ $muid, $offset ] = Message::decodeString($response['body'], $offset);

		return [
			'type'   => $stat['type'],
			'dev'    => $stat['dev'],
			'qid'    => $qid,
			'mode'   => $stat['mode'],
			'atime'  => $stat['atime'],
			'mtime'  => $stat['mtime'],
			'length' => $stat['length'],
			'name'   => $name,
			'uid'    => $uid,
			'gid'    => $gid,
			'muid'   => $muid,
		];
	}

	/**
	 * Close a fid
	 *
	 * @throws ClunkException If clunk operation fails
	 * @throws ProtocolException If response is invalid
	 * @throws \TypeError If message type has wrong type
	 * @throws \ValueError If message type value is invalid
	 */
	public function clunk( int $fid ) : void {
		$tag  = $this->nextTag++;
		$body = pack('V', $fid);

		$msg = Message::encodeHeader(MessageType::Tclunk, $tag, $body);
		fwrite($this->socket, $msg);

		$response = $this->readMessage();
		if( $response['type'] === MessageType::Rerror ) {
			throw new ClunkException($this->decodeError($response['body']), $fid);
		}
	}

	/**
	 * Read a complete message from the socket
	 *
	 * @return array{size: int, type: MessageType, tag: int, body: string}
	 * @throws ProtocolException If message reading or decoding fails
	 * @throws \TypeError If message type has wrong type
	 * @throws \ValueError If message type value is invalid
	 */
	private function readMessage() : array {
		$sizeData = fread($this->socket, 4);
		if( $sizeData === false || strlen($sizeData) < 4 ) {
			throw new ProtocolException('Failed to read message size');
		}

		$unpacked = unpack('V', $sizeData);
		if( $unpacked === false ) {
			throw new ProtocolException('Failed to unpack message size');
		}
		$size      = $unpacked[1];
		$remaining = $size - 4;

		$data = '';
		while( strlen($data) < $remaining ) {
			$toRead = $remaining - strlen($data);
			if( $toRead < 1 ) {
				break;
			}
			$chunk = fread($this->socket, $toRead);
			if( $chunk === false ) {
				throw new ProtocolException('Failed to read message data');
			}
			$data .= $chunk;
		}

		return Message::decodeHeader($sizeData . $data);
	}

	/**
	 * Decode error message
	 *
	 * @throws ProtocolException If error message decoding fails
	 */
	private function decodeError( string $body ) : string {
		[ $error, ] = Message::decodeString($body, 0);

		return $error;
	}

	/**
	 * Close the connection
	 */
	public function close() : void {
		if( !$this->closed ) {
			fclose($this->socket);
			$this->closed = true;
		}
	}

	public function __destruct() {
		$this->close();
	}
}
