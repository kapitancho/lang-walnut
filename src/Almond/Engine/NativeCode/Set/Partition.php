<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Set;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Expression\Expression;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\FunctionType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\SetType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\FunctionValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\SetValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationErrorType;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationFailure;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\SetNativeMethod;

/** @extends SetNativeMethod<FunctionType, FunctionValue> */
final readonly class Partition extends SetNativeMethod {

	protected function getValidator(): callable {
		return function(SetType $targetType, FunctionType $parameterType, Expression|null $origin): Type|ValidationFailure {
			if (!$parameterType->returnType->isSubtypeOf($this->typeRegistry->boolean)) {
				return $this->validationFactory->error(
					ValidationErrorType::invalidParameterType,
					sprintf("[%s] Invalid parameter type: %s", __CLASS__, $parameterType),
					$origin
				);
			}
			if (!$targetType->itemType->isSubtypeOf($parameterType->parameterType)) {
				return $this->validationFactory->error(
					ValidationErrorType::invalidParameterType,
					sprintf(
						"The parameter type %s of the callback function is not a subtype of %s",
						$targetType->itemType,
						$parameterType->parameterType
					),
					$origin
				);
			}
			$partitionType = $this->typeRegistry->set($targetType->itemType, 0, $targetType->range->maxLength);
			return $this->typeRegistry->record([
				'matching' => $partitionType,
				'notMatching' => $partitionType
			], null);
		};
	}

	protected function getExecutor(): callable {
		return function(SetValue $target, FunctionValue $parameter): Value {
			$matching = [];
			$notMatching = [];
			$true = $this->valueRegistry->true;

			foreach ($target->values as $value) {
				$r = $parameter->execute($value);
				if ($true->equals($r)) {
					$matching[] = $value;
				} else {
					$notMatching[] = $value;
				}
			}

			return $this->valueRegistry->record([
				'matching' => $this->valueRegistry->set($matching),
				'notMatching' => $this->valueRegistry->set($notMatching)
			]);
		};
	}

}
