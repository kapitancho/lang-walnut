<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Mutable;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Expression\Expression;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Method\NativeMethod;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\FunctionType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\MapType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\MutableType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\ResultType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\TypeRegistry;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\FunctionValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\MutableValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\RecordValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\StringValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\ValueRegistry;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Execution\ExecutionException;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationErrorType;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationFactory;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationFailure;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationSuccess;
use Walnut\Lang\Almond\Engine\Implementation\Code\Type\Helper\BaseType;

final readonly class REMAPKEYS implements NativeMethod {
	use BaseType;

	public function __construct(
		private ValidationFactory $validationFactory,
		private TypeRegistry $typeRegistry,
		private ValueRegistry $valueRegistry,
	) {}

	public function validate(Type $targetType, Type $parameterType, Expression|null $origin): ValidationSuccess|ValidationFailure {
		$targetType = $this->toBaseType($targetType);
		if ($targetType instanceof MutableType) {
			$valueType = $this->toBaseType($targetType->valueType);
			if ($valueType instanceof MapType) {
				if ($valueType->range->minLength > 1) {
					return $this->validationFactory->error(
						ValidationErrorType::invalidTargetType,
						"Invalid target type: REMAPKEYS can only be used on maps with a minimum size of 0 or 1",
						origin: $origin
					);
				}
				$parameterType = $this->toBaseType($parameterType);
				if ($parameterType instanceof FunctionType) {
					if ($valueType->keyType->isSubtypeOf($parameterType->parameterType)) {
						$r = $parameterType->returnType;
						$returnType = $r instanceof ResultType ? $r->returnType : $r;
						if ($returnType->isSubtypeOf($valueType->keyType)) {
							return $this->validationFactory->validationSuccess($targetType);
						}
						return $this->validationFactory->error(
							ValidationErrorType::invalidReturnType,
							sprintf(
								"The return type %s of the callback function is not a subtype of %s",
								$returnType,
								$valueType->keyType
							),
							origin: $origin
						);
					}
					return $this->validationFactory->error(
						ValidationErrorType::invalidParameterType,
						sprintf(
							"The parameter type %s of the callback function is not a supertype of %s",
							$parameterType->parameterType,
							$valueType->keyType,
						),
						origin: $origin
					);
				}
				return $this->validationFactory->error(
					ValidationErrorType::invalidParameterType,
					sprintf("[%s] Invalid parameter type: %s", __CLASS__, $parameterType),
					origin: $origin
				);
			}
		}
		// @codeCoverageIgnoreStart
		return $this->validationFactory->error(
			ValidationErrorType::invalidTargetType,
			sprintf("[%s] Invalid target type: %s", __CLASS__, $targetType),
			origin: $origin
		);
		// @codeCoverageIgnoreEnd
	}

	public function execute(Value $target, Value $parameter): Value {
		if ($target instanceof MutableValue) {
			$v = $target->value;
			if ($v instanceof RecordValue && $parameter instanceof FunctionValue) {
				$values = $v->values;
				$result = [];
				foreach($values as $key => $value) {
					$r = $parameter->execute(
						$this->valueRegistry->string($key)
					);
					if ($r instanceof StringValue) {
						$result[$r->literalValue] = $value;
					} else {
						// @codeCoverageIgnoreStart
						throw new ExecutionException("Invalid callback value");
						// @codeCoverageIgnoreEnd
					}
				}
				$target->value = $this->valueRegistry->record($result);
				return $target;
			}
		}
		// @codeCoverageIgnoreStart
		throw new ExecutionException("Invalid target value");
		// @codeCoverageIgnoreEnd
	}
}
