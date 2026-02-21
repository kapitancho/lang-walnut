<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Any;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\NullType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\RecordType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\BooleanValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\NullValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\RecordValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\NativeMethod;

/** @extends NativeMethod<Type, NullType|RecordType, Value, NullValue|RecordValue> */
final readonly class DUMP extends NativeMethod {

	protected function validateParameterType(Type $parameterType, Type $targetType): null|string {
		if ($parameterType instanceof RecordType) {
			$expectedParamType = $this->typeRegistry->record([
				'html' => $this->typeRegistry->optionalKey($this->typeRegistry->boolean),
				'newLine' => $this->typeRegistry->optionalKey($this->typeRegistry->boolean),
			], null);
			return $parameterType->isSubtypeOf($expectedParamType) ?
				null : sprintf(
					"The parameter type %s is not a subtype of the expected record type %s",
					$parameterType, $expectedParamType
				);
		}
		return null;
	}

	protected function getValidator(): callable {
		return fn(Type $targetType, NullType|RecordType $parameterType, mixed $origin): Type => $targetType;
	}

	protected function getExecutor(): callable {
		return function(Value $target, NullValue|RecordValue $parameter): Value {
			$html = false;
			$newLine = false;
			if ($parameter instanceof RecordValue) {
				$htmlVal = $parameter->valueOf('html');
				if ($htmlVal instanceof BooleanValue) {
					$html = $htmlVal->literalValue;
				}
				$newLineVal = $parameter->valueOf('newLine');
				if ($newLineVal instanceof BooleanValue) {
					$newLine = $newLineVal->literalValue;
				}
			}
			$output = (string)$target;
			if ($html) {
				$output = htmlspecialchars($output);
			}
			echo $output;
			if ($newLine) {
				echo ($html ? '<br/>' : ''), PHP_EOL;
			}
			return $target;
		};
	}

}
