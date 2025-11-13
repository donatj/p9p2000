<?php

namespace donatj\P9p2000\Exception;

/**
 * Exception thrown when Twrite operation fails
 */
class WriteException extends ServerErrorException {

	public function __construct(
		string $errorMessage,
		private readonly int $fid,
		private readonly int $offset,
		private readonly int $dataLength,
		?\Throwable $previous = null,
	) {
		parent::__construct('Write', $errorMessage, $previous);
	}

	public function getFid() : int {
		return $this->fid;
	}

	public function getOffset() : int {
		return $this->offset;
	}

	public function getDataLength() : int {
		return $this->dataLength;
	}
}
