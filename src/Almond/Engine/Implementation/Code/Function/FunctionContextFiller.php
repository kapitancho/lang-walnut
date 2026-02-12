<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Code\Function;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Function\FunctionContextFiller as FunctionContextFillerInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Function\NameAndType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\DataType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\NothingType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\OpenType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\OptionalKeyType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\RecordType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\ResultType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\SealedType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\TupleType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\TypeRegistry;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\DataValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\ErrorValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\OpenValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\RecordValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\SealedValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\TupleValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\ValueRegistry;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Identifier\IdentifierException;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Identifier\VariableName;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Execution\ExecutionContext;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationContext;
use Walnut\Lang\Almond\Engine\Implementation\Code\Type\Helper\BaseType;

final readonly class FunctionContextFiller implements FunctionContextFillerInterface {
	use BaseType;

	public function __construct(
		private TypeRegistry $typeRegistry,
		private ValueRegistry $valueRegistry,
	) {}

	public function fillValidationContext(
		ValidationContext $validationContext,
		NameAndType $target, NameAndType $parameter, NameAndType $dependency,
	): ValidationContext {
		$tConv = fn(Type $type): Type => $type instanceof OptionalKeyType ?
			$this->typeRegistry->result($type->valueType, $this->typeRegistry->core->mapItemNotFound) :
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
				$type = $this->toBaseType($type);
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

		if (
			$targetType instanceof SealedType &&
			($targetType->valueType instanceof RecordType || $targetType->valueType instanceof TupleType)
		) {
			foreach($targetType->valueType->types as $fieldName => $fieldType) {
				$validationContext = $validationContext->withAddedVariableType(
					new VariableName('$' . $fieldName),
					$tConv($fieldType)
				);
			}
			if (!$targetType->valueType->restType instanceof NothingType) {
				$validationContext = $validationContext->withAddedVariableType(
					new VariableName('$_'),
					$targetType->valueType instanceof RecordType ?
						$this->typeRegistry->map(
							$targetType->valueType->restType
						) : $this->typeRegistry->array(
							$targetType->valueType->restType
						)
				);
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

		$t = $this->toBaseType($target->type);
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
							$val = $v->valueOf($index);
							if ($val instanceof Value) {
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
		if (
			$target->type instanceof SealedType && $targetValue instanceof SealedValue &&
			(($tv = $targetValue->value) instanceof TupleValue || $tv instanceof RecordValue)
		) {
			$restValues = $tv->values;
			$values = $tv->values;
			foreach($target->type->valueType->types as $fieldName => $fieldType) {
				unset($restValues[$fieldName]);
				$value = $values[$fieldName] ??
					$this->valueRegistry->error(
						$this->valueRegistry->core->mapItemNotFound(
							$this->valueRegistry->record([
								'key' => $this->valueRegistry->string($fieldName)
							])
						)
					)
				;
				$executionContext = $executionContext->withAddedVariableValue(
					new VariableName('$' . $fieldName),
					$value
				);
			}
			if (!$target->type->valueType->restType instanceof NothingType) {
				$executionContext = $executionContext->withAddedVariableValue(
					new VariableName('$_'),
					$tv instanceof RecordValue ?
						$this->valueRegistry->record($restValues) :
						$this->valueRegistry->tuple(array_values($restValues))
				);
			}
		}
		return $executionContext;
	}

}