<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Array;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\ArrayType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\FunctionType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\FunctionValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\StringValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\TupleValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\CommonBase\ArrayGroupByIndexByBase;

final readonly class GroupBy extends ArrayGroupByIndexByBase {

	protected function getValidator(): callable {
		return function(ArrayType $targetType, FunctionType $parameterType, mixed $origin): Type {
			$returnType = $this->toBaseType($parameterType->returnType);
			$minGroupSize = (int)(string)$targetType->range->minLength > 0 ? 1 : 0;
			$groupArrayType = $this->typeRegistry->array(
				$targetType->itemType,
				$minGroupSize,
				$targetType->range->maxLength
			);
			return $this->typeRegistry->map(
				$groupArrayType,
				$minGroupSize,
				$targetType->range->maxLength,
				$returnType
			);
		};
	}

	protected function getExecutor(): callable {
		return function(TupleValue $target, FunctionValue $parameter): Value {
			$groups = [];
			foreach ($target->values as $value) {
				/** @var StringValue $key */
				$key = $parameter->execute($value);
				$keyStr = $key->literalValue;
				if (!isset($groups[$keyStr])) {
					$groups[$keyStr] = [];
				}
				$groups[$keyStr][] = $value;
			}
			$result = [];
			foreach ($groups as $key => $group) {
				$result[$key] = $this->valueRegistry->tuple($group);
			}
			return $this->valueRegistry->record($result);
		};
	}

}
