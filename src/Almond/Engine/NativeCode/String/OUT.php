<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\String;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\NullType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\RecordType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\StringType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\BooleanValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\NullValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\RecordValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\StringValue;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\NativeMethod;

/** @extends NativeMethod<StringType, NullType, StringValue, NullValue> */
final readonly class OUT extends NativeMethod {

	protected function validateParameterType(Type $parameterType, Type $targetType): null|string {
		if ($parameterType instanceof RecordType) {
			$expectedParamType = $this->typeRegistry->record([
				'html' => $this->typeRegistry->boolean,
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
		return fn(StringType $targetType, NullType|RecordType $parameterType): StringType =>
			$targetType;
	}

	protected function getExecutor(): callable {
		return function(StringValue $target, NullValue|RecordValue $parameter): StringValue {
			$html = false;
			if ($parameter instanceof RecordValue) {
				$htmlVal = $parameter->valueOf('html');
				if ($htmlVal instanceof BooleanValue) {
					$html = $htmlVal->literalValue;
				}
			}
			$output = $target->literalValue;
			if ($html) {
				$output = nl2br(htmlspecialchars($output));
			}
			echo $output;
			return $target;
		};
	}

}
