<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Function;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Expression\Expression;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Method\NativeMethod;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\FunctionType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type as TypeInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\TypeRegistry;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\FunctionValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\ValueRegistry;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Execution\ExecutionException;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationErrorType;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationFactory;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationFailure;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationSuccess;
use Walnut\Lang\Almond\Engine\Implementation\Code\Type\Helper\BaseType;
use Walnut\Lang\Almond\Engine\Implementation\Code\Type\Helper\TupleAsRecord;

final readonly class Invoke implements NativeMethod {
	use BaseType;
	use TupleAsRecord;

	public function __construct(
		private ValidationFactory $validationFactory,
		private TypeRegistry $typeRegistry,
		private ValueRegistry $valueRegistry,
	) {}

	public function validate(TypeInterface $targetType, TypeInterface $parameterType, Expression|null $origin): ValidationSuccess|ValidationFailure {
		/*
		$baseTargetType = $this->toTargetBaseType(
			$targetType,
			$typeRegistry->metaType(MetaTypeValue::Function)
		);
		if (!$baseTargetType) {
			// @codeCoverageIgnoreStart
			throw new AnalyserException(
				sprintf("Invalid target type: %s, expected a function", $targetType));
			// @codeCoverageIgnoreEnd
		}

		$p = $baseTargetType->parameterType;
		$parameterType = $this->adjustParameterType(
			$typeRegistry,
			$p,
			$parameterType,
		);
		*/
		$targetType = $this->toBaseType($targetType);
		if ($targetType instanceof FunctionType) {
			$p = $targetType->parameterType;
			$parameterType = $this->adjustParameterType(
				$this->typeRegistry,
				$p,
				$parameterType,
			);

			if ($parameterType->isSubtypeOf($targetType->parameterType)) {
				return $this->validationFactory->validationSuccess($targetType->returnType);
			}
			return $this->validationFactory->error(
				ValidationErrorType::invalidParameterType,
				sprintf("Invalid parameter type: %s, %s expected (target is %s)",
					$parameterType, $targetType->parameterType, $targetType
				),
				$origin
			);
		}
		// @codeCoverageIgnoreStart
		return $this->validationFactory->error(
			ValidationErrorType::invalidTargetType,
			sprintf("Invalid target type: %s, expected a function", $targetType),
			$origin
		);
		// @codeCoverageIgnoreEnd
	}

	/** @throws ExecutionException */
	public function execute(Value $target, Value $parameter): Value {
		if ($target instanceof FunctionValue) {
			$parameter = $this->adjustParameterValue(
				$this->valueRegistry,
				$target->type->parameterType,
				$parameter,
			);
			return $target->execute($parameter);
		}
		// @codeCoverageIgnoreStart
		throw new ExecutionException(
			sprintf("Invalid target value: %s, expected a function", $target->type)
		);
		// @codeCoverageIgnoreEnd
	}
}