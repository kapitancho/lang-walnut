<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Random;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\AtomType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\IntegerType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\RecordType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\AtomValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\IntegerValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\RecordValue;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Range\MinusInfinity;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Range\NumberInterval;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Range\PlusInfinity;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationErrorType;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationFailure;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\NativeMethod;

/** @extends NativeMethod<AtomType, RecordType, AtomValue, RecordValue> */
final readonly class Integer extends NativeMethod {

	protected function validateParameterType(Type $parameterType, Type $targetType): null|string {
		/** @var RecordType $parameterType */
		$fromType = $parameterType->types['min'] ?? null;
		$toType = $parameterType->types['max'] ?? null;
		return ($fromType instanceof IntegerType) && ($toType instanceof IntegerType) ?
			null : sprintf(
				"Expected a parameter type [min: Integer, max: Integer], but got %s",
				$parameterType
			);
	}

	protected function getValidator(): callable {
		return function(AtomType $targetType, RecordType $parameterType, mixed $origin): Type|ValidationFailure {
			/** @var IntegerType $fromType */
			$fromType = $parameterType->types['min'];
			/** @var IntegerType $toType */
			$toType = $parameterType->types['max'];

			$fromMax = $fromType->numberRange->max;
			$toMin = $toType->numberRange->min;
			if (
				$fromMax === PlusInfinity::value ||
				$toMin === MinusInfinity::value ||
				$fromMax->value > $toMin->value
			) {
				return $this->validationFactory->error(
					ValidationErrorType::invalidParameterType,
					sprintf("The min range %s is not compatible with the max range %s",
						$fromType, $toType
					),
					$origin
				);
			}
			return $this->typeRegistry->integerFull(
				new NumberInterval(
					$fromType->numberRange->min,
					$toType->numberRange->max
				)
			);
		};
	}

	protected function getExecutor(): callable {
		return function(AtomValue $target, RecordValue $parameter): IntegerValue {
			/** @var IntegerValue $from */
			$from = $parameter->valueOf('min');
			/** @var IntegerValue $to */
			$to = $parameter->valueOf('max');
			return $this->valueRegistry->integer(random_int(
				(int)(string)$from->literalValue,
				(int)(string)$to->literalValue
			));
		};
	}

}
