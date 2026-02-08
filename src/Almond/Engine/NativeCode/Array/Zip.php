<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Array;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Method\NativeMethod;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\ArrayType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\NothingType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\TupleType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\TypeRegistry;
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

final readonly class Zip implements NativeMethod {
	use BaseType;

	public function __construct(
		private ValidationFactory $validationFactory,
		private TypeRegistry $typeRegistry,
		private ValueRegistry $valueRegistry,
	) {}

	public function validate(Type $targetType, Type $parameterType, mixed $origin): ValidationSuccess|ValidationFailure {
		$type = $this->toBaseType($targetType);
		$pType = $this->toBaseType($parameterType);
		if ($type instanceof TupleType && $pType instanceof TupleType) {
			$resultType = [];
			$maxLength = max(count($type->types), count($pType->types));
			for ($i = 0; $i < $maxLength; $i++) {
				$tg = $type->types[$i] ?? $type->restType;
				$pr = $pType->types[$i] ?? $pType->restType;
				if (!$tg instanceof NothingType && !$pr instanceof NothingType) {
					$resultType[] = $this->typeRegistry->tuple([$tg, $pr], null);
				} else {
					break;
				}
			}
			return $this->validationFactory->validationSuccess(
				$this->typeRegistry->tuple($resultType,
					$type->restType instanceof NothingType || $pType->restType instanceof NothingType ?
						$this->typeRegistry->nothing : $this->typeRegistry->tuple([
							$type->restType,
							$pType->restType,
					], null)
				)
			);
		}
		$type = $type instanceof TupleType ? $type->asArrayType() : $type;
		$pType = $pType instanceof TupleType ? $pType->asArrayType() : $pType;
		if ($type instanceof ArrayType) {
			if ($pType instanceof ArrayType) {
				return $this->validationFactory->validationSuccess(
					$this->typeRegistry->array(
						$this->typeRegistry->tuple([
							$type->itemType,
							$pType->itemType,
						], null),
						min($type->range->minLength, $pType->range->minLength),
						match(true) {
							$type->range->maxLength === PlusInfinity::value => $pType->range->maxLength,
							$pType->range->maxLength === PlusInfinity::value => $type->range->maxLength,
							default => min($type->range->maxLength, $pType->range->maxLength)
						}
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
			if ($parameter instanceof TupleValue) {
				$values = $target->values;
				$pValues = $parameter->values;
				$result = [];
				foreach ($values as $index => $value) {
					$pValue = $pValues[$index] ?? null;
					if (!$pValue) {
						break;
					}
					$result[] = $this->valueRegistry->tuple([$value, $pValue]);
				}
				return $this->valueRegistry->tuple($result);
			}
			// @codeCoverageIgnoreStart
			throw new ExecutionException("Invalid parameter type");
			// @codeCoverageIgnoreEnd
		}
		// @codeCoverageIgnoreStart
		throw new ExecutionException("Invalid target value");
		// @codeCoverageIgnoreEnd
	}
}
