<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Expression\Expression;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\IntegerType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\RecordType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\StringType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\IntegerValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\RecordValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\StringValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Range\MinusInfinity;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Range\PlusInfinity;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Execution\ExecutionException;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationErrorType;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationFailure;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationSuccess;
use Walnut\Lang\Almond\Engine\Implementation\Code\Type\Helper\BaseType;

trait StringPadLeftPadRight {
	use BaseType;

	public function validate(Type $targetType, Type $parameterType, Expression|null $origin): ValidationSuccess|ValidationFailure {
		$targetType = $this->toBaseType($targetType);
		if ($targetType instanceof StringType) {
			$parameterType = $this->toBaseType($parameterType);
			if ($parameterType instanceof RecordType) {
				$types = $parameterType->types;
				$lengthType = $types['length'] ?? null;
				$padStringType = $types['padString'] ?? null;
				if ($lengthType instanceof IntegerType && $padStringType instanceof StringType) {
					return $this->validationFactory->validationSuccess(
						$this->typeRegistry->string(
							max(
								$targetType->range->minLength,
								$lengthType->numberRange->min === MinusInfinity::value ?
									0 : $lengthType->numberRange->min->value
							),
							$targetType->range->maxLength === PlusInfinity::value ||
							$lengthType->numberRange->max === PlusInfinity::value ? PlusInfinity::value :
								max(
									$targetType->range->maxLength,
									$lengthType->numberRange->max->value -
									($lengthType->numberRange->max->inclusive ? 0 : 1)
								),
						)
					);
				}
			}
			return $this->validationFactory->error(
				ValidationErrorType::invalidParameterType,
				sprintf("[%s] Invalid parameter type: %s", __CLASS__, $parameterType),
				$origin
			);
		}
		return $this->validationFactory->error(
			ValidationErrorType::invalidTargetType,
			sprintf("[%s] Invalid target type: %s", __CLASS__, $targetType),
			$origin
		);
	}

	private function executeHelper(
		Value $target,
		Value $parameter,
		int $padType
	): Value {
		if ($target instanceof StringValue) {
			if ($parameter instanceof RecordValue) {
				$values = $parameter->values;
				$length = $values['length'] ?? null;
				$padString = $values['padString'] ?? null;
				if ($length instanceof IntegerValue && $padString instanceof StringValue) {
					$result = str_pad(
						$target->literalValue,
						(int)(string)$length->literalValue,
						$padString->literalValue,
						$padType
					);
					return $this->valueRegistry->string($result);
				}
			}
			// @codeCoverageIgnoreStart
			throw new ExecutionException("Invalid parameter value");
			// @codeCoverageIgnoreEnd
		}
		// @codeCoverageIgnoreStart
		throw new ExecutionException("Invalid target value");
		// @codeCoverageIgnoreEnd
	}

}
