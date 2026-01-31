<?php

namespace Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Error;

use Walnut\Lang\Almond\Engine\Blueprint\Common\EngineException;

final class InvalidArgument extends EngineException {
	private function __construct(
		public readonly string $expectedType,
		public readonly mixed $actualValue,
		string|null $message,
	) {
		parent::__construct(
			$message ?? sprintf(
				'Invalid argument: expected type "%s", got "%s"',
				$this->expectedType,
				get_debug_type($this->actualValue)
			)
		);
	}

	public static function of(
		string $expectedType,
		mixed $actualValue,
		string|null $message = null
	): never {
		throw new self($expectedType, $actualValue, $message);
	}
}