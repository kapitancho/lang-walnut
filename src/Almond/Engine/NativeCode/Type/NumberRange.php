<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Type;

use BcMath\Number;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Expression\Expression;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Method\NativeMethod;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\IntegerType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\RealType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\TypeType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type as TypeInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\TypeRegistry;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\TypeValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\ValueRegistry;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Range\MinusInfinity;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Range\NumberIntervalEndpoint;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Range\PlusInfinity;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Execution\ExecutionException;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationErrorType;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationFactory;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationFailure;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationSuccess;
use Walnut\Lang\Almond\Engine\Implementation\Code\Type\Helper\BaseType;

final readonly class NumberRange implements NativeMethod {

	use BaseType;

	public function __construct(
		private ValidationFactory $validationFactory,
		private TypeRegistry $typeRegistry,
		private ValueRegistry $valueRegistry,
	) {}

	public function validate(TypeInterface $targetType, TypeInterface $parameterType, Expression|null $origin): ValidationSuccess|ValidationFailure {
		if ($targetType instanceof TypeType) {
			$refType = $this->toBaseType($targetType->refType);
			if ($refType instanceof IntegerType) {
				return $this->validationFactory->validationSuccess(
					$this->typeRegistry->core->integerNumberRange
				);
			}
			if ($refType instanceof RealType) {
				return $this->validationFactory->validationSuccess(
					$this->typeRegistry->core->realNumberRange
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
		if ($target instanceof TypeValue) {
			$typeValue = $this->toBaseType($target->typeValue);
			if ($typeValue instanceof IntegerType || $typeValue instanceof RealType) {
				$isInteger = $typeValue instanceof IntegerType;

				$vb = $isInteger ?
					fn(Number $number) => $this->valueRegistry->integer($number) :
					fn(Number $number) => $this->valueRegistry->real($number);
				$r = fn(NumberIntervalEndpoint $e) =>
					$this->valueRegistry->record([
						'value' => $vb($e->value),
						'inclusive' => $this->valueRegistry->boolean($e->inclusive)
					]);

				$data = fn(NumberIntervalEndpoint $e) =>
					$isInteger ?
						$this->valueRegistry->core->integerNumberIntervalEndpoint($r($e)) :
						$this->valueRegistry->core->realNumberIntervalEndpoint($r($e));

				$numberRange = $typeValue->numberRange;
				$intervals = [];
				foreach ($numberRange->intervals as $interval) {
					$start = $interval->start instanceof MinusInfinity ?
						$this->valueRegistry->core->minusInfinity :
						$data($interval->start);
					$end = $interval->end instanceof PlusInfinity ?
						$this->valueRegistry->core->plusInfinity :
						$data($interval->end);
					$interval = $this->valueRegistry->record([
						'start' => $start,
						'end' => $end,
					]);
					$intervals[] =
						$isInteger ?
						$this->valueRegistry->core->integerNumberInterval($interval) :
						$this->valueRegistry->core->realNumberInterval($interval);
				}
				$intervalsRec = $this->valueRegistry->record([
					'intervals' => $this->valueRegistry->tuple($intervals)
				]);
				return $isInteger ?
						$this->valueRegistry->core->integerNumberRange($intervalsRec) :
						$this->valueRegistry->core->realNumberRange($intervalsRec);
			}
		}
		// @codeCoverageIgnoreStart
		throw new ExecutionException("Invalid parameter value");
		// @codeCoverageIgnoreEnd
	}
}
