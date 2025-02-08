<?php

namespace Walnut\Lang\Implementation\Function;

use JsonSerializable;
use Walnut\Lang\Blueprint\Code\Analyser\AnalyserContext;
use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionContext;
use Walnut\Lang\Blueprint\Code\Expression\Expression;
use Walnut\Lang\Blueprint\Code\Scope\TypedValue;
use Walnut\Lang\Blueprint\Code\Scope\UnknownContextVariable;
use Walnut\Lang\Blueprint\Common\Identifier\IdentifierException;
use Walnut\Lang\Blueprint\Common\Identifier\TypeNameIdentifier;
use Walnut\Lang\Blueprint\Common\Identifier\VariableNameIdentifier;
use Walnut\Lang\Blueprint\Function\FunctionBody as FunctionBodyInterface;
use Walnut\Lang\Blueprint\Program\Registry\TypeRegistry;
use Walnut\Lang\Blueprint\Type\NothingType;
use Walnut\Lang\Blueprint\Type\RecordType;
use Walnut\Lang\Blueprint\Type\SealedType;
use Walnut\Lang\Blueprint\Type\SubtypeType;
use Walnut\Lang\Blueprint\Type\TupleType;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Type\UnknownProperty;
use Walnut\Lang\Blueprint\Value\RecordValue;
use Walnut\Lang\Blueprint\Value\SealedValue;
use Walnut\Lang\Blueprint\Value\TupleValue;
use Walnut\Lang\Blueprint\Value\Value;
use Walnut\Lang\Implementation\Type\Helper\BaseType;
use Walnut\Lang\Implementation\Type\OptionalKeyType;

final readonly class FunctionBody implements FunctionBodyInterface, JsonSerializable {

	use BaseType;

	public function __construct(
		public Expression $expression
	) {}

	private Type $returnType;

	private function getMapItemNotFound(TypeRegistry $typeRegistry): SealedType {
		return $typeRegistry->sealed(new TypeNameIdentifier("MapItemNotFound"));
	}

	public function analyse(
		AnalyserContext $analyserContext,
		Type $targetType,
		Type $parameterType,
		VariableNameIdentifier|null $parameterName,
		Type $dependencyType
	): Type {
		if (isset($this->returnType)) {
			return $this->returnType;
		}
		$tConv = fn(Type $type): Type => $type instanceof OptionalKeyType ?
			$analyserContext->programRegistry->typeRegistry->result($type->valueType, $this->getMapItemNotFound(
				$analyserContext->programRegistry->typeRegistry
			)) :
			$type;

		if ($targetType instanceof SealedType) {
			$analyserContext = $analyserContext->withAddedVariableType(
				new VariableNameIdentifier('$$'),
				$targetType->valueType
			);
		}

		if ($parameterName) {
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
				if ($type instanceof TupleType) {
					foreach($type->types as $index => $typeItem) {
						$analyserContext = $analyserContext->withAddedVariableType(
							new VariableNameIdentifier($variableName . $index),
							$typeItem
						);
					}
				}
				if ($type instanceof RecordType) {
					foreach($type->types as $fieldName => $fieldType) {
						$analyserContext = $analyserContext->withAddedVariableType(
							new VariableNameIdentifier($variableName . $fieldName),
							$tConv($fieldType)
						);
					}
				}
			}
		}
		if ($targetType instanceof SealedType) {
			foreach($targetType->valueType->types as $fieldName => $fieldType) {
				$analyserContext = $analyserContext->withAddedVariableType(
					new VariableNameIdentifier('$' . $fieldName),
					$tConv($fieldType)
				);
			}
		}
		try {
			$result = $this->expression->analyse($analyserContext);
		} catch (UnknownContextVariable $e) {
			throw new AnalyserException(
				sprintf(
					"Unknown variable '%s' in function body",
					$e->variableName
				)
			);
		}
		return $this->returnType ??= $analyserContext->programRegistry->typeRegistry->union([$result->expressionType, $result->returnType]);
	}

	public function execute(
		ExecutionContext $executionContext,
		TypedValue|null $targetValue,
		TypedValue $parameterValue,
		VariableNameIdentifier|null $parameterName,
		TypedValue|null $dependencyValue
	): Value {
		$tConv = fn(Type $type): Type => $type instanceof OptionalKeyType ?
			$executionContext->programRegistry->typeRegistry->result($type->valueType, $this->getMapItemNotFound(
				$executionContext->programRegistry->typeRegistry
			)) :
			$type;

		if ($targetValue && $targetValue->value instanceof SealedValue) {
			$executionContext = $executionContext->withAddedVariableValue(
				new VariableNameIdentifier('$$'),
				TypedValue::forValue($targetValue->value->value)
			);
		}
		if ($parameterName) {
			$executionContext = $executionContext->withAddedVariableValue(
				$parameterName,
				$parameterValue
			);
		}
		foreach(['$' => $targetValue, '#' => $parameterValue, '%' => $dependencyValue] as $variableName => $value) {
			if ($value) {
				$executionContext = $executionContext->withAddedVariableValue(
					new VariableNameIdentifier($variableName), $value);
				$t = $this->toBaseType($value->type);
				$v = $this->toBaseValue($value->value);
				if ($t instanceof TupleType && $v instanceof TupleValue) {
					foreach($t->types as $index => $typeItem) {
						try {
							$executionContext = $executionContext->withAddedVariableValue(
								new VariableNameIdentifier($variableName . $index),
								new TypedValue($typeItem, $v->valueOf($index)) // TODO: not found
							);
						// @codeCoverageIgnoreStart
						} catch(IdentifierException|UnknownProperty) {}
						// @codeCoverageIgnoreEnd
					}
				}
				if ($t instanceof RecordType && $v instanceof RecordValue) {
					$values = $v->values;
					foreach($t->types as $fieldName => $fieldType) {
						try {
							$value = $values[$fieldName] ??
								$executionContext->programRegistry->valueRegistry->error(
									$executionContext->programRegistry->valueRegistry->sealedValue(
										new TypeNameIdentifier('MapItemNotFound'),
										$executionContext->programRegistry->valueRegistry->record([
											'key' => $executionContext->programRegistry->valueRegistry->string($fieldName)
										])
									)
								);
							$executionContext = $executionContext->withAddedVariableValue(
								new VariableNameIdentifier($variableName . $fieldName),
								new TypedValue($tConv($fieldType), $value)
							);
						// @codeCoverageIgnoreStart
						} catch(IdentifierException) {}
						// @codeCoverageIgnoreEnd
					}
				}
			}
		}
		if ($targetValue && $targetValue->type instanceof SealedType && $targetValue->value instanceof SealedValue) {
			$values = $targetValue->value->value->values;
			foreach($targetValue->type->valueType->types as $fieldName => $fieldType) {
				$value = $values[$fieldName] ??
					$executionContext->programRegistry->valueRegistry->error(
						$executionContext->programRegistry->valueRegistry->sealedValue(
							new TypeNameIdentifier('MapItemNotFound'),
							$executionContext->programRegistry->valueRegistry->record([
								'key' => $executionContext->programRegistry->valueRegistry->string($fieldName)
							])
						)
					)
				;
				$executionContext = $executionContext->withAddedVariableValue(
					new VariableNameIdentifier('$' . $fieldName),
					new TypedValue(
						$tConv($fieldType),
						$value
					)
				);
			}
		}
		return $this->expression->execute($executionContext)->value;
	}

	public function __toString(): string {
		return (string)$this->expression;
	}

	public function jsonSerialize(): array {
		return [
			'expression' => $this->expression
		];
	}
}