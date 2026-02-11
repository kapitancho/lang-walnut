<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Type;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\IntegerType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\RealType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\TypeType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\AtomValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\TypeValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Identifier\TypeName;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Range\MinusInfinity;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Range\NumberInterval;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Range\NumberIntervalEndpoint;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Range\PlusInfinity;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationErrorType;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationFailure;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\TypeNativeMethod;

/** @extends TypeNativeMethod<IntegerType|RealType, Type, Value> */
final readonly class WithNumberRange extends TypeNativeMethod {

	protected function isTargetRefTypeValid(Type $targetRefType, mixed $origin): bool {
		$refType = $this->toBaseType($targetRefType);
		return $refType instanceof IntegerType || $refType instanceof RealType;
	}

	protected function getValidator(): callable {
		return function(TypeType $targetType, Type $parameterType, mixed $origin): TypeType|ValidationFailure {
			/** @var IntegerType|RealType $refType */
			$refType = $this->toBaseType($targetType->refType);
			if ($refType instanceof IntegerType) {
				if ($parameterType->isSubtypeOf($this->typeRegistry->core->integerNumberRange)) {
					return $this->typeRegistry->type($this->typeRegistry->integer());
				}
			} elseif ($refType instanceof RealType) {
				if ($parameterType->isSubtypeOf($this->typeRegistry->core->realNumberRange)) {
					return $this->typeRegistry->type($this->typeRegistry->real());
				}
			}
			return $this->validationFactory->error(
				ValidationErrorType::invalidParameterType,
				sprintf("[%s] Invalid parameter type: %s", __CLASS__, $parameterType),
				$origin
			);
		};
	}

	protected function getExecutor(): callable {
		return function(TypeValue $target, Value $parameter): TypeValue {
			/** @var IntegerType|RealType $typeValue */
			$typeValue = $this->toBaseType($target->typeValue);
			$prefix = $typeValue instanceof IntegerType ? 'Integer' : 'Real';
			$intervals = [];
			foreach($parameter->value->values['intervals']->values as $interval) {
				['start' => $start, 'end' => $end] = $interval->value->values;
				$vStart = $start instanceof AtomValue ? MinusInfinity::value :
					new NumberIntervalEndpoint(
						$start->value->values['value']->literalValue,
						$start->value->values['inclusive']->literalValue
					);
				$vEnd = $end instanceof AtomValue ? PlusInfinity::value :
					new NumberIntervalEndpoint(
						$end->value->values['value']->literalValue,
						$end->value->values['inclusive']->literalValue
					);
				$intervals[] = new NumberInterval(
					$vStart,
					$vEnd
				);
			}
			$type = $typeValue instanceof IntegerType ?
				$this->typeRegistry->integerFull(...$intervals) :
				$this->typeRegistry->realFull(...$intervals
			);
			return $this->valueRegistry->type($type);
		};
	}

}
