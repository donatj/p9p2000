<?php

namespace donatj\P9p2000\Exception;

/**
 * Exception thrown when the 9P2000 server returns an Rerror response
 */
class ServerErrorException extends P9p2000Exception {

	public function __construct(
		private readonly string $operation,
		private readonly string $errorMessage,
		?\Throwable $previous = null,
	) {
		parent::__construct(
			sprintf('%s failed: %s', $operation, $errorMessage),
			0,
			$previous,
		);
	}

	public function getOperation() : string {
		return $this->operation;
	}

	public function getErrorMessage() : string {
		return $this->errorMessage;
	}
}
