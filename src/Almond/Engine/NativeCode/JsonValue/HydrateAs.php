<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\JsonValue;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Method\MethodContext;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Method\NativeMethod;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\TypeType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type as TypeInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\TypeRegistry;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\RecordValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\TypeValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\ValueRegistry;
use Walnut\Lang\Almond\Engine\Blueprint\Feature\Hydrator\HydrationError;
use Walnut\Lang\Almond\Engine\Blueprint\Feature\Hydrator\HydrationSuccess;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Execution\ExecutionException;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationErrorType;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationFactory;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationFailure;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationSuccess;
use Walnut\Lang\Almond\Engine\Implementation\Feature\Hydrator\HydrationFactory;
use Walnut\Lang\Almond\Engine\Implementation\Feature\Hydrator\NamedTypeHydrator;

final readonly class HydrateAs implements NativeMethod {

	public function __construct(
		private ValidationFactory $validationFactory,
		private TypeRegistry $typeRegistry,
		private ValueRegistry $valueRegistry,
		private MethodContext $methodContext,
	) {}


	public function validate(
		TypeInterface $targetType, TypeInterface $parameterType, mixed $origin
	): ValidationSuccess|ValidationFailure {
		if ($parameterType instanceof TypeType) {
			return $this->validationFactory->validationSuccess(
				$this->typeRegistry->result(
					$parameterType->refType,
					$this->typeRegistry->core->hydrationError
				)
			);
		}
		return $this->validationFactory->error(
			ValidationErrorType::invalidParameterType,
			sprintf("[%s] Invalid parameter type: %s", __CLASS__, $parameterType),
			$origin
		);
	}

	public function execute(Value $target, Value $parameter): Value {
		if ($parameter instanceof TypeValue) {
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
		}
		// @codeCoverageIgnoreStart
		throw new ExecutionException("Invalid parameter value");
		// @codeCoverageIgnoreEnd
	}

}