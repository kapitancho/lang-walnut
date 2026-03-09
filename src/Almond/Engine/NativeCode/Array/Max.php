<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Array;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\ArrayType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\NullType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\IntegerValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\NullValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\RealValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\TupleValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\CommonBase\ArrayMinMax;

final readonly class Max extends ArrayMinMax {

	protected function getValidator(): callable {
		return fn(ArrayType $targetType, NullType $parameterType): Type => $targetType->itemType;
	}

	protected function getExecutor(): callable {
		return function(TupleValue $target, NullValue $parameter): Value {
			/** @var RealValue $bestV */
			$bestV = $target->values[0];
			$best = $bestV->literalValue;
			/** @var RealValue $item */
			foreach ($target->values as $item) {
				$value = $item->literalValue;
				if ($value > $best) {
					$bestV = $item;
					$best = $value;
				}
			}
			return $bestV;
		};
	}

}
