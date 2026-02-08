<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Array;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Method\NativeMethod;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\ArrayType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\IntegerSubsetType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\IntegerType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\NothingType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\TupleType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\TypeRegistry;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\IntegerValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\TupleValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\ValueRegistry;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Range\NumberIntervalEndpoint;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Range\PlusInfinity;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Execution\ExecutionException;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationErrorType;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationFactory;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationFailure;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationSuccess;
use Walnut\Lang\Almond\Engine\Implementation\Code\Type\Helper\BaseType;

final readonly class WithoutByIndex implements NativeMethod {
	use BaseType;

	public function __construct(
		private ValidationFactory $validationFactory,
		private TypeRegistry $typeRegistry,
		private ValueRegistry $valueRegistry,
	) {}

	public function validate(Type $targetType, Type $parameterType, mixed $origin): ValidationSuccess|ValidationFailure {
		$type = $this->toBaseType($targetType);
		$pType = $this->toBaseType($parameterType);
		if ($type instanceof TupleType && $pType instanceof IntegerSubsetType && count($pType->subsetValues) === 1) {
			$param = (int)(string)$pType->subsetValues[0];
			if ($param >= 0) {
				$hasError = $param >= count($type->types);
				$paramType = $type->types[$param] ?? $type->restType;
				$paramItemTypes = $type->types;
				array_splice($paramItemTypes, $param, 1);
				$returnType = $paramType instanceof NothingType ?
					$paramType :
					$this->typeRegistry->record([
						'element' => $paramType,
						'array' => $hasError ? $type : $this->typeRegistry->tuple(
							$paramItemTypes,
							$type->restType
						)
					], null);
				return $this->validationFactory->validationSuccess(
					$hasError ?
						$this->typeRegistry->result(
							$returnType,
							$this->typeRegistry->core->indexOutOfRange
						) :
						$returnType
				);
			}
		}
		$type = $type instanceof TupleType ? $type->asArrayType() : $type;
		if ($type instanceof ArrayType) {
			if ($pType instanceof IntegerType) {
				$returnType = $this->typeRegistry->record([
					'element' => $type->itemType,
					'array' => $this->typeRegistry->array(
						$type->itemType,
						max(0, $type->range->minLength - 1),
						$type->range->maxLength === PlusInfinity::value ?
							PlusInfinity::value : max($type->range->maxLength - 1, 0)
					)
				], null);
				return $this->validationFactory->validationSuccess(
					$pType->numberRange->min instanceof NumberIntervalEndpoint &&
					($pType->numberRange->min->value + ($pType->numberRange->min->inclusive ? 0 : 1)) >= 0 &&
					$pType->numberRange->max instanceof NumberIntervalEndpoint &&
					($pType->numberRange->max->value - ($pType->numberRange->max->inclusive ? 0 : 1)) <
						$type->range->maxLength ?
						$returnType :
						$this->typeRegistry->result(
							$returnType,
							$this->typeRegistry->core->indexOutOfRange
					)
				);
			}
			return $this->validationFactory->error(
				ValidationErrorType::invalidParameterType,
				sprintf("[%s] Invalid parameter type: %s", __CLASS__, $parameterType),
				origin: $origin
			);
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
			if ($parameter instanceof IntegerValue) {
				$values = $target->values;
				$p = (string)$parameter->literalValue;
				if (!array_key_exists($p, $values)) {
					return $this->valueRegistry->error(
						$this->valueRegistry->core->indexOutOfRange(
							$this->valueRegistry->record(['index' => $parameter])
						)
					);
				}
				$removed = array_splice($values, (int)$p, 1);
				return $this->valueRegistry->record([
					'element' => $removed[0],
					'array' => $this->valueRegistry->tuple($values)
				]);
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
