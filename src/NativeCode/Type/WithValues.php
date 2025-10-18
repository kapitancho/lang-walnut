<?php

namespace Walnut\Lang\NativeCode\Type;

use BcMath\Number;
use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionException;
use Walnut\Lang\Blueprint\Common\Identifier\TypeNameIdentifier;
use Walnut\Lang\Blueprint\Common\Type\MetaTypeValue;
use Walnut\Lang\Blueprint\Function\NativeMethod;
use Walnut\Lang\Blueprint\Program\Registry\ProgramRegistry;
use Walnut\Lang\Blueprint\Type\EnumerationType;
use Walnut\Lang\Blueprint\Type\IntegerType;
use Walnut\Lang\Blueprint\Type\MetaType;
use Walnut\Lang\Blueprint\Type\RealType;
use Walnut\Lang\Blueprint\Type\StringType;
use Walnut\Lang\Blueprint\Type\Type as TypeInterface;
use Walnut\Lang\Blueprint\Type\TypeType;
use Walnut\Lang\Blueprint\Value\EnumerationValue;
use Walnut\Lang\Blueprint\Value\IntegerValue;
use Walnut\Lang\Blueprint\Value\RealValue;
use Walnut\Lang\Blueprint\Value\StringValue;
use Walnut\Lang\Blueprint\Value\Value;
use Walnut\Lang\Blueprint\Value\TypeValue;
use Walnut\Lang\Implementation\Type\Helper\BaseType;

final readonly class WithValues implements NativeMethod {

	use BaseType;

	public function analyse(
		ProgramRegistry $programRegistry,
		TypeInterface $targetType,
		TypeInterface $parameterType,
	): TypeInterface {
		if ($targetType instanceof TypeType) {
			$refType = $this->toBaseType($targetType->refType);
			if ($refType instanceof IntegerType) {
				if ($parameterType->isSubtypeOf(
					$programRegistry->typeRegistry->array(
						$programRegistry->typeRegistry->integer(),
						1
					)
				)) {
					return $programRegistry->typeRegistry->type(
						$programRegistry->typeRegistry->metaType(MetaTypeValue::IntegerSubset)
					);
				}
				// @codeCoverageIgnoreStart
				throw new AnalyserException(sprintf("[%s] Invalid parameter type: %s", __CLASS__, $parameterType));
				// @codeCoverageIgnoreEnd
			}
			if ($refType instanceof RealType) {
				if ($parameterType->isSubtypeOf(
					$programRegistry->typeRegistry->array(
						$programRegistry->typeRegistry->real(),
						1
					)
				)) {
					return $programRegistry->typeRegistry->type(
						$programRegistry->typeRegistry->metaType(MetaTypeValue::RealSubset)
					);
				}
				// @codeCoverageIgnoreStart
				throw new AnalyserException(sprintf("[%s] Invalid parameter type: %s", __CLASS__, $parameterType));
				// @codeCoverageIgnoreEnd
			}
			if ($refType instanceof StringType) {
				if ($parameterType->isSubtypeOf(
					$programRegistry->typeRegistry->array(
						$programRegistry->typeRegistry->string(),
						1
					)
				)) {
					return $programRegistry->typeRegistry->type(
						$programRegistry->typeRegistry->metaType(MetaTypeValue::StringSubset)
					);
				}
				// @codeCoverageIgnoreStart
				throw new AnalyserException(sprintf("[%s] Invalid parameter type: %s", __CLASS__, $parameterType));
				// @codeCoverageIgnoreEnd
			}
			if ($refType instanceof EnumerationType) {
				if ($parameterType->isSubtypeOf(
					$programRegistry->typeRegistry->array(
						$refType,
						1
					)
				)) {
					return $programRegistry->typeRegistry->type($refType);
				}
				// @codeCoverageIgnoreStart
				throw new AnalyserException(sprintf("[%s] Invalid parameter type: %s", __CLASS__, $parameterType));
				// @codeCoverageIgnoreEnd
			}
			if ($refType instanceof MetaType && (
				$refType->value === MetaTypeValue::Enumeration /*||
				$refType->value === MetaTypeValue::EnumerationSubset*/
			)) {
				if ($parameterType->isSubtypeOf(
					$programRegistry->typeRegistry->array(
						$programRegistry->typeRegistry->any,
						1
					)
				)) {
					return $programRegistry->typeRegistry->result(
						$programRegistry->typeRegistry->type(
							$programRegistry->typeRegistry->metaType(MetaTypeValue::EnumerationSubset)
						),
						$programRegistry->typeRegistry->withName(
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
		ProgramRegistry $programRegistry,
		Value $target,
		Value $parameter
	): Value {
		$targetValue = $target;

		if ($targetValue instanceof TypeValue) {
			$typeValue = $this->toBaseType($targetValue->typeValue);
			if ($typeValue instanceof IntegerType) {
				if ($parameter->type->isSubtypeOf(
					$programRegistry->typeRegistry->array(
						$programRegistry->typeRegistry->integer(),
						1
					)
				)) {
					$values = $parameter->values;
					$result = $programRegistry->typeRegistry->integerSubset(
						array_map(fn(IntegerValue $value): Number => $value->literalValue, $values)
					);
					return ($programRegistry->valueRegistry->type($result));
				}
			}
			if ($typeValue instanceof RealType) {
				if ($parameter->type->isSubtypeOf(
					$programRegistry->typeRegistry->array(
						$programRegistry->typeRegistry->real(),
						1
					)
				)) {
					$values = $parameter->values;
					$result = $programRegistry->typeRegistry->realSubset(
						array_map(fn(RealValue|IntegerValue $value): Number => $value->literalValue, $values)
					);
					return ($programRegistry->valueRegistry->type($result));
				}
			}
			if ($typeValue instanceof StringType) {
				if ($parameter->type->isSubtypeOf(
					$programRegistry->typeRegistry->array(
						$programRegistry->typeRegistry->string(),
						1
					)
				)) {
					$values = $parameter->values;
					$result = $programRegistry->typeRegistry->stringSubset(
						array_map(fn(StringValue $value): string => $value->literalValue, $values)
					);
					return ($programRegistry->valueRegistry->type($result));
				}
			}
			if ($typeValue instanceof EnumerationType) {
				if ($parameter->type->isSubtypeOf(
					$programRegistry->typeRegistry->array(
						$programRegistry->typeRegistry->any,
						1
					)
				)) {
					$values = $parameter->values;
					$r = [];
					foreach($values as $value) {
						if ($value instanceof EnumerationValue && $value->enumeration == $typeValue) {
							$r[] = $value->name;
						} else {
							return ($programRegistry->valueRegistry->error(
								$programRegistry->valueRegistry->dataValue(
									new TypeNameIdentifier('UnknownEnumerationValue'),
									$programRegistry->valueRegistry->record([
										'enumeration' => $programRegistry->valueRegistry->type($typeValue),
										'value' => $value
									])
								)
							));
						}
					}
					$result = $typeValue->subsetType($r);
					return ($programRegistry->valueRegistry->type($result));
				}
			}
		}
		// @codeCoverageIgnoreStart
		throw new ExecutionException("Invalid parameter value");
		// @codeCoverageIgnoreEnd
	}

}