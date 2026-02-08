<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Array;

use BcMath\Number;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Method\NativeMethod;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\ArrayType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\IntegerType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\RealType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\TupleType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\TypeRegistry;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\TupleValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\ValueRegistry;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Range\MinusInfinity;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Range\NumberIntervalEndpoint;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Range\NumberInterval;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Range\PlusInfinity;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Execution\ExecutionException;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationErrorType;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationFactory;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationFailure;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationSuccess;
use Walnut\Lang\Almond\Engine\Implementation\Code\Type\Helper\BaseType;

final readonly class Product implements NativeMethod {
	use BaseType;

	public function __construct(
		private ValidationFactory $validationFactory,
		private TypeRegistry $typeRegistry,
		private ValueRegistry $valueRegistry,
	) {}

	public function validate(Type $targetType, Type $parameterType, mixed $origin): ValidationSuccess|ValidationFailure {
		$targetType = $this->toBaseType($targetType);
		if ($targetType instanceof TupleType) {
			$targetType = $targetType->asArrayType();
		}
		if ($targetType instanceof ArrayType) {
			$itemType = $this->toBaseType($targetType->itemType);
			if ($itemType->isSubtypeOf(
				$this->typeRegistry->union([
					$this->typeRegistry->integer(),
					$this->typeRegistry->real()
				])
			)) {
				if ($itemType instanceof RealType || $itemType instanceof IntegerType) {
					$start = match(true) {
						$itemType->numberRange->min !== MinusInfinity::value &&
							$itemType->numberRange->min->value > 0 => new NumberIntervalEndpoint(new Number(0), false),
						default => MinusInfinity::value,
					};
					$end = match(true) {
						$itemType->numberRange->max !== PlusInfinity::value && $targetType->range->maxLength !== PlusInfinity::value =>
						new NumberIntervalEndpoint(
							$itemType->numberRange->max->value->pow($targetType->range->maxLength),
							$itemType->numberRange->max->inclusive
						),
						default => PlusInfinity::value,
					};
					$interval = new NumberInterval($start, $end);
					return $this->validationFactory->validationSuccess(
						$itemType instanceof RealType ?
							$this->typeRegistry->realFull($interval) :
							$this->typeRegistry->integerFull($interval)
					);
				}
				return $this->validationFactory->validationSuccess(
					$this->typeRegistry->real()
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
		if ($target instanceof TupleValue) {
			$product = 1;
			$hasReal = false;
			foreach ($target->values as $item) {
				$v = $item->literalValue;
				if (str_contains((string)$v, '.')) {
					$hasReal = true;
				}
				$product *= $v;
			}
			return $hasReal ? $this->valueRegistry->real($product) : $this->valueRegistry->integer($product);
		}
		// @codeCoverageIgnoreStart
		throw new ExecutionException("Invalid target value");
		// @codeCoverageIgnoreEnd
	}
}
