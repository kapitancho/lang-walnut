<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\EventBus;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\SealedType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\FunctionValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\NullValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\SealedValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\TupleValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Execution\ExecutionException;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\NativeMethod;

/** @extends NativeMethod<Type, Type, Value, NullValue> */
final readonly class Fire extends NativeMethod {

	protected function getValidator(): callable {
		return fn(SealedType $targetType, Type $parameterType, mixed $origin): Type =>
			$this->typeRegistry->result(
				$parameterType,
				$this->typeRegistry->core->externalError
			);
	}

	protected function getExecutor(): callable {
		return function (SealedValue $target, Value $parameter): Value {
			/** @var TupleValue|null $listeners */
			/** @phpstan-ignore-next-line */
			$listeners = $target->value->values['listeners'] ?? null;
			if ($listeners instanceof TupleValue) {
				foreach($listeners->values as $listener) {
					if ($listener instanceof FunctionValue) {
						if ($parameter->type->isSubtypeOf($listener->type->parameterType)) {
							$result = $listener->execute($parameter);
							if ($result->type->isSubtypeOf(
								$this->typeRegistry->result(
									$this->typeRegistry->nothing,
									$this->typeRegistry->core->externalError
								)
							)) {
								return $result;
							}
						}
					} else {
						// @codeCoverageIgnoreStart
						throw new ExecutionException("Invalid listener");
						// @codeCoverageIgnoreEnd
					}
				}
				return $parameter;
			}
			// @codeCoverageIgnoreStart
			throw new ExecutionException("Invalid target value");
			// @codeCoverageIgnoreEnd
		};
	}

}