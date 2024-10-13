<?php

namespace Walnut\Lang\Implementation\Function;

use JsonSerializable;
use Walnut\Lang\Blueprint\Code\Analyser\AnalyserContext;
use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionContext;
use Walnut\Lang\Blueprint\Code\Expression\Expression;
use Walnut\Lang\Blueprint\Code\Scope\TypedValue;
use Walnut\Lang\Blueprint\Code\Scope\UnknownContextVariable;
use Walnut\Lang\Blueprint\Function\FunctionBody as FunctionBodyInterface;
use Walnut\Lang\Blueprint\Identifier\IdentifierException;
use Walnut\Lang\Blueprint\Identifier\VariableNameIdentifier;
use Walnut\Lang\Blueprint\Program\Registry\TypeRegistry;
use Walnut\Lang\Blueprint\Type\NothingType;
use Walnut\Lang\Blueprint\Type\RecordType;
use Walnut\Lang\Blueprint\Type\SealedType;
use Walnut\Lang\Blueprint\Type\SubtypeType;
use Walnut\Lang\Blueprint\Type\TupleType;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Value\RecordValue;
use Walnut\Lang\Blueprint\Value\SealedValue;
use Walnut\Lang\Blueprint\Value\TupleValue;
use Walnut\Lang\Blueprint\Value\Value;
use Walnut\Lang\Implementation\Type\Helper\BaseType;

final readonly class FunctionBody implements FunctionBodyInterface, JsonSerializable {

	use BaseType;

	public function __construct(
		private TypeRegistry $typeRegistry,
		private Expression $expression
	) {}

	public function expression(): Expression {
		return $this->expression;
	}

	public function analyse(
		AnalyserContext $analyserContext,
		Type $targetType,
		Type $parameterType,
		Type $dependencyType
	): Type {
		foreach(['$' => $targetType, '#' => $parameterType, '%' => $dependencyType] as $variableName => $type) {
			if (!($type instanceof NothingType)) {
				$analyserContext = $analyserContext->withAddedVariableType(
					new VariableNameIdentifier($variableName),
					$type
				);
				$type = $this->toBaseType($type);
				while($type instanceof SubtypeType) {
					$type = $type->baseType();
				}
				if ($type instanceof TupleType) {
					foreach($type->types() as $index => $typeItem) {
						$analyserContext = $analyserContext->withAddedVariableType(
							new VariableNameIdentifier($variableName . $index),
							$typeItem
						);
					}
				}
				if ($type instanceof RecordType) {
					foreach($type->types() as $fieldName => $fieldType) {
						$analyserContext = $analyserContext->withAddedVariableType(
							new VariableNameIdentifier($variableName . $fieldName),
							$fieldType
						);
					}
				}
			}
		}
		if ($targetType instanceof SealedType) {
			foreach($targetType->valueType()->types() as $fieldName => $fieldType) {
				$analyserContext = $analyserContext->withAddedVariableType(
					new VariableNameIdentifier('$' . $fieldName),
					$fieldType
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
		return $this->typeRegistry->union([$result->expressionType(), $result->returnType()]);
	}

	public function execute(
		ExecutionContext $executionContext,
		TypedValue|null $targetValue,
		TypedValue $parameterValue,
		TypedValue|null $dependencyValue
	): Value {
		foreach(['$' => $targetValue, '#' => $parameterValue, '%' => $dependencyValue] as $variableName => $value) {
			if ($value) {
				$executionContext = $executionContext->withAddedVariableValue(
					new VariableNameIdentifier($variableName), $value);
				$t = $this->toBaseType($value->type);
				$v = $this->toBaseValue($value->value);
				if ($t instanceof TupleType && $v instanceof TupleValue) {
					foreach($t->types() as $index => $typeItem) {
						try {
							$executionContext = $executionContext->withAddedVariableValue(
								new VariableNameIdentifier($variableName . $index),
								new TypedValue($typeItem, $v->valueOf($index)) // TODO: not found
							);
						} catch(IdentifierException) {}
					}
				}
				if ($t instanceof RecordType && $v instanceof RecordValue) {
					foreach($t->types() as $fieldName => $fieldType) {
						try {
							$executionContext = $executionContext->withAddedVariableValue(
								new VariableNameIdentifier($variableName . $fieldName),
								new TypedValue($fieldType, $v->valueOf($fieldName)) // TODO: not found
							);
						} catch(IdentifierException) {}
					}
				}
			}
		}
		if ($targetValue && $targetValue->type instanceof SealedType && $targetValue->value instanceof SealedValue) {
			foreach($targetValue->type->valueType()->types() as $fieldName => $fieldType) {
				$executionContext = $executionContext->withAddedVariableValue(
					new VariableNameIdentifier('$' . $fieldName),
					new TypedValue(
						$fieldType,
						$targetValue->value->value()->valueOf($fieldName) // TODO: not found
					)
				);
			}
		}
		return $this->expression->execute($executionContext)->value();
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