<?php

namespace donatj\P9p2000\Exception;

/**
 * Exception thrown when Tstat operation fails
 */
class StatException extends ServerErrorException {

	public function __construct(
		string $errorMessage,
		private readonly int $fid,
		?\Throwable $previous = null,
	) {
		parent::__construct('Stat', $errorMessage, $previous);
	}

	public function getFid() : int {
		return $this->fid;
	}
}
