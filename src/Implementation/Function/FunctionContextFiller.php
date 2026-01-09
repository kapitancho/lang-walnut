<?php

namespace Walnut\Lang\Implementation\Function;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserContext;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionContext;
use Walnut\Lang\Blueprint\Common\Identifier\IdentifierException;
use Walnut\Lang\Blueprint\Common\Identifier\TypeNameIdentifier;
use Walnut\Lang\Blueprint\Common\Identifier\VariableNameIdentifier;
use Walnut\Lang\Blueprint\Function\FunctionContextFiller as FunctionContextFillerInterface;
use Walnut\Lang\Blueprint\Program\Registry\TypeRegistry;
use Walnut\Lang\Blueprint\Type\CompositeNamedType;
use Walnut\Lang\Blueprint\Type\CustomType;
use Walnut\Lang\Blueprint\Type\DataType;
use Walnut\Lang\Blueprint\Type\NameAndType;
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
use Walnut\Lang\Blueprint\Value\DataValue;
use Walnut\Lang\Blueprint\Value\ErrorValue;
use Walnut\Lang\Blueprint\Value\OpenValue;
use Walnut\Lang\Blueprint\Value\RecordValue;
use Walnut\Lang\Blueprint\Value\SealedValue;
use Walnut\Lang\Blueprint\Value\Value;
use Walnut\Lang\Blueprint\Value\TupleValue;
use Walnut\Lang\Implementation\Type\Helper\BaseType;
use Walnut\Lang\Implementation\Type\Helper\TupleAsRecord;

