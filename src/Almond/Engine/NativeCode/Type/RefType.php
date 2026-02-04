<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Type;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Expression\Expression;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Method\NativeMethod;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\ShapeType;
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

final readonly class RefType implements NativeMethod {

	use BaseType;

	public function __construct(
		private ValidationFactory $validationFactory,
		private TypeRegistry $typeRegistry,
		private ValueRegistry $valueRegistry,
	) {}

	public function validate(TypeInterface $targetType, TypeInterface $parameterType, Expression|null $origin): ValidationSuccess|ValidationFailure {
		if ($targetType instanceof TypeType) {
			$refType = $this->toBaseType($targetType->refType);
			if ($refType instanceof TypeType || $refType instanceof ShapeType) {
				return $this->validationFactory->validationSuccess(
					$this->typeRegistry->type($refType->refType)
				);
			}
		}
		// @codeCoverageIgnoreStart
		return $this->validationFactory->error(
			ValidationErrorType::invalidTargetType,
			sprintf("[%s] Invalid target type: %s", __CLASS__, $targetType),
			origin: $origin
		);
		// @codeCoverageIgnoreEnd
	}

	public function execute(Value $target, Value $parameter): Value {
		if ($target instanceof TypeValue) {
			$typeValue = $this->toBaseType($target->typeValue);
			if ($typeValue instanceof TypeType || $typeValue instanceof ShapeType) {
				return $this->valueRegistry->type($typeValue->refType);
			}
		}
		// @codeCoverageIgnoreStart
		throw new ExecutionException("Invalid parameter value");
		// @codeCoverageIgnoreEnd
	}
}
