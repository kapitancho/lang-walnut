<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Map;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\FunctionType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\MapType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\ResultType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\ErrorValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\FunctionValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\RecordValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\CommonBase\MapCallbackBase;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\MapNativeMethod;

/** @extends MapNativeMethod<Type, FunctionType, FunctionValue> */
final readonly class Map extends MapCallbackBase {

	protected function getValidator(): callable {
		return function(MapType $targetType, FunctionType $parameterType, mixed $origin): Type {
			$r = $parameterType->returnType;
			$errorType = $r instanceof ResultType ? $r->errorType : null;
			$returnType = $r instanceof ResultType ? $r->returnType : $r;
			$t = $this->typeRegistry->map(
				$returnType,
				$targetType->range->minLength,
				$targetType->range->maxLength,
				$targetType->keyType
			);
			return $errorType ? $this->typeRegistry->result($t, $errorType) : $t;
		};
	}

	protected function getExecutor(): callable {
		return function(RecordValue $target, FunctionValue $parameter): Value {
			$result = [];
			foreach($target->values as $key => $value) {
				$r = $parameter->execute($value);
				if ($r instanceof ErrorValue) {
					return $r;
				}
				$result[$key] = $r;
			}
			return $this->valueRegistry->record($result);
		};
	}

}
