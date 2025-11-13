<?php

namespace donatj\P9p2000\Exception;

/**
 * Exception thrown when connection to 9P2000 server fails
 */
class ConnectionException extends P9p2000Exception {

	public function __construct(
		string $message,
		private readonly string $host,
		private readonly int $port,
		private readonly int $errorCode,
		?\Throwable $previous = null,
	) {
		parent::__construct(
			sprintf('Failed to connect to %s:%d: %s (errno %d)', $host, $port, $message, $errorCode),
			0,
			$previous,
		);
	}

	public function getHost() : string {
		return $this->host;
	}

	public function getPort() : int {
		return $this->port;
	}

	public function getErrorCode() : int {
		return $this->errorCode;
	}
}
