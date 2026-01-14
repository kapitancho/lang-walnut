<?php

namespace Walnut\Lang\Blueprint\Code\NativeCode\Hydrator;

use RuntimeException;
use Walnut\Lang\Blueprint\Value\Value;

final class HydrationException extends RuntimeException {
	public function __construct(
		public readonly Value $value,
		public readonly string $hydrationPath,
		public readonly string $errorMessage
	) {
		parent::__construct();
	}
}