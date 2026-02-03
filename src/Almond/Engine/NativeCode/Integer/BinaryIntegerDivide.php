<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Integer;

use BcMath\Number;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Expression\Expression;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Method\NativeMethod;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\IntegerType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\TypeRegistry;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\IntegerValue;
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

final readonly class BinaryIntegerDivide implements NativeMethod {
	use BaseType;

	public function __construct(
		private ValidationFactory $validationFactory,
		private TypeRegistry $typeRegistry,
		private ValueRegistry $valueRegistry,
	) {}

	public function validate(Type $targetType, Type $parameterType, Expression|null $origin): ValidationSuccess|ValidationFailure {
		$targetType = $this->toBaseType($targetType);
		if ($targetType instanceof IntegerType) {
			$parameterType = $this->toBaseType($parameterType);

			if ($parameterType instanceof IntegerType) {
				if ((string)$parameterType->numberRange === '1') {
					return $this->validationFactory->validationSuccess($targetType);
				}

				$int = $this->typeRegistry->integer();
				if (
					$targetType->numberRange->min instanceof NumberIntervalEndpoint && $targetType->numberRange->min->value >= 0 &&
					$parameterType->numberRange->min instanceof NumberIntervalEndpoint && $parameterType->numberRange->min->value >= 0
				) {
					$pMin = match(true) {
						$parameterType->numberRange->min === MinusInfinity::value => MinusInfinity::value,
						(string)$parameterType->numberRange->min->value === '0' => new Number(1),
						default => $parameterType->numberRange->min->value
					};
					$pMax = match(true) {
						$parameterType->numberRange->max === PlusInfinity::value => PlusInfinity::value,
						(string)$parameterType->numberRange->max->value === '0' => new Number(-1),
						default => $parameterType->numberRange->max->value
					};
					$min = $parameterType->numberRange->max === PlusInfinity::value ?
						new NumberIntervalEndpoint(
							new Number(0),
							true
						) :
						new NumberIntervalEndpoint(
							$targetType->numberRange->min->value->div($pMax)->floor(),
							true
						);
					$max = $targetType->numberRange->max === PlusInfinity::value ? PlusInfinity::value :
						new NumberIntervalEndpoint(
							$targetType->numberRange->max->value->div($pMin)->floor(),
							true
						);
					$interval = new NumberInterval($min, $max);
					$int = $this->typeRegistry->integerFull($interval);
				}

				return $this->validationFactory->validationSuccess(
					$parameterType->contains(0) ?
						$this->typeRegistry->result(
							$int,
							$this->typeRegistry->core->notANumber
						) : $int
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
		if ($target instanceof IntegerValue) {
			if ($parameter instanceof IntegerValue) {
				if ((int)(string)$parameter->literalValue === 0) {
					return $this->valueRegistry->error(
						$this->valueRegistry->core->notANumber
					);
				}
				return $this->valueRegistry->integer(
					intdiv((int)(string)$target->literalValue, (int)(string)$parameter->literalValue)
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
