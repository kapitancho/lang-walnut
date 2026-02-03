<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Array;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Expression\Expression;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Method\NativeMethod;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\ArrayType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\NothingType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\NullType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\TupleType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\TypeRegistry;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\NullValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\TupleValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\ValueRegistry;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Range\PlusInfinity;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Execution\ExecutionException;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationErrorType;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationFactory;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationFailure;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationSuccess;
use Walnut\Lang\Almond\Engine\Implementation\Code\Type\Helper\BaseType;

final readonly class WithoutFirst implements NativeMethod {
	use BaseType;

	public function __construct(
		private ValidationFactory $validationFactory,
		private TypeRegistry $typeRegistry,
		private ValueRegistry $valueRegistry,
	) {}

	public function validate(Type $targetType, Type $parameterType, Expression|null $origin): ValidationSuccess|ValidationFailure {
		$targetType = $this->toBaseType($targetType);
		if ($targetType instanceof TupleType && $this->toBaseType($parameterType) instanceof NullType) {
			if (count($targetType->types) === 0) {
				return $this->validationFactory->validationSuccess(
					$this->typeRegistry->result(
						$targetType->restType instanceof NothingType ?
							$this->typeRegistry->nothing :
							$this->typeRegistry->record([
								'element' => $targetType->restType,
								'array' => $this->typeRegistry->tuple([], $targetType->restType)
							], null),
						$this->typeRegistry->core->itemNotFound
					)
				);
			}
			$tupleTypes = $targetType->types;
			$firstType = array_shift($tupleTypes);
			return $this->validationFactory->validationSuccess(
				$this->typeRegistry->record([
					'element' => $firstType,
					'array' => $this->typeRegistry->tuple($tupleTypes, $targetType->restType)
				], null)
			);
		}
		$type = $targetType instanceof TupleType ? $targetType->asArrayType() : $targetType;
		if ($type instanceof ArrayType) {
			$pType = $this->toBaseType($parameterType);
			if ($pType instanceof NullType) {
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
					$type->range->minLength > 0 ? $returnType :
						$this->typeRegistry->result($returnType,
							$this->typeRegistry->core->itemNotFound
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
		if ($target instanceof TupleValue && $parameter instanceof NullValue) {
			$values = $target->values;
			if (count($values) === 0) {
				return $this->valueRegistry->error(
					$this->valueRegistry->core->itemNotFound
				);
			}
			$element = array_shift($values);
			return $this->valueRegistry->record([
				'element' => $element,
				'array' => $this->valueRegistry->tuple($values)
			]);
		}
		// @codeCoverageIgnoreStart
		throw new ExecutionException("Invalid target value");
		// @codeCoverageIgnoreEnd
	}
}
