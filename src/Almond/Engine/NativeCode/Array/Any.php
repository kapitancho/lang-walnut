<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Array;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\FunctionValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\TupleValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\CommonBase\ArrayAnyAll;

final readonly class Any extends ArrayAnyAll {

	protected function getExecutor(): callable {
		return function(TupleValue $target, FunctionValue $parameter): Value {
			$values = $target->values;
			$true = $this->valueRegistry->true;
			foreach ($values as $value) {
				$r = $parameter->execute($value);
				if ($true->equals($r)) {
					return $true;
				}
			}
			return $this->valueRegistry->false;
		};
	}

}
