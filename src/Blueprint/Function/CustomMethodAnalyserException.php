<?php

namespace Walnut\Lang\Blueprint\Function;

use LogicException;
use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;

class CustomMethodAnalyserException extends LogicException {

	public function __construct(
		public string $errorLocation,
		public string|AnalyserException $error,
		public CustomMethod $target,
	) {
		parent::__construct(
			is_string($error)
				? $error
				: $error->message
		);
	}

	public string $fullMessage { get => sprintf("%s : %s", $this->errorLocation, $this->error); }

	public function __toString(): string {
		return $this->fullMessage;
	}

}