<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Array;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\IntegerValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\RecordValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\TupleValue;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\CommonBase\ArrayPadLeftRight;

final readonly class PadLeft extends ArrayPadLeftRight {

	protected function getExecutor(): callable {
		return function(TupleValue $target, RecordValue $parameter): TupleValue {
			$values = $target->values;
			$paramValues = $parameter->values;
			/** @var IntegerValue $length */
			$length = $paramValues['length'];
			$padValue = $paramValues['value'];
			return $this->valueRegistry->tuple(
				array_pad(
					$values,
					-1 * (int)(string)$length->literalValue,
					$padValue
				)
			);
		};
	}

}
