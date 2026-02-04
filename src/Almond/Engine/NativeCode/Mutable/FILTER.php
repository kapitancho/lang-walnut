<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Mutable;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Expression\Expression;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Method\NativeMethod;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\ArrayType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\FunctionType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\MapType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\MutableType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\SetType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\TypeRegistry;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\FunctionValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\MutableValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\RecordValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\SetValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\TupleValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\ValueRegistry;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Execution\ExecutionException;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationErrorType;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationFactory;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationFailure;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationSuccess;
use Walnut\Lang\Almond\Engine\Implementation\Code\Type\Helper\BaseType;

final readonly class FILTER implements NativeMethod {
	use BaseType;

	public function __construct(
		private ValidationFactory $validationFactory,
		private TypeRegistry $typeRegistry,
		private ValueRegistry $valueRegistry,
	) {}

	public function validate(Type $targetType, Type $parameterType, Expression|null $origin): ValidationSuccess|ValidationFailure {
		if ($targetType instanceof MutableType) {
			$type = $this->toBaseType($targetType->valueType);
			if (($type instanceof ArrayType || $type instanceof MapType || $type instanceof SetType) && $type->isSubtypeOf(
				$this->typeRegistry->union([
					$this->typeRegistry->array($type->itemType),
					$this->typeRegistry->map($type->itemType),
					$this->typeRegistry->set($type->itemType),
				])
			) &&
				!$type->isSubtypeOf($this->typeRegistry->array($this->typeRegistry->any, 1)) &&
				!$type->isSubtypeOf($this->typeRegistry->map($this->typeRegistry->any, 1)) &&
				!$type->isSubtypeOf($this->typeRegistry->set($this->typeRegistry->any, 1))
			) {
				$parameterType = $this->toBaseType($parameterType);
				if ($parameterType instanceof FunctionType && $parameterType->returnType->isSubtypeOf($this->typeRegistry->boolean)) {
					if ($type->itemType->isSubtypeOf($parameterType->parameterType)) {
						return $this->validationFactory->validationSuccess($targetType);
					}
					return $this->validationFactory->error(
						ValidationErrorType::invalidParameterType,
						sprintf(
							"The parameter type %s of the callback function is not a subtype of %s",
							$type->itemType,
							$parameterType->parameterType
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
		if ($target instanceof MutableValue && $parameter instanceof FunctionValue) {
			$v = $target->value;
			if ($v instanceof TupleValue || $v instanceof RecordValue || $v instanceof SetValue) {
				$values = $v->values;
				$result = [];
				$true = $this->valueRegistry->true;
				foreach($values as $key => $value) {
					$r = $parameter->execute($value);
					if ($true->equals($r)) {
						$result[$key] = $value;
					}
				}
				if (!$v instanceof RecordValue) {
					$result = array_values($result);
				}
				$output = match(true) {
					$v instanceof TupleValue => $this->valueRegistry->tuple($result),
					$v instanceof RecordValue => $this->valueRegistry->record($result),
					$v instanceof SetValue => $this->valueRegistry->set($result),
				};
				$target->value = $output;
				return $target;
			}
		}
		// @codeCoverageIgnoreStart
		throw new ExecutionException("Invalid target value");
		// @codeCoverageIgnoreEnd
	}
}
