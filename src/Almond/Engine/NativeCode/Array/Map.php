<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Array;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\ArrayType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\FunctionType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\OptionalType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\ResultType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\EmptyValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\ErrorValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\FunctionValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\TupleValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\CommonBase\ArrayCallbackBase;

final readonly class Map extends ArrayCallbackBase {

	protected function getValidator(): callable {
		return function(ArrayType $targetType, FunctionType $parameterType, mixed $origin): Type {
			$r = $parameterType->returnType;
			$errorType = $r instanceof ResultType ? $r->errorType : null;
			$returnType = $r instanceof ResultType ? $r->returnType : $r;
			if ($isOptional = $returnType instanceof OptionalType) {
				$returnType = $returnType->valueType;
			}
			$t = $this->typeRegistry->array(
				$returnType,
				$isOptional ? 0 : $targetType->range->minLength,
				$targetType->range->maxLength,
			);
			return $errorType ? $this->typeRegistry->result($t, $errorType) : $t;
		};
	}

	protected function getExecutor(): callable {
		return function(TupleValue $target, FunctionValue $parameter): Value {
			$result = [];
			foreach($target->values as $value) {
				$r = $parameter->execute($value);
				if ($r instanceof ErrorValue) {
					return $r;
				}
				if ($r instanceof EmptyValue) {
					continue;
				}
				$result[] = $r;
			}
			return $this->valueRegistry->tuple($result);
		};
	}

}
