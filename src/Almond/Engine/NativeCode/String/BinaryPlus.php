<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\String;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Expression\Expression;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Method\MethodContext;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Method\NativeMethod;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\StringType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\TypeRegistry;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\StringValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\ValueRegistry;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Range\PlusInfinity;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Execution\ExecutionException;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationErrorType;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationFactory;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationFailure;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationSuccess;
use Walnut\Lang\Almond\Engine\Implementation\Code\Type\Helper\BaseType;
use Walnut\Lang\Almond\Engine\Implementation\Code\Value\ValueConverter;

final readonly class BinaryPlus implements NativeMethod {
	use BaseType;

	private ValueConverter $valueConverter;

	public function __construct(
		private ValidationFactory $validationFactory,
		private TypeRegistry $typeRegistry,
		private ValueRegistry $valueRegistry,
		private MethodContext $methodContext,
	) {
		$this->valueConverter = new ValueConverter(
			$this->validationFactory,
			$this->typeRegistry,
			$this->valueRegistry,
			$this->methodContext,
		);
	}

	public function validate(Type $targetType, Type $parameterType, Expression|null $origin): ValidationSuccess|ValidationFailure {
		$targetType = $this->toBaseType($targetType);
		if ($targetType instanceof StringType) {
			$parameterType = $this->toBaseType($parameterType);
			if ($parameterType instanceof StringType) {
				return $this->validationFactory->validationSuccess($this->typeRegistry->string(
					$targetType->range->minLength + $parameterType->range->minLength,
					$targetType->range->maxLength === PlusInfinity::value ||
					$parameterType->range->maxLength === PlusInfinity::value ? PlusInfinity::value :
						$targetType->range->maxLength + $parameterType->range->maxLength
				));
			}
			if ($parameterType->isSubtypeOf(
				$this->typeRegistry->shape(
					$this->typeRegistry->string()
				)
			)) {
				return $this->validationFactory->validationSuccess($this->typeRegistry->string());
			}
			return $this->validationFactory->error(
				ValidationErrorType::invalidParameterType,
				sprintf("[%s] Invalid parameter type: %s", __CLASS__, $parameterType),
				$origin
			);
		}
		return $this->validationFactory->error(
			ValidationErrorType::invalidTargetType,
			sprintf("[%s] Invalid target type: %s", __CLASS__, $targetType),
			$origin
		);
	}

	public function execute(Value $target, Value $parameter): Value {
		if ($target instanceof StringValue) {
			$value = $parameter instanceof StringValue ?
				$parameter : $this->valueConverter->convertValueToShape(
					$parameter,
					$this->typeRegistry->string()
				);
			if ($value instanceof StringValue) {
				$result = $target->literalValue . $value->literalValue;
				return $this->valueRegistry->string($result);
			}
			// @codeCoverageIgnoreStart
			throw new ExecutionException("Invalid parameter value");
			// @codeCoverageIgnoreEnd
		}
		// @codeCoverageIgnoreStart
		throw new ExecutionException("Invalid target value");
		// @codeCoverageIgnoreEnd
	}
}