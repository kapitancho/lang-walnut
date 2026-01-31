<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Real;

use BcMath\Number;
use RoundingMode;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Expression\Expression;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Method\NativeMethod;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\IntegerSubsetType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\IntegerType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\RealSubsetType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\RealType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\TypeRegistry;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\IntegerValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\RealValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\ValueRegistry;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Range\MinusInfinity;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Range\NumberInterval;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Range\NumberIntervalEndpoint;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Range\PlusInfinity;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Execution\ExecutionException;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationErrorType;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationFactory;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationFailure;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationSuccess;

final readonly class AsInteger implements NativeMethod {

	public function __construct(
		private ValidationFactory $validationFactory,
		private TypeRegistry $typeRegistry,
		private ValueRegistry $valueRegistry,
	) {}

	public function validate(Type $targetType, Type $parameterType, Expression|null $origin): ValidationSuccess|ValidationFailure {
		//$targetType = $this->toBaseType($targetType);
		if ($targetType instanceof IntegerSubsetType || $targetType instanceof RealSubsetType) {
			return $this->validationFactory->validationSuccess(
				$this->typeRegistry->integerSubset(
					array_map(fn(Number $v) => $v > 0 ? $v->floor() : $v->ceil(),
						$targetType->subsetValues)
				)
			);
		}
		if ($targetType instanceof IntegerType || $targetType instanceof RealType) {
			return $this->validationFactory->validationSuccess(
				$this->typeRegistry->integerFull(... array_map(
					fn(NumberInterval $interval) => new NumberInterval(
						$interval->start === MinusInfinity::value ? MinusInfinity::value :
							new NumberIntervalEndpoint(
								$interval->start->value->round(0, RoundingMode::TowardsZero),
								(string)$interval->start->value !== (string)$interval->start->value->round(0, RoundingMode::TowardsZero) ||
								$interval->start->inclusive
							),
						$interval->end === PlusInfinity::value ? PlusInfinity::value :
							new NumberIntervalEndpoint(
								$interval->end->value->round(0, RoundingMode::TowardsZero),
								(string)$interval->end->value !== (string)$interval->end->value->round(0, RoundingMode::TowardsZero) ||
								$interval->end->inclusive
							)
					),
					$targetType->numberRange->intervals
				))
			);
		}
		return $this->validationFactory->error(
			ValidationErrorType::invalidTargetType,
			sprintf("[%s] Invalid target type: %s", __CLASS__, $targetType),
			$origin
		);
	}

	public function execute(Value $target, Value $parameter): Value {
		if ($target instanceof RealValue || $target instanceof IntegerValue) {
			return $this->valueRegistry->integer(
				$target->literalValue->round(0, RoundingMode::TowardsZero)
			);
		}
		// @codeCoverageIgnoreStart
		throw new ExecutionException("Invalid target value");
		// @codeCoverageIgnoreEnd
	}
}