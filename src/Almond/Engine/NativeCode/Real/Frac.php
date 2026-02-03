<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Real;

use BcMath\Number;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Expression\Expression;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Method\NativeMethod;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\IntegerType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\NullType;
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
use Walnut\Lang\Almond\Engine\Implementation\Code\Type\Helper\BaseType;

final readonly class Frac implements NativeMethod {
	use BaseType;

	public function __construct(
		private ValidationFactory $validationFactory,
		private TypeRegistry $typeRegistry,
		private ValueRegistry $valueRegistry,
	) {}

	public function validate(Type $targetType, Type $parameterType, Expression|null $origin): ValidationSuccess|ValidationFailure {
		$targetType = $this->toBaseType($targetType);
		if ($targetType instanceof IntegerType || $targetType instanceof RealType) {
			$parameterType = $this->toBaseType($parameterType);
			if ($parameterType instanceof NullType) {
				if ($targetType instanceof IntegerType) {
					return $this->validationFactory->validationSuccess(
						$this->typeRegistry->integerSubset([new Number(0)])
					);
				}
				if ($targetType instanceof RealSubsetType) {
					return $this->validationFactory->validationSuccess(
						$this->typeRegistry->realSubset(
							array_values(
								array_unique(
									array_map(
										fn(Number $v) => $this->calculateFrac($v),
										$targetType->subsetValues
									)
								)
							)
						)
					);
				}
				$min = $targetType->numberRange->min;
				$max = $targetType->numberRange->max;

				return $this->validationFactory->validationSuccess(
					$this->typeRegistry->realFull(
						new NumberInterval(
							match(true) {
								$min === MinusInfinity::value || $min->value <= -1 => new NumberIntervalEndpoint(new Number(-1), false),
								$min->value < 1 => $min,
								default => new NumberIntervalEndpoint(new Number(0), true),
							},
							match(true) {
								$max === PlusInfinity::value || $max->value >= 1 => new NumberIntervalEndpoint(new Number(1), false),
								$max->value > -1 => $max,
								default => new NumberIntervalEndpoint(new Number(0), true),
							}
						),
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

	private function calculateFrac(Number $value): Number {
		if ($value > 0) {
			return $value->sub($value->floor());
		} elseif ($value < 0) {
			return $value->sub($value->ceil());
		} else {
			return new Number(0);
		}
	}

	public function execute(Value $target, Value $parameter): Value {
		if ($target instanceof RealValue || $target instanceof IntegerValue) {
			return $this->valueRegistry->real($this->calculateFrac($target->literalValue));
		}
		// @codeCoverageIgnoreStart
		throw new ExecutionException("Invalid target value");
		// @codeCoverageIgnoreEnd
	}
}
