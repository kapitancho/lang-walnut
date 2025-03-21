<?php

namespace Walnut\Lang\Implementation\Function;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserContext;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionContext;
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
use Walnut\Lang\Blueprint\Value\Value;
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
					if (!$t->restType instanceof NothingType) {
						$analyserContext = $analyserContext->withAddedVariableType(
							new VariableNameIdentifier($variableName . '_'),
							$analyserContext->programRegistry->typeRegistry->array($t->restType)
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
					if (!$t->restType instanceof NothingType) {
						$analyserContext = $analyserContext->withAddedVariableType(
							new VariableNameIdentifier($variableName . '_'),
							$analyserContext->programRegistry->typeRegistry->map($t->restType)
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
						$analyserContext->programRegistry->typeRegistry->map(
							$targetType->valueType->restType
						) : $analyserContext->programRegistry->typeRegistry->array(
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
		Type $parameterType,
		Value|null $parameterValue,
		VariableNameIdentifier|null $parameterName,
		Type $dependencyType,
		Value|null $dependencyValue
	): ExecutionContext {
		$t = $this->toBaseType($targetType);
		if ($t instanceof CustomType && $targetValue instanceof CustomValue) {
			$executionContext = $executionContext->withAddedVariableValue(
				new VariableNameIdentifier('$$'),
				$targetValue->value
			);
		}
		if ($parameterValue && $parameterName) {
			$executionContext = $executionContext->withAddedVariableValue(
				$parameterName,
				$parameterValue
			);
		}
		foreach([
			'$' => [$targetType, $targetValue],
	        '#' => [$parameterType, $parameterValue],
	        '%' => [$dependencyType, $dependencyValue]
        ] as $variableName => [$xType, $value]) {
			if ($value) {
				$executionContext = $executionContext->withAddedVariableValue(
					new VariableNameIdentifier($variableName), $value);
				$type = $this->toBaseType($xType);
				[$t, $v] = $type instanceof OpenType && $value instanceof OpenValue ?
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
									$executionContext->programRegistry->valueRegistry->openValue(
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
					($value)
				);
			}
			if (!$targetType->valueType->restType instanceof NothingType) {
				$executionContext = $executionContext->withAddedVariableValue(
					new VariableNameIdentifier('$_'),
					$tv instanceof RecordValue ?
						$executionContext->programRegistry->valueRegistry->record($restValues) :
						$executionContext->programRegistry->valueRegistry->tuple(array_values($restValues))
				);
			}
		}
		return $executionContext;
	}

}