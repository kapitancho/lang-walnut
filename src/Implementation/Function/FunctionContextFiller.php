<?php

namespace Walnut\Lang\Implementation\Function;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserContext;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionContext;
use Walnut\Lang\Blueprint\Code\Scope\TypedValue;
use Walnut\Lang\Blueprint\Common\Identifier\IdentifierException;
use Walnut\Lang\Blueprint\Common\Identifier\TypeNameIdentifier;
use Walnut\Lang\Blueprint\Common\Identifier\VariableNameIdentifier;
use Walnut\Lang\Blueprint\Function\FunctionContextFiller as FunctionContextFillerInterface;
use Walnut\Lang\Blueprint\Program\Registry\TypeRegistry;
use Walnut\Lang\Blueprint\Type\CustomType;
use Walnut\Lang\Blueprint\Type\NothingType;
use Walnut\Lang\Blueprint\Type\OpenType;
use Walnut\Lang\Blueprint\Type\OptionalKeyType;
use Walnut\Lang\Blueprint\Type\RecordType;
use Walnut\Lang\Blueprint\Type\ResultType;
use Walnut\Lang\Blueprint\Type\SealedType;
use Walnut\Lang\Blueprint\Type\TupleType;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Type\UnknownProperty;
use Walnut\Lang\Blueprint\Value\CustomValue;
use Walnut\Lang\Blueprint\Value\ErrorValue;
use Walnut\Lang\Blueprint\Value\OpenValue;
use Walnut\Lang\Blueprint\Value\RecordValue;
use Walnut\Lang\Blueprint\Value\SealedValue;
use Walnut\Lang\Blueprint\Value\TupleValue;
use Walnut\Lang\Implementation\Type\Helper\BaseType;
use Walnut\Lang\Implementation\Type\Helper\TupleAsRecord;

