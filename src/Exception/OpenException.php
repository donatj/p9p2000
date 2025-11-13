<?php

namespace donatj\P9p2000\Exception;

use donatj\P9p2000\OpenMode;

/**
 * Exception thrown when Topen operation fails
 */
class OpenException extends ServerErrorException {

	public function __construct(
		string $errorMessage,
		private readonly int $fid,
		private readonly OpenMode $mode,
		?\Throwable $previous = null,
	) {
		parent::__construct('Open', $errorMessage, $previous);
	}

	public function getFid() : int {
		return $this->fid;
	}

	public function getMode() : OpenMode {
		return $this->mode;
	}
}
