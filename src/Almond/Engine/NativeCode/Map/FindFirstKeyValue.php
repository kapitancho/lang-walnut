<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Map;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Expression\Expression;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\AnyType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\FunctionType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\MapType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\FunctionValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\RecordValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationErrorType;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationFailure;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\MapNativeMethod;
use Walnut\Lang\Almond\Engine\Implementation\Code\Type\BuiltIn\ResultType;

/** @extends MapNativeMethod<AnyType, FunctionType, FunctionValue> */
final readonly class FindFirstKeyValue extends MapNativeMethod {

	protected function isParameterTypeValid(Type $parameterType, callable $validator): bool {
		if (!parent::isParameterTypeValid($parameterType, $validator)) {
			return false;
		}
		/** @var FunctionType $parameterType */
		return $parameterType->returnType->isSubtypeOf($this->typeRegistry->boolean);
	}

	protected function getValidator(): callable {
		return function(MapType $targetType, FunctionType $parameterType, Expression|null $origin): ResultType|ValidationFailure {
			$kv = $this->typeRegistry->record([
				'key' => $targetType->keyType,
				'value' => $targetType->itemType
			], null);

			if ($kv->isSubtypeOf($parameterType->parameterType)) {
				return $this->typeRegistry->result(
					$kv,
					$this->typeRegistry->core->itemNotFound
				);
			}
			return $this->validationFactory->error(
				ValidationErrorType::invalidParameterType,
				sprintf(
					"The parameter type %s of the callback function is not a subtype of %s",
					$parameterType->parameterType,
					$kv,
				),
				origin: $origin
			);
		};
	}

	protected function getExecutor(): callable {
		return function(RecordValue $target, FunctionValue $parameter): Value {
			$true = $this->valueRegistry->true;
			foreach($target->values as $key => $value) {
				$val = $this->valueRegistry->record([
					'key' => $this->valueRegistry->string($key),
					'value' => $value
				]);
				$filterResult = $parameter->execute($val);
				if ($filterResult->equals($true)) {
					return $val;
				}
			}
			return $this->valueRegistry->error(
				$this->valueRegistry->core->itemNotFound
			);
		};
	}

}
