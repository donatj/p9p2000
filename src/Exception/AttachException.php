<?php

namespace donatj\P9p2000\Exception;

/**
 * Exception thrown when Tattach operation fails
 */
class AttachException extends ServerErrorException {

	public function __construct(
		string $errorMessage,
		private readonly string $username,
		private readonly string $aname,
		?\Throwable $previous = null,
	) {
		parent::__construct('Attach', $errorMessage, $previous);
	}

	public function getUsername() : string {
		return $this->username;
	}

	public function getAname() : string {
		return $this->aname;
	}
}
