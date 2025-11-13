<?php

namespace donatj\P9p2000\Exception;

use donatj\P9p2000\OpenMode;

/**
 * Exception thrown when Tcreate operation fails
 */
class CreateException extends ServerErrorException {

	public function __construct(
		string $errorMessage,
		private readonly string $name,
		private readonly int $permissions,
		private readonly OpenMode $mode,
		?\Throwable $previous = null,
	) {
		parent::__construct('Create', $errorMessage, $previous);
	}

	public function getName() : string {
		return $this->name;
	}

	public function getPermissions() : int {
		return $this->permissions;
	}

	public function getMode() : OpenMode {
		return $this->mode;
	}
}
