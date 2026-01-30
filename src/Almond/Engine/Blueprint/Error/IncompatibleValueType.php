<?php

namespace Walnut\Lang\Almond\Engine\Blueprint\Error;

use Walnut\Lang\Almond\Engine\Blueprint\Type\DataType;
use Walnut\Lang\Almond\Engine\Blueprint\Type\OpenType;
use Walnut\Lang\Almond\Engine\Blueprint\Type\SealedType;
use Walnut\Lang\Almond\Engine\Blueprint\Value\Value;

final class IncompatibleValueType extends EngineException {
	public function __construct(
		public readonly DataType|OpenType|SealedType $userlandType,
		public readonly Value $value,
	) {
		parent::__construct(
			sprintf(
				'The value "%s" is not compatible with the expected value type "%s" of the type "%s"',
				$value,
				$userlandType->valueType,
				$userlandType,
			)
		);
	}

	public static function of(
		DataType|OpenType|SealedType $userlandType,
		Value              $value,
	): never {
		throw new self($userlandType, $value);
	}
}