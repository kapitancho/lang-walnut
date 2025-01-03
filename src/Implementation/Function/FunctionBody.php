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
use Walnut\Lang\Blueprint\Program\Registry\ValueRegistry;
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
		private TypeRegistry $typeRegistry,
		private ValueRegistry $valueRegistry,
		public Expression $expression
	) {}

	private function getMapItemNotFound(): SealedType {
		return $this->typeRegistry->sealed(new TypeNameIdentifier("MapItemNotFound"));
	}

	public function analyse(
		AnalyserContext $analyserContext,
		Type $targetType,
		Type $parameterType,
		Type $dependencyType
	): Type {
		$tConv = fn(Type $type): Type => $type instanceof OptionalKeyType ?
			$this->typeRegistry->result($type->valueType, $this->getMapItemNotFound()) :
			$type;

		foreach(['$' => $targetType, '#' => $parameterType, '%' => $dependencyType] as $variableName => $type) {
			if (!($type instanceof NothingType)) {
				$analyserContext = $analyserContext->withAddedVariableType(
					new VariableNameIdentifier($variableName),
					$type
				);
				$type = $this->toBaseType($type);
				while($type instanceof SubtypeType) {
					$type = $type->baseType;
				}
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
		return $this->typeRegistry->union([$result->expressionType, $result->returnType]);
	}

	public function execute(
		ExecutionContext $executionContext,
		TypedValue|null $targetValue,
		TypedValue $parameterValue,
		TypedValue|null $dependencyValue
	): Value {
		$tConv = fn(Type $type): Type => $type instanceof OptionalKeyType ?
			$this->typeRegistry->result($type->valueType, $this->getMapItemNotFound()) :
			$type;

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
						} catch(IdentifierException|UnknownProperty) {}
					}
				}
				if ($t instanceof RecordType && $v instanceof RecordValue) {
					$values = $v->values;
					foreach($t->types as $fieldName => $fieldType) {
						try {
							$value = $values[$fieldName] ??
								$this->valueRegistry->error(
									$this->valueRegistry->sealedValue(
										new TypeNameIdentifier('MapItemNotFound'),
										$this->valueRegistry->record(['key' => $this->valueRegistry->string($fieldName)])
									)
								);
							$executionContext = $executionContext->withAddedVariableValue(
								new VariableNameIdentifier($variableName . $fieldName),
								new TypedValue($tConv($fieldType), $value)
							);
						} catch(IdentifierException) {}
					}
				}
			}
		}
		if ($targetValue && $targetValue->type instanceof SealedType && $targetValue->value instanceof SealedValue) {
			$values = $targetValue->value->value->values;
			foreach($targetValue->type->valueType->types as $fieldName => $fieldType) {
				$value = $values[$fieldName] ??
					$this->valueRegistry->error(
						$this->valueRegistry->sealedValue(
							new TypeNameIdentifier('MapItemNotFound'),
							$this->valueRegistry->record(['key' => $this->valueRegistry->string($fieldName)])
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