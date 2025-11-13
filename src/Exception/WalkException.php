<?php

namespace donatj\P9p2000\Exception;

/**
 * Exception thrown when Twalk operation fails
 */
class WalkException extends ServerErrorException {

	/**
	 * @param array<string> $path
	 */
	public function __construct(
		string $errorMessage,
		private readonly array $path,
		?\Throwable $previous = null,
	) {
		parent::__construct('Walk', $errorMessage, $previous);
	}

	/**
	 * @return array<string>
	 */
	public function getPath() : array {
		return $this->path;
	}
}
