<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Type;

use BcMath\Number;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Method\NativeMethod;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\EnumerationType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\IntegerType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\MetaType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\RealType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\StringType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\TypeType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\MetaTypeValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type as TypeInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\TypeRegistry;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\EnumerationValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\IntegerValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\RealValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\StringValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\TypeValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\ValueRegistry;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Execution\ExecutionException;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationErrorType;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationFactory;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationFailure;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationSuccess;
use Walnut\Lang\Almond\Engine\Implementation\Code\Type\Helper\BaseType;

final readonly class WithValues implements NativeMethod {

	use BaseType;

	public function __construct(
		private ValidationFactory $validationFactory,
		private TypeRegistry $typeRegistry,
		private ValueRegistry $valueRegistry,
	) {}

	public function validate(TypeInterface $targetType, TypeInterface $parameterType, mixed $origin): ValidationSuccess|ValidationFailure {
		if ($targetType instanceof TypeType) {
			$refType = $this->toBaseType($targetType->refType);
			if ($refType instanceof IntegerType) {
				if ($parameterType->isSubtypeOf(
					$this->typeRegistry->array(
						$this->typeRegistry->integer(),
						1
					)
				)) {
					return $this->validationFactory->validationSuccess(
						$this->typeRegistry->type(
							$this->typeRegistry->metaType(MetaTypeValue::IntegerSubset)
						)
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
					$this->typeRegistry->array(
						$this->typeRegistry->real(),
						1
					)
				)) {
					return $this->validationFactory->validationSuccess(
						$this->typeRegistry->type(
							$this->typeRegistry->metaType(MetaTypeValue::RealSubset)
						)
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
			if ($refType instanceof StringType) {
				if ($parameterType->isSubtypeOf(
					$this->typeRegistry->array(
						$this->typeRegistry->string(),
						1
					)
				)) {
					return $this->validationFactory->validationSuccess(
						$this->typeRegistry->type(
							$this->typeRegistry->metaType(MetaTypeValue::StringSubset)
						)
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
			if ($refType instanceof EnumerationType) {
				if ($parameterType->isSubtypeOf(
					$this->typeRegistry->array(
						$refType,
						1
					)
				)) {
					return $this->validationFactory->validationSuccess(
						$this->typeRegistry->type($refType)
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
			if ($refType instanceof MetaType && (
				$refType->value === MetaTypeValue::Enumeration
			)) {
				if ($parameterType->isSubtypeOf(
					$this->typeRegistry->array(
						$this->typeRegistry->any,
						1
					)
				)) {
					return $this->validationFactory->validationSuccess(
						$this->typeRegistry->result(
							$this->typeRegistry->type(
								$this->typeRegistry->metaType(MetaTypeValue::EnumerationSubset)
							),
							$this->typeRegistry->core->unknownEnumerationValue
						)
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
			if ($typeValue instanceof IntegerType) {
				if ($parameter->type->isSubtypeOf(
					$this->typeRegistry->array(
						$this->typeRegistry->integer(),
						1
					)
				)) {
					/** @var list<IntegerValue> $values */
					$values = $parameter->values;
					$result = $this->typeRegistry->integerSubset(
						array_map(fn(IntegerValue $value): Number => $value->literalValue, $values)
					);
					return $this->valueRegistry->type($result);
				}
			}
			if ($typeValue instanceof RealType) {
				if ($parameter->type->isSubtypeOf(
					$this->typeRegistry->array(
						$this->typeRegistry->real(),
						1
					)
				)) {
					/** @var list<RealValue|IntegerValue> $values */
					$values = $parameter->values;
					$result = $this->typeRegistry->realSubset(
						array_map(fn(RealValue|IntegerValue $value): Number => $value->literalValue, $values)
					);
					return $this->valueRegistry->type($result);
				}
			}
			if ($typeValue instanceof StringType) {
				if ($parameter->type->isSubtypeOf(
					$this->typeRegistry->array(
						$this->typeRegistry->string(),
						1
					)
				)) {
					/** @var list<StringValue> $values */
					$values = $parameter->values;
					$result = $this->typeRegistry->stringSubset(
						array_map(fn(StringValue $value): string => $value->literalValue, $values)
					);
					return $this->valueRegistry->type($result);
				}
			}
			if ($typeValue instanceof EnumerationType) {
				if ($parameter->type->isSubtypeOf(
					$this->typeRegistry->array(
						$this->typeRegistry->any,
						1
					)
				)) {
					$values = $parameter->values;
					$r = [];
					foreach($values as $value) {
						if ($value instanceof EnumerationValue && $value->enumeration == $typeValue) {
							$r[] = $value->name;
						} else {
							return $this->valueRegistry->error(
								$this->valueRegistry->core->unknownEnumerationValue(
									$this->valueRegistry->record([
										'enumeration' => $this->valueRegistry->type($typeValue),
										'value' => $value
									])
								)
							);
						}
					}
					$result = $typeValue->subsetType($r);
					return $this->valueRegistry->type($result);
				}
			}
		}
		// @codeCoverageIgnoreStart
		throw new ExecutionException("Invalid parameter value");
		// @codeCoverageIgnoreEnd
	}
}
