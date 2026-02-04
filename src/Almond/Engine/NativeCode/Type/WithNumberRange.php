<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Type;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Expression\Expression;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Method\NativeMethod;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\IntegerType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\RealType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\TypeType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type as TypeInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\TypeRegistry;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\AtomValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\TypeValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\ValueRegistry;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Identifier\TypeName;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Range\MinusInfinity;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Range\NumberInterval;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Range\NumberIntervalEndpoint;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Range\PlusInfinity;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Execution\ExecutionException;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationErrorType;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationFactory;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationFailure;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationSuccess;
use Walnut\Lang\Almond\Engine\Implementation\Code\Type\Helper\BaseType;

final readonly class WithNumberRange implements NativeMethod {

	use BaseType;

	public function __construct(
		private ValidationFactory $validationFactory,
		private TypeRegistry $typeRegistry,
		private ValueRegistry $valueRegistry,
	) {}

	public function validate(TypeInterface $targetType, TypeInterface $parameterType, Expression|null $origin): ValidationSuccess|ValidationFailure {
		if ($targetType instanceof TypeType) {
			$refType = $this->toBaseType($targetType->refType);
			if ($refType instanceof IntegerType) {
				if ($parameterType->isSubtypeOf(
					$this->typeRegistry->core->integerNumberRange
				)) {
					return $this->validationFactory->validationSuccess(
						$this->typeRegistry->type($this->typeRegistry->integer())
					);
				}
				// @codeCoverageIgnoreStart
				return $this->validationFactory->error(
					ValidationErrorType::invalidParameterType,
					sprintf("[%s] Invalid parameter type: %s", __CLASS__, $parameterType),
					origin: $origin
				);
				// @codeCoverageIgnoreEnd
			}
			if ($refType instanceof RealType) {
				if ($parameterType->isSubtypeOf(
					$this->typeRegistry->core->realNumberRange
				)) {
					return $this->validationFactory->validationSuccess(
						$this->typeRegistry->type($this->typeRegistry->real())
					);
				}
				// @codeCoverageIgnoreStart
				return $this->validationFactory->error(
					ValidationErrorType::invalidParameterType,
					sprintf("[%s] Invalid parameter type: %s", __CLASS__, $parameterType),
					origin: $origin
				);
				// @codeCoverageIgnoreEnd
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
			if ($typeValue instanceof IntegerType || $typeValue instanceof RealType) {
				$prefix = $typeValue instanceof IntegerType ? 'Integer' : 'Real';
				if ($parameter->type->isSubtypeOf(
					$this->typeRegistry->typeByName(
						new TypeName($prefix . 'NumberRange')
					)
				)) {
					$intervals = [];
					foreach($parameter->value->values['intervals']->values as $interval) {
						['start' => $start, 'end' => $end] = $interval->value->values;
						$vStart = $start instanceof AtomValue ? MinusInfinity::value :
							new NumberIntervalEndpoint(
								$start->value->values['value']->literalValue,
								$start->value->values['inclusive']->literalValue
							);
						$vEnd = $end instanceof AtomValue ? PlusInfinity::value :
							new NumberIntervalEndpoint(
								$end->value->values['value']->literalValue,
								$end->value->values['inclusive']->literalValue
							);
						$intervals[] = new NumberInterval(
							$vStart,
							$vEnd
						);
					}
					$type = $typeValue instanceof IntegerType ?
						$this->typeRegistry->integerFull(...$intervals) :
						$this->typeRegistry->realFull(...$intervals
					);
					return $this->valueRegistry->type($type);
				}
			}
		}
		// @codeCoverageIgnoreStart
		throw new ExecutionException("Invalid parameter value");
		// @codeCoverageIgnoreEnd
	}
}
