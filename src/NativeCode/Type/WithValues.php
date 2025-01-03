<?php

namespace Walnut\Lang\NativeCode\Type;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionException;
use Walnut\Lang\Blueprint\Code\Scope\TypedValue;
use Walnut\Lang\Blueprint\Common\Identifier\TypeNameIdentifier;
use Walnut\Lang\Blueprint\Common\Type\MetaTypeValue;
use Walnut\Lang\Blueprint\Function\MethodExecutionContext;
use Walnut\Lang\Blueprint\Function\NativeMethod;
use Walnut\Lang\Blueprint\Type\EnumerationType;
use Walnut\Lang\Blueprint\Type\IntegerType;
use Walnut\Lang\Blueprint\Type\MetaType;
use Walnut\Lang\Blueprint\Type\RealType;
use Walnut\Lang\Blueprint\Type\StringType;
use Walnut\Lang\Blueprint\Type\Type as TypeInterface;
use Walnut\Lang\Blueprint\Type\TypeType;
use Walnut\Lang\Blueprint\Value\EnumerationValue;
use Walnut\Lang\Blueprint\Value\TypeValue;
use Walnut\Lang\Implementation\Type\Helper\BaseType;

final readonly class WithValues implements NativeMethod {

	use BaseType;

	public function __construct(
		private MethodExecutionContext $context
	) {}

	public function analyse(
		TypeInterface $targetType,
		TypeInterface $parameterType,
	): TypeInterface {
		if ($targetType instanceof TypeType) {
			$refType = $this->toBaseType($targetType->refType);
			if ($refType instanceof IntegerType) {
				if ($parameterType->isSubtypeOf(
					$this->context->typeRegistry->array(
						$this->context->typeRegistry->integer(),
						1
					)
				)) {
					return $this->context->typeRegistry->type(
						$this->context->typeRegistry->metaType(MetaTypeValue::IntegerSubset)
					);
				}
				// @codeCoverageIgnoreStart
				throw new AnalyserException(sprintf("[%s] Invalid parameter type: %s", __CLASS__, $parameterType));
				// @codeCoverageIgnoreEnd
			}
			if ($refType instanceof RealType) {
				if ($parameterType->isSubtypeOf(
					$this->context->typeRegistry->array(
						$this->context->typeRegistry->real(),
						1
					)
				)) {
					return $this->context->typeRegistry->type(
						$this->context->typeRegistry->metaType(MetaTypeValue::RealSubset)
					);
				}
				// @codeCoverageIgnoreStart
				throw new AnalyserException(sprintf("[%s] Invalid parameter type: %s", __CLASS__, $parameterType));
				// @codeCoverageIgnoreEnd
			}
			if ($refType instanceof StringType) {
				if ($parameterType->isSubtypeOf(
					$this->context->typeRegistry->array(
						$this->context->typeRegistry->string(),
						1
					)
				)) {
					return $this->context->typeRegistry->type(
						$this->context->typeRegistry->metaType(MetaTypeValue::StringSubset)
					);
				}
				// @codeCoverageIgnoreStart
				throw new AnalyserException(sprintf("[%s] Invalid parameter type: %s", __CLASS__, $parameterType));
				// @codeCoverageIgnoreEnd
			}
			if ($refType instanceof EnumerationType) {
				if ($parameterType->isSubtypeOf(
					$this->context->typeRegistry->array(
						$refType,
						1
					)
				)) {
					return $this->context->typeRegistry->type($refType);
				}
				// @codeCoverageIgnoreStart
				throw new AnalyserException(sprintf("[%s] Invalid parameter type: %s", __CLASS__, $parameterType));
				// @codeCoverageIgnoreEnd
			}
			if ($refType instanceof MetaType && (
				$refType->value === MetaTypeValue::Enumeration ||
				$refType->value === MetaTypeValue::EnumerationSubset
			)) {
				if ($parameterType->isSubtypeOf(
					$this->context->typeRegistry->array(
						$this->context->typeRegistry->any,
						1
					)
				)) {
					return $this->context->typeRegistry->result(
						$this->context->typeRegistry->type(
							$this->context->typeRegistry->metaType(MetaTypeValue::EnumerationSubset)
						),
						$this->context->typeRegistry->withName(
							new TypeNameIdentifier('UnknownEnumerationValue')
						)
					);
				}
				// @codeCoverageIgnoreStart
				throw new AnalyserException(sprintf("[%s] Invalid parameter type: %s", __CLASS__, $parameterType));
				// @codeCoverageIgnoreEnd
			}
		}
		// @codeCoverageIgnoreStart
		throw new AnalyserException(sprintf("[%s] Invalid target type: %s", __CLASS__, $targetType));
		// @codeCoverageIgnoreEnd
	}

	public function execute(
		TypedValue $target,
		TypedValue $parameter
	): TypedValue {
		$targetValue = $target->value;

		if ($targetValue instanceof TypeValue) {
			$typeValue = $this->toBaseType($targetValue->typeValue);
			if ($typeValue instanceof IntegerType) {
				if ($parameter->type->isSubtypeOf(
					$this->context->typeRegistry->array(
						$this->context->typeRegistry->integer(),
						1
					)
				)) {
					$values = $this->toBaseValue($parameter->value)->values;
					$result = $this->context->typeRegistry->integerSubset($values);
					return TypedValue::forValue($this->context->valueRegistry->type($result));
				}
			}
			if ($typeValue instanceof RealType) {
				if ($parameter->type->isSubtypeOf(
					$this->context->typeRegistry->array(
						$this->context->typeRegistry->real(),
						1
					)
				)) {
					$values = $this->toBaseValue($parameter->value)->values;
					$result = $this->context->typeRegistry->realSubset($values);
					return TypedValue::forValue($this->context->valueRegistry->type($result));
				}
			}
			if ($typeValue instanceof StringType) {
				if ($parameter->type->isSubtypeOf(
					$this->context->typeRegistry->array(
						$this->context->typeRegistry->string(),
						1
					)
				)) {
					$values = $this->toBaseValue($parameter->value)->values;
					$result = $this->context->typeRegistry->stringSubset($values);
					return TypedValue::forValue($this->context->valueRegistry->type($result));
				}
			}
			if ($typeValue instanceof EnumerationType) {
				if ($parameter->type->isSubtypeOf(
					$this->context->typeRegistry->array(
						$this->context->typeRegistry->any,
						1
					)
				)) {
					$values = $this->toBaseValue($parameter->value)->values;
					$r = [];
					foreach($values as $value) {
						if ($value instanceof EnumerationValue && $value->enumeration == $typeValue) {
							$r[] = $value->name;
						} else {
							return TypedValue::forValue($this->context->valueRegistry->error(
								$this->context->valueRegistry->sealedValue(
									new TypeNameIdentifier('UnknownEnumerationValue'),
									$this->context->valueRegistry->record([
										'enumeration' => $this->context->valueRegistry->type($typeValue),
										'value' => $value
									])
								)
							));
						}
					}
					$result = $typeValue->subsetType($r);
					return TypedValue::forValue($this->context->valueRegistry->type($result));
				}
			}
		}
		// @codeCoverageIgnoreStart
		throw new ExecutionException("Invalid parameter value");
		// @codeCoverageIgnoreEnd
	}

}