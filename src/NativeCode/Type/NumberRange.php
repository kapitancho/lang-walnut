<?php

namespace Walnut\Lang\NativeCode\Type;

use BcMath\Number;
use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionException;
use Walnut\Lang\Blueprint\Common\Identifier\TypeNameIdentifier;
use Walnut\Lang\Blueprint\Common\Range\MinusInfinity;
use Walnut\Lang\Blueprint\Common\Range\NumberIntervalEndpoint;
use Walnut\Lang\Blueprint\Common\Range\PlusInfinity;
use Walnut\Lang\Blueprint\Function\NativeMethod;
use Walnut\Lang\Blueprint\Program\Registry\MethodAnalyser;
use Walnut\Lang\Blueprint\Program\Registry\ProgramRegistry;
use Walnut\Lang\Blueprint\Program\Registry\TypeRegistry;
use Walnut\Lang\Blueprint\Type\IntegerType;
use Walnut\Lang\Blueprint\Type\RealType;
use Walnut\Lang\Blueprint\Type\Type as TypeInterface;
use Walnut\Lang\Blueprint\Type\TypeType;
use Walnut\Lang\Blueprint\Value\Value;
use Walnut\Lang\Blueprint\Value\TypeValue;
use Walnut\Lang\Implementation\Type\Helper\BaseType;

final readonly class NumberRange implements NativeMethod {

	use BaseType;

	public function analyse(
		TypeRegistry $typeRegistry,
		MethodAnalyser $methodAnalyser,
		TypeInterface $targetType,
		TypeInterface $parameterType,
	): TypeInterface {
		if ($targetType instanceof TypeType) {
			$refType = $this->toBaseType($targetType->refType);
			if ($refType instanceof IntegerType) {
				return $typeRegistry->core->integerNumberRange;
			}
			if ($refType instanceof RealType) {
				return $typeRegistry->core->realNumberRange;
			}
		}
		// @codeCoverageIgnoreStart
		throw new AnalyserException(sprintf("[%s] Invalid target type: %s", __CLASS__, $targetType));
		// @codeCoverageIgnoreEnd
	}

	public function execute(
		ProgramRegistry $programRegistry,
		Value $target,
		Value $parameter
	): Value {
		if ($target instanceof TypeValue) {
			$typeValue = $this->toBaseType($target->typeValue);
			if ($typeValue instanceof IntegerType || $typeValue instanceof RealType) {
				$isInteger = $typeValue instanceof IntegerType;

				$vb = $isInteger ?
					fn(Number $number) => $programRegistry->valueRegistry->integer($number) :
					fn(Number $number) => $programRegistry->valueRegistry->real($number);
				$r = fn(NumberIntervalEndpoint $e) =>
					$programRegistry->valueRegistry->record([
						'value' => $vb($e->value),
						'inclusive' => $programRegistry->valueRegistry->boolean($e->inclusive)
					]);

				$data = fn(NumberIntervalEndpoint $e) =>
					$isInteger ?
						$programRegistry->valueRegistry->core->integerNumberIntervalEndpoint($r($e)) :
						$programRegistry->valueRegistry->core->realNumberIntervalEndpoint($r($e));

				$numberRange = $typeValue->numberRange;
				$intervals = [];
				foreach ($numberRange->intervals as $interval) {
					$start = $interval->start instanceof MinusInfinity ?
						$programRegistry->valueRegistry->core->minusInfinity :
						$data($interval->start);
					$end = $interval->end instanceof PlusInfinity ?
						$programRegistry->valueRegistry->core->plusInfinity :
						$data($interval->end);
					$interval = $programRegistry->valueRegistry->record([
						'start' => $start,
						'end' => $end,
					]);
					$intervals[] =
						$isInteger ?
						$programRegistry->valueRegistry->core->integerNumberInterval($interval) :
						$programRegistry->valueRegistry->core->realNumberInterval($interval);
				}
				$intervalsRec = $programRegistry->valueRegistry->record([
					'intervals' => $programRegistry->valueRegistry->tuple($intervals)
				]);
				return $isInteger ?
						$programRegistry->valueRegistry->core->integerNumberRange($intervalsRec) :
						$programRegistry->valueRegistry->core->realNumberRange($intervalsRec);
			}
		}
		// @codeCoverageIgnoreStart
		throw new ExecutionException("Invalid parameter value");
		// @codeCoverageIgnoreEnd
	}

}