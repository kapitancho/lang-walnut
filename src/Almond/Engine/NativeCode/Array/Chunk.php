<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Array;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Expression\Expression;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Method\NativeMethod;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\ArrayType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\IntegerType;
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

final readonly class Chunk implements NativeMethod {
	use BaseType;

	public function __construct(
		private ValidationFactory $validationFactory,
		private TypeRegistry $typeRegistry,
		private ValueRegistry $valueRegistry,
	) {}

	public function validate(Type $targetType, Type $parameterType, Expression|null $origin): ValidationSuccess|ValidationFailure {
		$targetType = $this->toBaseType($targetType);
		$type = $targetType instanceof TupleType ? $targetType->asArrayType() : $targetType;
		if ($type instanceof ArrayType) {
			$parameterType = $this->toBaseType($parameterType);
			if (
				$parameterType instanceof IntegerType &&
				$parameterType->numberRange->min !== MinusInfinity::value &&
				$parameterType->numberRange->min->value > 0
			) {
				$minL = $type->range->minLength;
				$maxL = $type->range->maxLength;
				$minS = $parameterType->numberRange->min->value;
				$maxS = $parameterType->numberRange->max === PlusInfinity::value ?
					PlusInfinity::value : $parameterType->numberRange->max->value;

				$minI = $minL > 0 ? 1 : 0;
				$maxI = $maxL < $maxS ? $maxL : $minS;

				$minO = match(true) {
					$maxS !== PlusInfinity::value => $minL->div($maxS)->ceil(),
					$minL < 1 => 0,
					default => 1,
				};
				$maxO = match(true) {
					$maxL === PlusInfinity::value => PlusInfinity::value,
					$minS > 0 => $maxL->div($minS)->ceil(),
					// @codeCoverageIgnoreStart
					default => PlusInfinity::value,
					// @codeCoverageIgnoreEnd
				};

				return $this->validationFactory->validationSuccess(
					$this->typeRegistry->array(
						$this->typeRegistry->array(
							$type->itemType,
							$minI,
							$maxI
						),
						$minO,
						$maxO
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
				return $this->valueRegistry->tuple(
					array_map(
						fn(array $chunk): TupleValue =>
						$this->valueRegistry->tuple($chunk),
						$chunks
					)
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
