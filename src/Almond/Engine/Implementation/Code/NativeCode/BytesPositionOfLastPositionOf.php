<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Expression\Expression;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\BytesType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\BytesValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Range\PlusInfinity;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Execution\ExecutionException;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationErrorType;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationFailure;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationSuccess;
use Walnut\Lang\Almond\Engine\Implementation\Code\Type\Helper\BaseType;

trait BytesPositionOfLastPositionOf {
	use BaseType;

	public function validate(Type $targetType, Type $parameterType, Expression|null $origin): ValidationSuccess|ValidationFailure {
		$targetType = $this->toBaseType($targetType);
		if ($targetType instanceof BytesType) {
			$parameterType = $this->toBaseType($parameterType);
			if ($parameterType instanceof BytesType) {
				return $this->validationFactory->validationSuccess(
					$this->typeRegistry->result(
						$this->typeRegistry->integer(0,
							$targetType->range->maxLength === PlusInfinity::value ? PlusInfinity::value :
							$targetType->range->maxLength - $parameterType->range->minLength
						),
						$this->typeRegistry->core->sliceNotInBytes
					)
				);
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

	/**
	 * @param callable(string, string): (false|int) $posFn
	 */
	private function executeHelper(
		Value $target,
		Value $parameter,
		callable $posFn
	): Value {
		if ($target instanceof BytesValue) {
			if ($parameter instanceof BytesValue) {
				$result = $posFn($target->literalValue, $parameter->literalValue);
				return $result === false ?
					$this->valueRegistry->error(
						$this->valueRegistry->core->sliceNotInBytes
					) : $this->valueRegistry->integer($result);
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
