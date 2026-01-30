<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Function;

use Walnut\Lang\Almond\Engine\Blueprint\Abc\Function\NameAndType;
use Walnut\Lang\Almond\Engine\Blueprint\Execution\ExecutionContext;
use Walnut\Lang\Almond\Engine\Blueprint\Function\FunctionContextFiller as FunctionContextFillerInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Identifier\IdentifierException;
use Walnut\Lang\Almond\Engine\Blueprint\Identifier\VariableName;
use Walnut\Lang\Almond\Engine\Blueprint\Registry\TypeRegistry;
use Walnut\Lang\Almond\Engine\Blueprint\Registry\ValueRegistry;
use Walnut\Lang\Almond\Engine\Blueprint\Type\DataType;
use Walnut\Lang\Almond\Engine\Blueprint\Type\NothingType;
use Walnut\Lang\Almond\Engine\Blueprint\Type\OpenType;
use Walnut\Lang\Almond\Engine\Blueprint\Type\OptionalKeyType;
use Walnut\Lang\Almond\Engine\Blueprint\Type\RecordType;
use Walnut\Lang\Almond\Engine\Blueprint\Type\ResultType;
use Walnut\Lang\Almond\Engine\Blueprint\Type\SealedType;
use Walnut\Lang\Almond\Engine\Blueprint\Type\TupleType;
use Walnut\Lang\Almond\Engine\Blueprint\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Validation\ValidationContext;
use Walnut\Lang\Almond\Engine\Blueprint\Value\DataValue;
use Walnut\Lang\Almond\Engine\Blueprint\Value\ErrorValue;
use Walnut\Lang\Almond\Engine\Blueprint\Value\OpenValue;
use Walnut\Lang\Almond\Engine\Blueprint\Value\RecordValue;
use Walnut\Lang\Almond\Engine\Blueprint\Value\SealedValue;
use Walnut\Lang\Almond\Engine\Blueprint\Value\TupleValue;
use Walnut\Lang\Almond\Engine\Blueprint\Value\Value;