final readonly class FunctionContextFiller implements FunctionContextFillerInterface {

	use BaseType;
	use TupleAsRecord;

	private function getMapItemNotFound(TypeRegistry $typeRegistry): DataType {
		return $typeRegistry->data(new TypeNameIdentifier("MapItemNotFound"));
	}

	public function fillAnalyserContext(
		AnalyserContext $analyserContext,
		Type $targetType,
		NameAndType $parameter,
		NameAndType $dependency,
	): AnalyserContext {
		$parameterType = $parameter->type;
		$dependencyType = $dependency->type;

		$tConv = fn(Type $type): Type => $type instanceof OptionalKeyType ?
			$analyserContext->typeRegistry->result($type->valueType, $this->getMapItemNotFound(
				$analyserContext->typeRegistry
			)) :
			$type;

		if ($targetType instanceof CompositeNamedType) {
			$analyserContext = $analyserContext->withAddedVariableType(
				new VariableNameIdentifier('$$'),
				$targetType->valueType
			);
		}
		if (!($parameterType instanceof NothingType) && ($parameterName = $parameter->name)) {
			$analyserContext = $analyserContext->withAddedVariableType(
				$parameterName,
				$parameterType
			);
		}
		if (!($dependencyType instanceof NothingType) && ($dependencyName = $dependency->name)) {
			$analyserContext = $analyserContext->withAddedVariableType(
				$dependencyName,
				$dependencyType
			);
		}
		foreach(['$' => $targetType, '#' => $parameterType, '%' => $dependencyType] as $variableName => $type) {
			if (!($type instanceof NothingType)) {
				$analyserContext = $analyserContext->withAddedVariableType(
					new VariableNameIdentifier($variableName),
					$type
				);
				$type = $this->toBaseType($type);
				$t = $type instanceof OpenType || $type instanceof DataType ? $type->valueType : $type;
				if ($t instanceof TupleType) {
					foreach($t->types as $index => $typeItem) {
						$analyserContext = $analyserContext->withAddedVariableType(
							new VariableNameIdentifier($variableName . $index),
							$typeItem
						);
					}
					if (!$t->restType instanceof NothingType) {
						$analyserContext = $analyserContext->withAddedVariableType(
							new VariableNameIdentifier($variableName . '_'),
							$analyserContext->typeRegistry->array($t->restType)
						);
					}
				}
				if ($t instanceof RecordType) {
					foreach($t->types as $fieldName => $fieldType) if ($fieldName !== '') {
						try {
							$analyserContext = $analyserContext->withAddedVariableType(
								new VariableNameIdentifier($variableName . $fieldName),
								$tConv($fieldType)
							);
						} catch (IdentifierException) {}
					}
					if (!$t->restType instanceof NothingType) {
						$analyserContext = $analyserContext->withAddedVariableType(
							new VariableNameIdentifier($variableName . '_'),
							$analyserContext->typeRegistry->map($t->restType)
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
			if (!$targetType->valueType->restType instanceof NothingType) {
				$analyserContext = $analyserContext->withAddedVariableType(
					new VariableNameIdentifier('$_'),
					$targetType->valueType instanceof RecordType ?
						$analyserContext->typeRegistry->map(
							$targetType->valueType->restType
						) : $analyserContext->typeRegistry->array(
							$targetType->valueType->restType
						)
				);
			}
		}
		return $analyserContext;
	}

	public function fillExecutionContext(
		ExecutionContext $executionContext,
		Type $targetType,
		Value|null $targetValue,
		NameAndType $parameter,
		Value|null $parameterValue,
		NameAndType $dependency,
		Value|null $dependencyValue,
	): ExecutionContext {
		$t = $this->toBaseType($targetType);
		if (
			($t instanceof CustomType && $targetValue instanceof CustomValue) ||
			($t instanceof DataType && $targetValue instanceof DataValue)
		) {
			$executionContext = $executionContext->withAddedVariableValue(
				new VariableNameIdentifier('$$'),
				$targetValue->value
			);
		}
		if ($parameterValue && ($parameterName = $parameter->name)) {
			$executionContext = $executionContext->withAddedVariableValue(
				$parameterName,
				$parameterValue
			);
		}
		if ($dependencyValue && ($dependencyName = $dependency->name)) {
			$executionContext = $executionContext->withAddedVariableValue(
				$dependencyName,
				$dependencyValue
			);
		}
		foreach([
			'$' => [$targetType, $targetValue],
	        '#' => [$parameter->type, $parameterValue],
	        '%' => [$dependency->type, $dependencyValue]
        ] as $variableName => [$xType, $value]) {
			if ($value) {
				$executionContext = $executionContext->withAddedVariableValue(
					new VariableNameIdentifier($variableName), $value);
				$type = $this->toBaseType($xType);
				[$t, $v] =
					($type instanceof OpenType && $value instanceof OpenValue) ||
					($type instanceof DataType && $value instanceof DataValue) ?
					[$type->valueType, $value->value] :
					[$type, $value];
				if ($t instanceof ResultType && !($v instanceof ErrorValue)) {
					$t = $t->returnType;
				}
				if ($t instanceof TupleType && $v instanceof TupleValue) {
					foreach($t->types as $index => $typeItem) {
						try {
							$executionContext = $executionContext->withAddedVariableValue(
								new VariableNameIdentifier($variableName . $index),
								($v->valueOf($index))
							);
							// @codeCoverageIgnoreStart
						} catch(IdentifierException|UnknownProperty) {}
						// @codeCoverageIgnoreEnd
					}
					if (!$t->restType instanceof NothingType) {
						$executionContext = $executionContext->withAddedVariableValue(
							new VariableNameIdentifier($variableName . '_'),
							$executionContext->programRegistry->valueRegistry->tuple(array_slice($v->values, count($t->types)))
						);
					}
				} elseif ($t instanceof RecordType && $v instanceof RecordValue) {
					$recordValues = $v->values;
					$values = $v->values;
					foreach($t->types as $fieldName => $fieldType) {
						unset($recordValues[$fieldName]);
						try {
							$rValue = $values[$fieldName] ??
								$executionContext->programRegistry->valueRegistry->error(
									$executionContext->programRegistry->valueRegistry->dataValue(
										new TypeNameIdentifier('MapItemNotFound'),
										$executionContext->programRegistry->valueRegistry->record([
											'key' => $executionContext->programRegistry->valueRegistry->string($fieldName)
										])
									)
								);
							$executionContext = $executionContext->withAddedVariableValue(
								new VariableNameIdentifier($variableName . $fieldName),
								($rValue)
							);
							// @codeCoverageIgnoreStart
						} catch(IdentifierException) {}
						// @codeCoverageIgnoreEnd
					}
					if (!$t->restType instanceof NothingType) {
						$executionContext = $executionContext->withAddedVariableValue(
							new VariableNameIdentifier($variableName . '_'),
							$executionContext->programRegistry->valueRegistry->record($recordValues)
						);
					}
				}
			}
		}
		if (
			$targetType instanceof SealedType && $targetValue instanceof SealedValue &&
			(($tv = $targetValue->value) instanceof TupleValue || $tv instanceof RecordValue)
		) {
			$restValues = $tv->values;
			$values = $tv->values;
			foreach($targetType->valueType->types /*?? $tv->type->types*/ as $fieldName => $fieldType) {
				unset($restValues[$fieldName]);
				$value = $values[$fieldName] ??
					$executionContext->programRegistry->valueRegistry->error(
						$executionContext->programRegistry->valueRegistry->dataValue(
							new TypeNameIdentifier('MapItemNotFound'),
							$executionContext->programRegistry->valueRegistry->record([
								'key' => $executionContext->programRegistry->valueRegistry->string($fieldName)
							])
						)
					)
				;
				$executionContext = $executionContext->withAddedVariableValue(
					new VariableNameIdentifier('$' . $fieldName),
					$value
				);
			}
			if (!$targetType->valueType->restType instanceof NothingType) {
				$executionContext = $executionContext->withAddedVariableValue(
					new VariableNameIdentifier('$_'),
					$tv instanceof RecordValue ?
						/** @phpstan-ignore-next-line argument.type */
						$executionContext->programRegistry->valueRegistry->record($restValues) :
						$executionContext->programRegistry->valueRegistry->tuple(array_values($restValues))
				);
			}
		}
		return $executionContext;
	}

}