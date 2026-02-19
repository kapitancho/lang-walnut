<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\PasswordString;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\BooleanType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\OpenType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\StringType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\BooleanValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\OpenValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\RecordValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\StringValue;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\NativeMethod;

/** @extends NativeMethod<OpenType, StringType, OpenValue, StringValue> */
final readonly class Verify extends NativeMethod {

	protected function getValidator(): callable {
		return fn(OpenType $targetType, StringType $parameterType): BooleanType =>
			$this->typeRegistry->boolean;
	}

	protected function getExecutor(): callable {
		return function(OpenValue $target, StringValue $parameter): BooleanValue {
			/** @var RecordValue $recordValue */
			$recordValue = $target->value;
			/** @var StringValue $passwordString */
			$passwordString = $recordValue->valueOf('value');
			return $this->valueRegistry->boolean(
				password_verify(
					$passwordString->literalValue,
					$parameter->literalValue,
				)
			);
		};
	}

}
