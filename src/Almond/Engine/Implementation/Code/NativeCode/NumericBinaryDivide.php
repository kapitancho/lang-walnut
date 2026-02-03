<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Expression\Expression;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\IntegerType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\RealType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\IntegerValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\RealValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Execution\ExecutionException;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationErrorType;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationFailure;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationSuccess;
use Walnut\Lang\Almond\Engine\Implementation\Code\Type\Helper\BaseType;

trait NumericBinaryDivide {
	use BaseType;
	use NumericRangeHelper;

	private function validateHelper(
		IntegerType|RealType $targetType,
		Type $parameterType,
		Expression|null $origin,
	): ValidationSuccess|ValidationFailure {
		$parameterType = $this->toBaseType($parameterType);

		if ($parameterType instanceof IntegerType || $parameterType instanceof RealType) {
			if ($parameterType instanceof IntegerType && (string)$parameterType->numberRange === '1') {
				return $this->validationFactory->validationSuccess($targetType);
			}
			$subsetType = $this->getDivideSubsetType(
				$targetType, $parameterType
			);
			if ($subsetType !== null) {
				return $this->validationFactory->validationSuccess($subsetType);
			}

			$interval = $this->getDivideRange($targetType, $parameterType);
			$intervals = $this->getSplitInterval($interval, !$targetType->contains(0));
			$real = $this->typeRegistry->realFull(...$intervals);
			return $this->validationFactory->validationSuccess(
				$parameterType->contains(0) ?
					$this->typeRegistry->result(
						$real,
						$this->typeRegistry->core->notANumber
					) : $real
			);
		}
		return $this->validationFactory->error(
			ValidationErrorType::invalidParameterType,
			sprintf("[%s] Invalid parameter type: %s", __CLASS__, $parameterType),
			$origin
		);
	}

	public function execute(Value $target, Value $parameter): Value {
		if ($target instanceof RealValue || $target instanceof IntegerValue) {
			if ($parameter instanceof IntegerValue || $parameter instanceof RealValue) {
				if ((float)(string)$parameter->literalValue === 0.0) {
					return $this->valueRegistry->error(
						$this->valueRegistry->core->notANumber
					);
				}
				// Special case: Integer / 1 = Integer
				if (
					$parameter instanceof IntegerValue &&
					(string)$parameter->literalValue === '1'
				) {
					return $target;
				}
                return $this->valueRegistry->real(
	                fdiv((float)(string)$target->literalValue, (float)(string)$parameter->literalValue)
                );
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
