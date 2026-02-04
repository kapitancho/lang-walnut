<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Mutable;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Expression\Expression;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Method\NativeMethod;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\ArrayType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\MutableType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\TypeRegistry;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\MutableValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\ValueRegistry;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Execution\ExecutionException;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationErrorType;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationFactory;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationFailure;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationSuccess;
use Walnut\Lang\Almond\Engine\Implementation\Code\Type\Helper\BaseType;

final readonly class POP implements NativeMethod {
	use BaseType;

	public function __construct(
		private ValidationFactory $validationFactory,
		private TypeRegistry $typeRegistry,
		private ValueRegistry $valueRegistry,
	) {}

	public function validate(Type $targetType, Type $parameterType, Expression|null $origin): ValidationSuccess|ValidationFailure {
		$t = $this->toBaseType($targetType);
		if ($t instanceof MutableType) {
			$valueType = $this->toBaseType($t->valueType);
			if ($valueType instanceof ArrayType && (int)(string)$valueType->range->minLength === 0) {
				return $this->validationFactory->validationSuccess(
					$this->typeRegistry->result(
						$valueType->itemType,
						$this->typeRegistry->core->itemNotFound
					)
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
		if ($target instanceof MutableValue) {
			$targetType = $this->toBaseType($target->targetType);
			if ($targetType instanceof ArrayType) {
				$values = $target->value->values;
				if (count($values) > 0) {
					$value = array_pop($values);
					$target->value = $this->valueRegistry->tuple($values);
					return $value;
				}
				return $this->valueRegistry->error(
					$this->valueRegistry->core->itemNotFound
				);
			}
		}
		// @codeCoverageIgnoreStart
		throw new ExecutionException("Invalid target value");
		// @codeCoverageIgnoreEnd
	}
}
