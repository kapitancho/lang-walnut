<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Array;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\ArrayType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\FunctionType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\ResultType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\ErrorValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\FunctionValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\TupleValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\CommonBase\ArrayFilterBase;

final readonly class Partition extends ArrayFilterBase {

	protected function getValidator(): callable {
		return function(ArrayType $targetType, FunctionType $parameterType, mixed $origin): Type {
			$pType = $this->toBaseType($parameterType->returnType);
			$partitionType = $this->typeRegistry->array($targetType->itemType, 0, $targetType->range->maxLength);
			$returnType = $this->typeRegistry->record([
				'matching' => $partitionType,
				'notMatching' => $partitionType
			], null);

			return $pType instanceof ResultType ? $this->typeRegistry->result(
				$returnType,
				$pType->errorType
			) : $returnType;
		};
	}

	protected function getExecutor(): callable {
		return function(TupleValue $target, FunctionValue $parameter): Value {
			$matching = [];
			$notMatching = [];
			$true = $this->valueRegistry->true;
			foreach ($target->values as $value) {
				$r = $parameter->execute($value);
				if ($r instanceof ErrorValue) {
					return $r;
				}
				if ($true->equals($r)) {
					$matching[] = $value;
				} else {
					$notMatching[] = $value;
				}
			}
			return $this->valueRegistry->record([
				'matching' => $this->valueRegistry->tuple($matching),
				'notMatching' => $this->valueRegistry->tuple($notMatching),
			]);
		};
	}

}
