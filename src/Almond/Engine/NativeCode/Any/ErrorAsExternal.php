<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Any;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\NullType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\ResultType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\StringType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\CoreType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\ErrorValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\NullValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\SealedValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\StringValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\NativeMethod;

/** @extends NativeMethod<Type, NullType|StringType, Value, NullValue|StringValue> */
final readonly class ErrorAsExternal extends NativeMethod {

	protected function getValidator(): callable {
		return fn(Type $targetType, NullType|StringType $parameterType, mixed $origin): Type =>
			$targetType instanceof ResultType ?
				$this->typeRegistry->result(
					$targetType->returnType,
					$this->typeRegistry->core->externalError
				) :
				$targetType;
	}

	protected function getExecutor(): callable {
		return function(Value $target, NullValue|StringValue $parameter): Value {
			if ($target instanceof ErrorValue) {
				$errorValue = $target->errorValue;
				if (!($errorValue instanceof SealedValue && $errorValue->type->name->equals(
						CoreType::ExternalError->typeName()
					))) {
					$errorMessage = $parameter instanceof StringValue ? $parameter :
						$this->valueRegistry->string('Error');

					return $this->valueRegistry->error(
						$this->valueRegistry->core->externalError(
							$this->valueRegistry->record([
								'errorType' => $this->valueRegistry->string((string)$errorValue->type),
								'originalError' => $target,
								'errorMessage' => $errorMessage
							])
						)
					);
				}
			}
			return $target;
		};
	}
}