<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\JsonValue;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\ResultType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\TypeType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\RecordValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\TypeValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Engine\Blueprint\Feature\Hydrator\HydrationError;
use Walnut\Lang\Almond\Engine\Blueprint\Feature\Hydrator\HydrationSuccess;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\NativeMethod;
use Walnut\Lang\Almond\Engine\Implementation\Feature\Hydrator\HydrationFactory;
use Walnut\Lang\Almond\Engine\Implementation\Feature\Hydrator\NamedTypeHydrator;

/** @extends NativeMethod<Type, TypeType, Value, TypeValue> */
final readonly class HydrateAs extends NativeMethod {

	protected function getValidator(): callable {
		return fn(Type $targetType, TypeType $parameterType): ResultType =>
			$this->typeRegistry->result(
				$parameterType->refType,
				$this->typeRegistry->core->hydrationError
			);
	}

	protected function getExecutor(): callable {
		return function(Value $target, TypeValue $parameter): Value {
			$hydrationRequest = new HydrationFactory(
				$this->typeRegistry,
				$this->valueRegistry,
				new NamedTypeHydrator(
					$this->methodContext
				),
				'value'
			)->forValue($target);
			$result = $parameter->typeValue->hydrate(
				$hydrationRequest
			);
			if ($result instanceof HydrationSuccess) {
				return $result->hydratedValue;
			}
			return $this->valueRegistry->error(
				$this->valueRegistry->core->hydrationError(
					$this->valueRegistry->record([
						'value' => $target,
						'errors' => $this->valueRegistry->tuple(
							array_map(
								fn(HydrationError $error): RecordValue => $this->valueRegistry->record([
									'hydrationPath' => $this->valueRegistry->string($error->path),
									'errorMessage' => $this->valueRegistry->string($error->message),
									'targetType' => $this->valueRegistry->type($error->targetType),
								]),
								$result->errors
							)
						)
					])
				)
			);
		};
	}

}
