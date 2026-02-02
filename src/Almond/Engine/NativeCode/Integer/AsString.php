<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Integer;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Expression\Expression;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Method\NativeMethod;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\IntegerType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\TypeRegistry;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\IntegerValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\ValueRegistry;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Range\MinusInfinity;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Range\PlusInfinity;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Execution\ExecutionException;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationErrorType;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationFactory;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationFailure;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationSuccess;
use Walnut\Lang\Almond\Engine\Implementation\Code\Type\Helper\BaseType;

final readonly class AsString implements NativeMethod {
	use BaseType;

	public function __construct(
		private ValidationFactory $validationFactory,
		private TypeRegistry $typeRegistry,
		private ValueRegistry $valueRegistry,
	) {}

	public function validate(Type $targetType, Type $parameterType, Expression|null $origin): ValidationSuccess|ValidationFailure {
		$targetType = $this->toBaseType($targetType);
		if ($targetType instanceof IntegerType) {
			$min = $targetType->numberRange->min;
			$max = $targetType->numberRange->max;
			$minLength = match(true) {
				$max !== PlusInfinity::value && $max->value <= 0 =>
					strlen((string)$max->value->sub($max->inclusive ? 0 : 1)),
				$min !== MinusInfinity::value && $min->value >= 0 =>
					strlen((string)$min->value->add($min->inclusive ? 0 : 1)),
				default => 1,
			};
			$maxLength = match(true) {
				$max === PlusInfinity::value, $min === MinusInfinity::value => PlusInfinity::value,
				default => max(
					1,
					(int)ceil(log10(1 + abs((int)(string)$max->value))),
					1 + (int)ceil( log10(1 + abs((int)(string)$min->value))),
				)
			};
			return $this->validationFactory->validationSuccess(
				$this->typeRegistry->string($minLength, $maxLength),
			);
		}
		return $this->validationFactory->error(
			ValidationErrorType::invalidTargetType,
			sprintf("[%s] Invalid target type: %s", __CLASS__, $targetType),
			origin: $origin
		);
	}

	public function execute(Value $target, Value $parameter): Value {
		if ($target instanceof IntegerValue) {
			return $this->valueRegistry->string((string)$target->literalValue);
		}
		throw new ExecutionException('Invalid target value');
	}
}