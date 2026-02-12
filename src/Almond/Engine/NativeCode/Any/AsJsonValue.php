<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Any;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\NullType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\UnionType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\NullValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Identifier\MethodName;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationFailure;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\NativeMethod;

/** @extends NativeMethod<Type, NullType, Value, NullValue> */
final readonly class AsJsonValue extends NativeMethod {

	private function getUnion(UnionType $type, NullType $parameterType, mixed $origin): Type|null {
		$union = [];
		foreach ($type->types as $unionType) {
			$result = $this->methodContext->validateMethod(
				$unionType,
				new MethodName('asJsonValue'),
				$parameterType,
				$origin
			);
			if ($result instanceof ValidationFailure) {
				return null;
			}
			$union[] = $result->type;
		}
		return $this->typeRegistry->union($union);
	}

	protected function getValidator(): callable {
		return function(Type $targetType, NullType $parameterType, mixed $origin): Type {
			if ($targetType instanceof UnionType) {
				$unionResult = $this->getUnion($targetType, $parameterType, $origin);
				if ($unionResult !== null) {
					return $unionResult;
				}
			}
			return $this->typeRegistry->result(
				$this->typeRegistry->core->jsonValue,
				$this->typeRegistry->core->invalidJsonValue
			);
		};
	}

	protected function getExecutor(): callable {
		return fn(Value $target, NullValue $parameter): Value =>
			$this->valueRegistry->error(
				$this->valueRegistry->core->invalidJsonValue(
					$this->valueRegistry->record([
						'value' => $target
					])
				)
			);
	}

}