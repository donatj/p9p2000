<?php

namespace donatj\P9p2000\Exception;

/**
 * Exception thrown when Tclunk operation fails
 */
class ClunkException extends ServerErrorException {

	public function __construct(
		string $errorMessage,
		private readonly int $fid,
		?\Throwable $previous = null,
	) {
		parent::__construct('Clunk', $errorMessage, $previous);
	}

	public function getFid() : int {
		return $this->fid;
	}
}
