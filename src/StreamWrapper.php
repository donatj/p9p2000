<?php

namespace donatj\P9p2000;

use donatj\P9p2000\Exception\P9p2000Exception;

/**
 * Stream Wrapper for 9P2000 protocol
 * Allows using 9p:// URLs with standard PHP file functions
 *
 * Usage:
 *   StreamWrapper::register();
 *   $content = file_get_contents('9p://localhost:564/path/to/file');
 */
final class StreamWrapper {

	private ?Client $client = null;
	private ?int $fid = null;
	private int $position = 0;

	/**
	 * Register the 9p:// protocol handler
	 */
	public static function register( string $protocol = '9p' ) : bool {
		if( in_array($protocol, stream_get_wrappers()) ) {
			stream_wrapper_unregister($protocol);
		}

		return stream_wrapper_register($protocol, self::class);
	}

	/**
	 * Unregister the protocol handler
	 */
	public static function unregister( string $protocol = '9p' ) : bool {
		return stream_wrapper_unregister($protocol);
	}

	/**
	 * Open a stream
	 */
	public function stream_open( string $path, string $mode, int $options, ?string &$opened_path ) : bool {
		$this->position = 0;

		$parsed = self::parseUrl($path);
		if( !$parsed ) {
			return false;
		}

		try {
			$this->client = Client::connect($parsed['host'], $parsed['port']);
			$this->client->version();

			$rootFid = $this->client->attach($parsed['user'] ?? 'nobody', '');

			$pathComponents = array_filter(explode('/', $parsed['path']), fn( $x ) => $x !== '');

			if( !empty($pathComponents) ) {
				// Try to walk to existing file first
				try {
					$this->fid = $this->client->walk($rootFid, $pathComponents);
					$openMode  = $this->phpModeToP9Mode($mode);
					$this->client->open($this->fid, $openMode);
				} catch( P9p2000Exception $e ) {
					// If walk failed and mode is write, try to create the file
					if( str_contains($mode, 'w') || str_contains($mode, 'a') ) {
						$fileName = array_pop($pathComponents);
						if( !empty($pathComponents) ) {
							$parentFid = $this->client->walk($rootFid, $pathComponents);
						} else {
							$parentFid = $this->client->walk($rootFid, []);
						}

						$openMode = $this->phpModeToP9Mode($mode);
						$this->client->create($parentFid, $fileName, 0666, $openMode);
						$this->fid = $parentFid; // create leaves fid pointing to new file
					} else {
						throw $e;
					}
				}
				$this->client->clunk($rootFid);
			} else {
				$this->fid = $rootFid;
				$openMode  = $this->phpModeToP9Mode($mode);
				$this->client->open($this->fid, $openMode);
			}

			return true;
		} catch( P9p2000Exception|\TypeError|\ValueError $e ) {
			if( $options & STREAM_REPORT_ERRORS ) {
				trigger_error($e->getMessage(), E_USER_WARNING);
			}

			return false;
		}
	}

	/**
	 * Read from stream
	 */
	public function stream_read( int $count ) : string {
		if( !$this->client || $this->fid === null ) {
			return '';
		}

		try {
			$data           = $this->client->read($this->fid, $this->position, $count);
			$this->position += strlen($data);

			return $data;
		} catch( P9p2000Exception|\TypeError|\ValueError $e ) {
			return '';
		}
	}

	/**
	 * Write to stream
	 */
	public function stream_write( string $data ) : int {
		if( !$this->client || $this->fid === null ) {
			return 0;
		}

		try {
			$written        = $this->client->write($this->fid, $this->position, $data);
			$this->position += $written;

			return $written;
		} catch( P9p2000Exception|\TypeError|\ValueError $e ) {
			return 0;
		}
	}

	/**
	 * Tell position in stream
	 */
	public function stream_tell() : int {
		return $this->position;
	}

	/**
	 * Check if at end of stream
	 */
	public function stream_eof() : bool {
		return false; // 9P doesn't have a clear EOF concept
	}

	/**
	 * Seek to position
	 */
	public function stream_seek( int $offset, int $whence = SEEK_SET ) : bool {
		return match ($whence) {
			SEEK_SET => (($this->position = $offset) || true),
			SEEK_CUR => (($this->position += $offset) || true),
			SEEK_END => false, // Can't easily determine file size in 9P without stat
			default  => false,
		};
	}

	/**
	 * Get stream statistics
	 *
	 * @return array<int|string, int>|false
	 */
	public function stream_stat() : array|false {
		if( !$this->client || $this->fid === null ) {
			return false;
		}

		try {
			$stat = $this->client->stat($this->fid);

			// Convert 9P stat to PHP stat format
			// PHP expects both numeric and string keys
			$phpStat = [
				'dev'     => $stat['dev'],
				'ino'     => $stat['qid']['path'],
				'mode'    => $stat['mode'],
				'nlink'   => 1,
				'uid'     => 0, // 9P uses string UIDs, PHP expects numeric
				'gid'     => 0,
				'rdev'    => 0,
				'size'    => $stat['length'],
				'atime'   => $stat['atime'],
				'mtime'   => $stat['mtime'],
				'ctime'   => $stat['mtime'], // 9P doesn't have ctime
				'blksize' => -1,
				'blocks'  => -1,
			];

			// PHP stat() returns both numeric and string keys
			return array_merge($phpStat, array_values($phpStat));
		} catch( P9p2000Exception|\TypeError|\ValueError $e ) {
			return false;
		}
	}

	/**
	 * Close stream
	 */
	public function stream_close() : void {
		if( $this->client && $this->fid !== null ) {
			try {
				$this->client->clunk($this->fid);
			} catch( P9p2000Exception|\TypeError|\ValueError $e ) {
				// Ignore errors on close
			}
		}

		if( $this->client ) {
			$this->client->close();
		}
	}

	/**
	 * Parse 9p:// URL
	 *
	 * @return array{host: string, port: int, path: string, user: string|null}|null
	 */
	private static function parseUrl( string $url ) : ?array {
		$parsed = parse_url($url);
		if( !$parsed || !isset($parsed['host']) ) {
			return null;
		}

		return [
			'host' => $parsed['host'],
			'port' => $parsed['port'] ?? 564, // Default 9P port
			'path' => $parsed['path'] ?? '/',
			'user' => $parsed['user'] ?? null,
		];
	}

	/**
	 * Convert PHP fopen mode to 9P open mode
	 */
	private function phpModeToP9Mode( string $mode ) : OpenMode {
		$mode     = strtolower($mode);
		$baseMode = $mode[0] ?? 'r';
		$hasPlus  = str_contains($mode, '+');

		return match ($baseMode) {
			'r'     => $hasPlus ? OpenMode::ORDWR : OpenMode::OREAD,
			'w'     => $hasPlus ? OpenMode::ORDWR : OpenMode::OWRITE,
			'a'     => $hasPlus ? OpenMode::ORDWR : OpenMode::OWRITE,
			default => OpenMode::OREAD,
		};
	}
}
