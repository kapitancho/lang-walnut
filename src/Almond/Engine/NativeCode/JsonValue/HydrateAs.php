<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\JsonValue;

use Walnut\Lang\Almond\Engine\Blueprint\Execution\ExecutionException;
use Walnut\Lang\Almond\Engine\Blueprint\Expression\Expression;
use Walnut\Lang\Almond\Engine\Blueprint\Hydrator\HydrationError;
use Walnut\Lang\Almond\Engine\Blueprint\Hydrator\HydrationSuccess;
use Walnut\Lang\Almond\Engine\Blueprint\Method\MethodContext;
use Walnut\Lang\Almond\Engine\Blueprint\Method\NativeMethod;
use Walnut\Lang\Almond\Engine\Blueprint\Registry\TypeRegistry;
use Walnut\Lang\Almond\Engine\Blueprint\Registry\ValueRegistry;
use Walnut\Lang\Almond\Engine\Blueprint\Type\Type as TypeInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Type\TypeType;
use Walnut\Lang\Almond\Engine\Blueprint\Validation\ValidationErrorType;
use Walnut\Lang\Almond\Engine\Blueprint\Validation\ValidationFactory;
use Walnut\Lang\Almond\Engine\Blueprint\Validation\ValidationFailure;
use Walnut\Lang\Almond\Engine\Blueprint\Validation\ValidationSuccess;
use Walnut\Lang\Almond\Engine\Blueprint\Value\TypeValue;
use Walnut\Lang\Almond\Engine\Blueprint\Value\Value;
use Walnut\Lang\Almond\Engine\Implementation\Hydrator\HydrationFactory;
use Walnut\Lang\Almond\Engine\Implementation\Hydrator\NamedTypeHydrator;

final readonly class HydrateAs implements NativeMethod {

	public function __construct(
		private ValidationFactory $validationFactory,
		private TypeRegistry $typeRegistry,
		private ValueRegistry $valueRegistry,
		private MethodContext $methodContext,
	) {}


	public function validate(
		TypeInterface $targetType, TypeInterface $parameterType, Expression|null $origin
	): ValidationSuccess|ValidationFailure {
		if ($parameterType instanceof TypeType) {
			return $this->validationFactory->validationSuccess(
				$this->typeRegistry->result(
					$parameterType->refType,
					//TODO: hydration error instead
					$this->typeRegistry->string()
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
			//TODO : hydration error
			return $this->valueRegistry->string(
				implode(' / ', array_map(
					fn (HydrationError $error) => sprintf(
						"%s (%s): %s",
						$error->path,
						$error->targetType,
						$error->message,
					),
					$result->errors
				))
			);
		}
		// @codeCoverageIgnoreStart
		throw new ExecutionException("Invalid parameter value");
		// @codeCoverageIgnoreEnd
	}

}