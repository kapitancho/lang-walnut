<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Type;

use BcMath\Number;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\IntegerType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\NullType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\RealType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\TypeType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\NullValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\TypeValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Range\MinusInfinity;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Range\NumberIntervalEndpoint;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Range\PlusInfinity;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\TypeNativeMethod;

/** @extends TypeNativeMethod<IntegerType|RealType, NullType, NullValue> */
final readonly class NumberRange extends TypeNativeMethod {

	protected function isTargetRefTypeValid(Type $targetRefType, mixed $origin): bool {
		$targetRefType = $this->toBaseType($targetRefType);
		return $targetRefType instanceof IntegerType || $targetRefType instanceof RealType;
	}

	protected function getValidator(): callable {
		return function(TypeType $targetType, NullType $parameterType): Type {
			$refType = $this->toBaseType($targetType->refType);
			if ($refType instanceof IntegerType) {
				return $this->typeRegistry->core->integerNumberRange;
			}
			return $this->typeRegistry->core->realNumberRange;
		};
	}

	protected function getExecutor(): callable {
		return function(TypeValue $target, NullValue $parameter): Value {
			$typeValue = $this->toBaseType($target->typeValue);
			/** @var IntegerType|RealType $typeValue */
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
		};
	}

}
