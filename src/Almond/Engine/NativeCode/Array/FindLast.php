<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Array;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\ArrayType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\FunctionType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\FunctionValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\TupleValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationFailure;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\CommonBase\ArrayFilterBase;

final readonly class FindLast extends ArrayFilterBase {

	protected function getValidator(): callable {
		return function(ArrayType $targetType, FunctionType $parameterType, mixed $origin): Type|ValidationFailure {
			return $this->typeRegistry->result(
				$targetType->itemType,
				$this->typeRegistry->core->itemNotFound
			);
		};
	}

	protected function getExecutor(): callable {
		return function(TupleValue $target, FunctionValue $parameter): Value {
			$true = $this->valueRegistry->true;
			$values = $target->values;
			for ($i = count($values) - 1; $i >= 0; $i--) {
				$r = $parameter->execute($values[$i]);
				if ($true->equals($r)) {
					return $values[$i];
				}
			}
			return $this->valueRegistry->error(
				$this->valueRegistry->core->itemNotFound
			);
		};
	}

}
