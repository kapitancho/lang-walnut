<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Any;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Expression\Expression;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Method\MethodContext;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Method\NativeMethod;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\TypeType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type as TypeInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\TypeRegistry;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\TypeValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\ValueRegistry;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Execution\ExecutionException;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationErrorType;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationFactory;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationFailure;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationSuccess;
use Walnut\Lang\Almond\Engine\Implementation\Code\Type\Helper\BaseType;
use Walnut\Lang\Almond\Engine\Implementation\Code\Value\ValueConverter;

final readonly class Shape implements NativeMethod {
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


	public function validate(
		TypeInterface $targetType, TypeInterface $parameterType, Expression|null $origin
	): ValidationSuccess|ValidationFailure {
		$targetType = $this->toBaseType($targetType);
		$parameterType = $this->toBaseType($parameterType);
		if ($parameterType instanceof TypeType) {
			return $this->valueConverter->analyseConvertValueToShape(
				$targetType,
				$parameterType->refType,
				$origin
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
			return $this->valueConverter->convertValueToShape(
				$target,
				$parameter->typeValue
			);
		}
		// @codeCoverageIgnoreStart
		throw new ExecutionException("Invalid parameter value");
		// @codeCoverageIgnoreEnd
	}

}