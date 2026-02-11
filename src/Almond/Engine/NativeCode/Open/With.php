<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Open;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\OpenType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\ResultType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\ErrorValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\OpenValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value as ValueInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationFailure;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\CommonBase\WithMethod;

/** @extends WithMethod<OpenType, Type, OpenValue, ValueInterface> */
final readonly class With extends WithMethod {

	protected function getValidator(): callable {
		return fn(OpenType $targetType, Type $parameterType, mixed $origin): Type|ValidationFailure =>
			$this->validateDataOpenType(
				$targetType,
				$parameterType,
				function() use ($targetType, $origin): Type|ValidationFailure {
					$validationMethod = $targetType->validator;
					if ($validationMethod !== null) {
						$validationResult = $validationMethod->validate(
							$this->typeRegistry->nothing,
							$targetType->valueType,
							$origin
						);
						if ($validationResult instanceof ValidationFailure) {
							return $validationResult;
						}
						if ($validationResult->type instanceof ResultType) {
							return $this->typeRegistry->result(
								$targetType, $validationResult->type->errorType
							);
						}
					}
					return $targetType;
				},
				$origin
			);
	}

	protected function getExecutor(): callable {
		return fn(OpenValue $target, ValueInterface $parameter): ValueInterface =>
			$this->executeDataOpenType(
				$target,
				$parameter,
				function(ValueInterface $p) use ($target): ValueInterface {
					$validationMethod = $target->type->validator;
					if ($validationMethod !== null) {
						$result = $validationMethod->execute(
							$this->variableScopeFactory->emptyVariableValueScope,
							null,
							$p,
						);
						if ($result instanceof ErrorValue) {
							return $result;
						}
					}
					return $this->valueRegistry->open($target->type->name, $p);
				}
			);
	}
}
