<?php

namespace donatj\P9p2000\Exception;

/**
 * Exception thrown when Tread operation fails
 */
class ReadException extends ServerErrorException {

	public function __construct(
		string $errorMessage,
		private readonly int $fid,
		private readonly int $offset,
		private readonly int $count,
		?\Throwable $previous = null,
	) {
		parent::__construct('Read', $errorMessage, $previous);
	}

	public function getFid() : int {
		return $this->fid;
	}

	public function getOffset() : int {
		return $this->offset;
	}

	public function getCount() : int {
		return $this->count;
	}
}
