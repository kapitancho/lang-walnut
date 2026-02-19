<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Array;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\ArrayType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\FunctionType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\FunctionValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\StringValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\TupleValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Range\PlusInfinity;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\CommonBase\ArrayGroupByIndexByBase;

final readonly class IndexBy extends ArrayGroupByIndexByBase {

	protected function getValidator(): callable {
		return function(ArrayType $targetType, FunctionType $parameterType, mixed $origin): Type {
			$returnType = $this->toBaseType($parameterType->returnType);
			$maxLength = $targetType->range->maxLength;
			$minLength = (string)$targetType->range->minLength === '0' ||
				($maxLength !== PlusInfinity::value && (string)$maxLength === '0') ? 0 : $targetType->range->minLength;
			return $this->typeRegistry->map(
				$targetType->itemType,
				$minLength,
				$maxLength,
				$returnType
			);
		};
	}

	protected function getExecutor(): callable {
		return function(TupleValue $target, FunctionValue $parameter): Value {
			$result = [];
			foreach ($target->values as $value) {
				/** @var StringValue $key */
				$key = $parameter->execute($value);
				$result[$key->literalValue] = $value;
			}
			return $this->valueRegistry->record($result);
		};
	}

}
