<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\PasswordString;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\OpenType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\NullType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\StringType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\OpenValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\NullValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\RecordValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\StringValue;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\NativeMethod;

/** @extends NativeMethod<OpenType, NullType, OpenValue, NullValue> */
final readonly class Hash extends NativeMethod {

	protected function getValidator(): callable {
		return fn(OpenType $targetType, NullType $parameterType): StringType =>
			$this->typeRegistry->string(24, 255);
	}

	protected function getExecutor(): callable {
		return function(OpenValue $target, NullValue $parameter): StringValue {
			/** @var RecordValue $recordValue */
			$recordValue = $target->value;
			/** @var StringValue $passwordString */
			$passwordString = $recordValue->valueOf('value');
			return $this->valueRegistry->string(
				password_hash($passwordString->literalValue, PASSWORD_DEFAULT)
			);
		};
	}

}
