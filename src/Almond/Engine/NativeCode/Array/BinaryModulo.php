<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Array;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Method\NativeMethod;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\ArrayType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\IntegerType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\NothingType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\TupleType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\TypeRegistry;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\IntegerValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\TupleValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\ValueRegistry;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Range\MinusInfinity;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Range\PlusInfinity;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Execution\ExecutionException;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationErrorType;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationFactory;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationFailure;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationSuccess;
use Walnut\Lang\Almond\Engine\Implementation\Code\Type\Helper\BaseType;

final readonly class BinaryModulo implements NativeMethod {
	use BaseType;

	public function __construct(
		private ValidationFactory $validationFactory,
		private TypeRegistry $typeRegistry,
		private ValueRegistry $valueRegistry,
	) {}

	public function validate(Type $targetType, Type $parameterType, mixed $origin): ValidationSuccess|ValidationFailure {
		$targetType = $this->toBaseType($targetType);
		if (
			$targetType instanceof TupleType &&
			$targetType->restType instanceof NothingType &&
			$parameterType instanceof IntegerType &&
			$parameterType->numberRange->min !== MinusInfinity::value &&
			$parameterType->numberRange->min->value > 0 &&
			$parameterType->numberRange->max !== PlusInfinity::value &&
			(string)$parameterType->numberRange->max->value === (string)$parameterType->numberRange->min->value
		) {
			$l = (int)(string)$parameterType->numberRange->max->value;
			$skip = intdiv(count($targetType->types), $l) * $l;
			return $this->validationFactory->validationSuccess(
				$this->typeRegistry->tuple(
					array_slice(
						$targetType->types,
						$skip
					),
					null
				)
			);
		}
		$type = $targetType instanceof TupleType ? $targetType->asArrayType() : $targetType;
		if ($type instanceof ArrayType) {
			$parameterType = $this->toBaseType($parameterType);
			if (
				$parameterType instanceof IntegerType &&
				$parameterType->numberRange->min !== MinusInfinity::value &&
				$parameterType->numberRange->min->value > 0
			) {
				if (
					$type->range->maxLength !== PlusInfinity::value &&
					$parameterType->numberRange->min->value > $type->range->maxLength
				) {
					return $this->validationFactory->validationSuccess($targetType);
				}
				if (
					$type->range->maxLength !== PlusInfinity::value &&
					(string)$type->range->minLength === (string)$type->range->maxLength &&
					$parameterType->numberRange->max !== PlusInfinity::value &&
					(string)$parameterType->numberRange->max->value === (string)$parameterType->numberRange->min->value
				) {
					$size = $type->range->maxLength->mod($parameterType->numberRange->min->value);
					return $this->validationFactory->validationSuccess(
						$this->typeRegistry->array($type->itemType, $size, $size)
					);
				}
				return $this->validationFactory->validationSuccess(
					$this->typeRegistry->array(
						$type->itemType,
						0,
						match(true) {
							$parameterType->numberRange->max === PlusInfinity::value => $type->range->maxLength,
							$type->range->maxLength === PlusInfinity::value => max(
								0,
								$parameterType->numberRange->max->value->sub(1),
							),
							default => max(
								0,
								min(
									$parameterType->numberRange->max->value->sub(1),
									$type->range->maxLength
								)
							)
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
		if ($target instanceof TupleValue && $parameter instanceof IntegerValue) {
			$chunkSize = (int)(string)$parameter->literalValue;
			if ($chunkSize > 0) {
				$chunks = array_chunk($target->values, $chunkSize);
				$last = $chunks[array_key_last($chunks)];

				return $this->valueRegistry->tuple(
					count($chunks) > 0 && count($last) < $chunkSize ?
						$last : []
				);
			}
			// @codeCoverageIgnoreStart
			throw new ExecutionException("Chunk size must be positive");
			// @codeCoverageIgnoreEnd
		}
		// @codeCoverageIgnoreStart
		throw new ExecutionException("Invalid target or parameter value");
		// @codeCoverageIgnoreEnd
	}
}