final readonly class FunctionContextFiller implements FunctionContextFillerInterface {

	use BaseType;
	use TupleAsRecord;

	private function getMapItemNotFound(TypeRegistry $typeRegistry): OpenType {
		return $typeRegistry->open(new TypeNameIdentifier("MapItemNotFound"));
	}

	public function fillAnalyserContext(
		AnalyserContext $analyserContext,
		Type $targetType,
		Type $parameterType,
		VariableNameIdentifier|null $parameterName,
		Type $dependencyType
	): AnalyserContext {
		$tConv = fn(Type $type): Type => $type instanceof OptionalKeyType ?
			$analyserContext->programRegistry->typeRegistry->result($type->valueType, $this->getMapItemNotFound(
				$analyserContext->programRegistry->typeRegistry
			)) :
			$type;

		if ($targetType instanceof CustomType) {
			$analyserContext = $analyserContext->withAddedVariableType(
				new VariableNameIdentifier('$$'),
				$targetType->valueType
			);
		}
		if ($parameterName && !($parameterType instanceof NothingType)) {
			$analyserContext = $analyserContext->withAddedVariableType(
				$parameterName,
				$parameterType
			);
		}
		foreach(['$' => $targetType, '#' => $parameterType, '%' => $dependencyType] as $variableName => $type) {
			if (!($type instanceof NothingType)) {
				$analyserContext = $analyserContext->withAddedVariableType(
					new VariableNameIdentifier($variableName),
					$type
				);
				$type = $this->toBaseType($type);
				$t = $type instanceof OpenType ? $type->valueType : $type;
				if ($t instanceof TupleType) {
					foreach($t->types as $index => $typeItem) {
						$analyserContext = $analyserContext->withAddedVariableType(
							new VariableNameIdentifier($variableName . $index),
							$typeItem
						);
					}
				}
				if ($t instanceof RecordType) {
					foreach($t->types as $fieldName => $fieldType) {
						$analyserContext = $analyserContext->withAddedVariableType(
							new VariableNameIdentifier($variableName . $fieldName),
							$tConv($fieldType)
						);
					}
				}
			}
		}
		if (
			$targetType instanceof SealedType &&
			($targetType->valueType instanceof RecordType || $targetType->valueType instanceof TupleType)
		) {
			foreach($targetType->valueType->types as $fieldName => $fieldType) {
				$analyserContext = $analyserContext->withAddedVariableType(
					new VariableNameIdentifier('$' . $fieldName),
					$tConv($fieldType)
				);
			}
		}
		return $analyserContext;
	}

	public function fillExecutionContext(
		ExecutionContext $executionContext,
		TypedValue|null $targetValue,
		TypedValue|null $parameterValue,
		VariableNameIdentifier|null $parameterName,
		TypedValue|null $dependencyValue
	): ExecutionContext {
		$tConv = fn(Type $type): Type => $type instanceof OptionalKeyType ?
			$executionContext->programRegistry->typeRegistry->result($type->valueType, $this->getMapItemNotFound(
				$executionContext->programRegistry->typeRegistry
			)) :
			$type;

		if ($targetValue && $targetValue->value instanceof CustomValue) {
			$executionContext = $executionContext->withAddedVariableValue(
				new VariableNameIdentifier('$$'),
				TypedValue::forValue($targetValue->value->value)
			);
		}
		if ($parameterValue && $parameterName) {
			$executionContext = $executionContext->withAddedVariableValue(
				$parameterName,
				$parameterValue
			);
		}
		foreach(['$' => $targetValue, '#' => $parameterValue, '%' => $dependencyValue] as $variableName => $value) {
			if ($value) {
				$executionContext = $executionContext->withAddedVariableValue(
					new VariableNameIdentifier($variableName), $value);
				foreach($value->types as $vType) {
					$type = $this->toBaseType($vType);
					[$t, $v] = $type instanceof OpenType && $value->value instanceof OpenValue ?
						[$type->valueType, $value->value->value] :
						[$type, $value->value];
					if ($t instanceof ResultType && !($v instanceof ErrorValue)) {
						$t = $t->returnType;
					}
					if ($t instanceof TupleType && $v instanceof TupleValue) {
						foreach($t->types as $index => $typeItem) {
							try {
								$executionContext = $executionContext->withAddedVariableValue(
									new VariableNameIdentifier($variableName . $index),
									TypedValue::forValue($v->valueOf($index))->withType($typeItem)
								);
								// @codeCoverageIgnoreStart
							} catch(IdentifierException|UnknownProperty) {}
							// @codeCoverageIgnoreEnd
						}
						break;
					}
					if ($t instanceof RecordType && $v instanceof RecordValue) {
						$values = $v->values;
						foreach($t->types as $fieldName => $fieldType) {
							try {
								$rValue = $values[$fieldName] ??
									$executionContext->programRegistry->valueRegistry->error(
										$executionContext->programRegistry->valueRegistry->openValue(
											new TypeNameIdentifier('MapItemNotFound'),
											$executionContext->programRegistry->valueRegistry->record([
												'key' => $executionContext->programRegistry->valueRegistry->string($fieldName)
											])
										)
									);
								$executionContext = $executionContext->withAddedVariableValue(
									new VariableNameIdentifier($variableName . $fieldName),
									TypedValue::forValue($rValue)->withType($tConv($fieldType))
								);
								// @codeCoverageIgnoreStart
							} catch(IdentifierException) {}
							// @codeCoverageIgnoreEnd
						}
						break;
					}
				}
			}
		}
		if (
			$targetValue &&
			$targetValue->type instanceof SealedType &&
			//(($vt = $targetValue->type->valueType) instanceof TupleType || $vt instanceof RecordType) &&
			$targetValue->value instanceof SealedValue &&
			(($tv = $targetValue->value->value) instanceof TupleValue || $tv instanceof RecordValue)
		) {
			$values = $tv->values;
			foreach($targetValue->type->valueType->types /*?? $tv->type->types*/ as $fieldName => $fieldType) {
				$value = $values[$fieldName] ??
					$executionContext->programRegistry->valueRegistry->error(
						$executionContext->programRegistry->valueRegistry->openValue(
							new TypeNameIdentifier('MapItemNotFound'),
							$executionContext->programRegistry->valueRegistry->record([
								'key' => $executionContext->programRegistry->valueRegistry->string($fieldName)
							])
						)
					)
				;
				$executionContext = $executionContext->withAddedVariableValue(
					new VariableNameIdentifier('$' . $fieldName),
					TypedValue::forValue($value)->withType($tConv($fieldType))
				);
			}
		}
		return $executionContext;
	}

}