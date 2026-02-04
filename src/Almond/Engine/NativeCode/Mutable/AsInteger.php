<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Mutable;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Expression\Expression;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Method\MethodContext;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Method\NativeMethod;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\TypeRegistry;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\ValueRegistry;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Identifier\MethodName;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationErrorType;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationFactory;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationFailure;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationSuccess;

final readonly class AsInteger implements NativeMethod {

	public function __construct(
		private ValidationFactory $validationFactory,
		private TypeRegistry $typeRegistry,
		private ValueRegistry $valueRegistry,
		private MethodContext $methodContext,
	) {}

	public function validate(Type $targetType, Type $parameterType, Expression|null $origin): ValidationSuccess|ValidationFailure {
		$result = $this->methodContext->validateMethod(
			$targetType->valueType,
			new MethodName('asInteger'),
			$parameterType,
			$origin
		);
		if ($result instanceof ValidationFailure && ($result->errors[0]?->type === ValidationErrorType::undefinedMethod)) {
			return $this->validationFactory->error(
				ValidationErrorType::invalidTargetType,
				sprintf("[%s] Invalid target type: %s", __CLASS__, $targetType),
				origin: $origin
			);
		}
		return $result;
	}

	public function execute(Value $target, Value $parameter): Value {
		return $this->methodContext->executeMethod(
			$target->value,
			new MethodName('asInteger'),
			$parameter
		);
	}
}
