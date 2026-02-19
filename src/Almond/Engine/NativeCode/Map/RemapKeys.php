<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Map;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\AnyType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\FunctionType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\MapType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\ResultType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\ErrorValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\FunctionValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\RecordValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\StringValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Execution\ExecutionException;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\MapNativeMethod;

/** @extends MapNativeMethod<AnyType, MapType, FunctionType> */
final readonly class RemapKeys extends MapNativeMethod {

	protected function validateParameterType(Type $parameterType, Type $targetType): null|string {
		/** @var MapType $targetType */
		/** @var FunctionType $parameterType */

		if (!$targetType->keyType->isSubtypeOf($parameterType->parameterType)) {
			return sprintf(
				"The parameter type %s of the callback function is not a supertype of %s",
				$parameterType->parameterType,
				$targetType->keyType,
			);
		}
		$r = $parameterType->returnType;
		$returnType = $r instanceof ResultType ? $r->returnType : $r;
		return $returnType->isSubtypeOf($this->typeRegistry->string()) ?
			null :
			sprintf(
				"The return type %s of the callback function is not a subtype of String",
				$returnType
			);
	}

	protected function getValidator(): callable {
		return function(MapType $targetType, FunctionType $parameterType, mixed $origin): Type {
			$r = $parameterType->returnType;
			$errorType = $r instanceof ResultType ? $r->errorType : null;
			$returnType = $r instanceof ResultType ? $r->returnType : $r;

			$t = $this->typeRegistry->map(
				$targetType->itemType,
				$targetType->range->minLength > 0 ? 1 : 0,
				$targetType->range->maxLength,
				$returnType,
			);
			return $errorType ? $this->typeRegistry->result($t, $errorType) : $t;
		};
	}

	protected function getExecutor(): callable {
		return function(RecordValue $target, FunctionValue $parameter): Value {
			$values = $target->values;
			$result = [];
			foreach($values as $key => $value) {
				$r = $parameter->execute($this->valueRegistry->string($key));
				if ($r instanceof ErrorValue) {
					return $r;
				}
				if ($r instanceof StringValue) {
					$result[$r->literalValue] = $value;
				} else {
					// @codeCoverageIgnoreStart
					throw new ExecutionException("Invalid callback value");
					// @codeCoverageIgnoreEnd
				}
			}
			return $this->valueRegistry->record($result);
		};
	}

}
