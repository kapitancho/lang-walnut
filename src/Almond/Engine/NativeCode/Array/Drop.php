<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Array;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Expression\Expression;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Method\NativeMethod;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\ArrayType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\IntegerSubsetType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\TupleType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\TypeRegistry;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\IntegerValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\TupleValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\ValueRegistry;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Range\PlusInfinity;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Execution\ExecutionException;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationErrorType;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationFactory;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationFailure;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationSuccess;
use Walnut\Lang\Almond\Engine\Implementation\Code\Type\Helper\BaseType;

final readonly class Drop implements NativeMethod {
	use BaseType;

	public function __construct(
		private ValidationFactory $validationFactory,
		private TypeRegistry $typeRegistry,
		private ValueRegistry $valueRegistry,
	) {}

	public function validate(Type $targetType, Type $parameterType, Expression|null $origin): ValidationSuccess|ValidationFailure {
		$type = $this->toBaseType($targetType);
		$pType = $this->toBaseType($parameterType);
		if ($type instanceof TupleType && $pType instanceof IntegerSubsetType && count($pType->subsetValues) === 1) {
			$param = (int)(string)$pType->subsetValues[0];
			if ($param >= 0) {
				return $this->validationFactory->validationSuccess(
					$this->typeRegistry->tuple(
						array_slice($type->types, $param),
						$type->restType
					)
				);
			}
		}
		$type = $type instanceof TupleType ? $type->asArrayType() : $type;
		if ($type instanceof ArrayType) {
			$pType = $this->toBaseType($parameterType);
			if ($pType->isSubtypeOf($this->typeRegistry->integer(0))) {
				$minLength = match(true) {
					$pType->numberRange->max === PlusInfinity::value,
					$pType->numberRange->max->value > $type->range->minLength => 0,
					default => $type->range->minLength->sub($pType->numberRange->max->value)
				};
				$maxLength = match(true) {
					$type->range->maxLength === PlusInfinity::value => PlusInfinity::value,
					$pType->numberRange->min->value > $type->range->maxLength => 0,
					default => $type->range->maxLength->sub($pType->numberRange->min->value)
				};
				return $this->validationFactory->validationSuccess(
					$this->typeRegistry->array(
						$type->itemType,
						$minLength,
						$maxLength
					)
				);
			}
			return $this->validationFactory->error(
				ValidationErrorType::invalidParameterType,
				sprintf("[%s] Invalid parameter type: %s", __CLASS__, $parameterType),
				origin: $origin
			);
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
		if ($target instanceof TupleValue) {
			if ($parameter instanceof IntegerValue && $parameter->literalValue >= 0) {
				return $this->valueRegistry->tuple(
					array_slice($target->values, (int)(string)$parameter->literalValue)
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