final readonly class FunctionContextFiller implements FunctionContextFillerInterface {

	public function __construct(
		private TypeRegistry $typeRegistry,
		private ValueRegistry $valueRegistry,
	) {}

	private function getMapItemNotFound(TypeRegistry $typeRegistry): DataType {
		return $this->typeRegistry->core->mapItemNotFound;
	}

	public function fillValidationContext(
		ValidationContext $validationContext,
		NameAndType $target, NameAndType $parameter, NameAndType $dependency,
	): ValidationContext {
		$tConv = fn(Type $type): Type => $type instanceof OptionalKeyType ?
			$this->typeRegistry->result($type->valueType, $this->getMapItemNotFound(
				$this->typeRegistry
			)) :
			$type;

		$targetType = $target->type;
		$parameterType = $parameter->type;
		$dependencyType = $dependency->type;

		if ($targetType instanceof OpenType || $targetType instanceof SealedType || $targetType instanceof DataType) {
			$validationContext = $validationContext->withAddedVariableType(
				new VariableName('$$'),
				$targetType->valueType
			);
		}

		foreach([$target, $parameter, $dependency] as $nameAndType) {
			if (!($nameAndType->type instanceof NothingType) && ($varName = $nameAndType->name)) {
				$validationContext = $validationContext->withAddedVariableType(
					$varName,
					$nameAndType->type
				);
			}
		}

		foreach(['$' => $targetType, '#' => $parameterType, '%' => $dependencyType] as $variableName => $type) {
			if (!($type instanceof NothingType)) {
				$validationContext = $validationContext->withAddedVariableType(
					new VariableName($variableName),
					$type
				);
				//$type = $this->toBaseType($type);
				$t = $type instanceof OpenType || $type instanceof DataType ? $type->valueType : $type;
				if ($t instanceof TupleType) {
					foreach($t->types as $index => $typeItem) {
						$validationContext = $validationContext->withAddedVariableType(
							new VariableName($variableName . $index),
							$typeItem
						);
					}
					if (!$t->restType instanceof NothingType) {
						$validationContext = $validationContext->withAddedVariableType(
							new VariableName($variableName . '_'),
							$this->typeRegistry->array($t->restType)
						);
					}
				}
				if ($t instanceof RecordType) {
					foreach($t->types as $fieldName => $fieldType) if ($fieldName !== '') {
						try {
							$validationContext = $validationContext->withAddedVariableType(
								new VariableName($variableName . $fieldName),
								$tConv($fieldType)
							);
						} catch (IdentifierException) {}
					}
					if (!$t->restType instanceof NothingType) {
						$validationContext = $validationContext->withAddedVariableType(
							new VariableName($variableName . '_'),
							$this->typeRegistry->map($t->restType)
						);
					}
				}
			}
		}

		return $validationContext;
	}

	public function fillExecutionContext(
		ExecutionContext $executionContext,
		NameAndType $target, Value|null $targetValue,
		NameAndType $parameter, Value|null $parameterValue,
		NameAndType $dependency, Value|null $dependencyValue,
	): ExecutionContext {

		$t = /*$this->toBaseType(*/$target->type/*)*/;
		if (
			($t instanceof OpenType && $targetValue instanceof OpenValue) ||
			($t instanceof SealedType && $targetValue instanceof SealedValue) ||
			($t instanceof DataType && $targetValue instanceof DataValue)
		) {
			$executionContext = $executionContext->withAddedVariableValue(
				new VariableName('$$'),
				$targetValue->value
			);
		}

		foreach([
			[$target, $targetValue],
	        [$parameter, $parameterValue],
	        [$dependency, $dependencyValue]
        ] as [$nameAndType, $value]) {
			if ($value && ($varName = $nameAndType->name)) {
				$executionContext = $executionContext->withAddedVariableValue(
					$varName,
					$value
				);
			}
		}
		foreach([
	        '$' => [$target->type, $targetValue],
	        '#' => [$parameter->type, $parameterValue],
	        '%' => [$dependency->type, $dependencyValue]
        ] as $variableName => [$xType, $value]) {
			if ($value) {
				$executionContext = $executionContext->withAddedVariableValue(
					new VariableName($variableName), $value);
				$type = /*$this->toBaseType(*/$xType/*)*/;
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
							$v = $v->valueOf($index);
							if ($v instanceof Value) {
								$executionContext = $executionContext->withAddedVariableValue(
									new VariableName($variableName . $index),
									$v->valueOf($index)
								);
							}
							// @codeCoverageIgnoreStart
						} catch(IdentifierException) {}
						// @codeCoverageIgnoreEnd
					}
					if (!$t->restType instanceof NothingType) {
						$executionContext = $executionContext->withAddedVariableValue(
							new VariableName($variableName . '_'),
							$this->valueRegistry->tuple(array_slice($v->values, count($t->types)))
						);
					}
				} elseif ($t instanceof RecordType && $v instanceof RecordValue) {
					$recordValues = $v->values;
					$values = $v->values;
					foreach($t->types as $fieldName => $fieldType) {
						unset($recordValues[$fieldName]);
						try {
							$rValue = $values[$fieldName] ??
								$this->valueRegistry->error(
									$this->valueRegistry->core->mapItemNotFound(
										$this->valueRegistry->record([
											'key' => $this->valueRegistry->string($fieldName)
										])
									)
								);
							$executionContext = $executionContext->withAddedVariableValue(
								new VariableName($variableName . $fieldName),
								($rValue)
							);
							// @codeCoverageIgnoreStart
						} catch(IdentifierException) {}
						// @codeCoverageIgnoreEnd
					}
					if (!$t->restType instanceof NothingType) {
						$executionContext = $executionContext->withAddedVariableValue(
							new VariableName($variableName . '_'),
							$this->valueRegistry->record($recordValues)
						);
					}
				}
			}
		}
		return $executionContext;
	}

}