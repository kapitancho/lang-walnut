<?php

namespace Walnut\Lang\Almond\Engine\Blueprint\Code\Value;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\DataType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\OpenType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\SealedType;
use Walnut\Lang\Almond\Engine\Blueprint\Common\EngineException;

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