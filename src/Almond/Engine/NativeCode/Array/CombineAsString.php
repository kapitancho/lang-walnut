<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Array;

use BcMath\Number;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\ArrayType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\StringType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\StringValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\TupleValue;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Range\PlusInfinity;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\ArrayNativeMethod;

/** @extends ArrayNativeMethod<Type, StringType, StringValue> */
final readonly class CombineAsString extends ArrayNativeMethod {

	protected function getExpectedArrayItemType(): Type {
		return $this->typeRegistry->string();
	}

	protected function getValidator(): callable {
		return function (ArrayType $targetType, StringType $parameterType): StringType {
			if ($targetType->range->maxLength instanceof Number && (string)$targetType->range->maxLength === '0') {
				return $this->typeRegistry->stringSubset(['']);
			}
			$arrayItemType = $targetType->itemType;
			if ($arrayItemType instanceof StringType) {
				return $this->typeRegistry->string(
					$targetType->range->minLength > 0 ?
						$targetType->range->minLength * $arrayItemType->range->minLength +
						($targetType->range->minLength - 1) * $parameterType->range->minLength :
					0,
					$targetType->range->maxLength === PlusInfinity::value ||
					$arrayItemType->range->maxLength === PlusInfinity::value ||
					$parameterType->range->maxLength === PlusInfinity::value ?
						PlusInfinity::value :
						$targetType->range->maxLength * $arrayItemType->range->maxLength +
						($targetType->range->maxLength - 1) * $parameterType->range->maxLength
				);
			}
			return $this->typeRegistry->string();
		};
	}

	protected function getExecutor(): callable {
		return function(TupleValue $target, StringValue $parameter): StringValue {
			$result = [];
			foreach($target->values as $value) {
				/** @var StringValue $value */
				$result[] = $value->literalValue;
			}
			return $this->valueRegistry->string(implode($parameter->literalValue, $result));
		};
	}

}
