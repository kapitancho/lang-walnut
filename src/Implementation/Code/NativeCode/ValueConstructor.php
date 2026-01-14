<?php

namespace Walnut\Lang\Implementation\Code\NativeCode;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionException;
use Walnut\Lang\Blueprint\Common\Identifier\EnumValueIdentifier;
use Walnut\Lang\Blueprint\Common\Identifier\MethodNameIdentifier;
use Walnut\Lang\Blueprint\Function\UnknownMethod;
use Walnut\Lang\Blueprint\Program\Registry\MethodAnalyser;
use Walnut\Lang\Blueprint\Program\Registry\MethodContext;
use Walnut\Lang\Blueprint\Program\Registry\TypeRegistry;
use Walnut\Lang\Blueprint\Program\Registry\ValueRegistry;
use Walnut\Lang\Blueprint\Type\AtomType;
use Walnut\Lang\Blueprint\Type\EnumerationType;
use Walnut\Lang\Blueprint\Type\NamedType;
use Walnut\Lang\Blueprint\Type\NothingType;
use Walnut\Lang\Blueprint\Type\OpenType;
use Walnut\Lang\Blueprint\Type\ResultType;
use Walnut\Lang\Blueprint\Type\SealedType;
use Walnut\Lang\Blueprint\Type\StringSubsetType;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Type\UnknownEnumerationValue;
use Walnut\Lang\Blueprint\Value\EnumerationValue;
use Walnut\Lang\Blueprint\Value\ErrorValue;
use Walnut\Lang\Blueprint\Value\StringValue;
use Walnut\Lang\Blueprint\Value\Value;
use Walnut\Lang\Implementation\Type\Helper\TupleAsRecord;

final readonly class ValueConstructor {
	use TupleAsRecord;

	public function __construct() {}

	private function getConstructorType(TypeRegistry $typeRegistry): AtomType {
		return $typeRegistry->constructor;
	}

	private function getConstructingType(TypeRegistry $typeRegistry, Type $type, Type $parameterType): Type {
		return match(true) {
			$type instanceof SealedType, $type instanceof OpenType => $type->valueType,
			//$type instanceof AtomType => $typeRegistry->null,
			//$type instanceof AliasType => $type->aliasedType,
			$type instanceof ResultType && $type->returnType instanceof NothingType => $parameterType,
			$type instanceof EnumerationType => $typeRegistry->union([
				$typeRegistry->string(),
				$type
			]),
			default => throw new AnalyserException(
				sprintf("Cannot construct a value of type: %s", $type)
			)
		};
	}

	/** @throws AnalyserException */
	public function analyseConstructor(
		TypeRegistry $typeRegistry,
		MethodAnalyser $methodAnalyser,
		Type $resultType,
		Type $parameterType
	): Type {
		$constructorType = $this->getConstructorType($typeRegistry);

		$errorType = null;
		$validatorInputType = $parameterType;
		if ($resultType instanceof NamedType) {
			$constructorResult = $methodAnalyser->safeAnalyseMethod(
				$constructorType,
				new MethodNameIdentifier($resultType->name->identifier),
				$parameterType
			);
			if ($constructorResult !== UnknownMethod::value) {
				if ($constructorResult instanceof ResultType) {
					$validatorInputType = $constructorResult->returnType;
					$errorType = $constructorResult->errorType;
				} else {
					$validatorInputType = $constructorResult;
				}
			}
		}
		$validatorType = $this->analyseValidator(
			$typeRegistry,
			$methodAnalyser,
			$resultType,
			$validatorInputType
		);

		return $errorType ? $typeRegistry->result(
			$validatorType,
			$errorType
		): $validatorType;
	}

	public function analyseValidator(
		TypeRegistry $typeRegistry,
		MethodAnalyser $methodAnalyser,
		Type $resultType,
		Type $parameterType
	): Type {
		// This is the expected input type on top of which the value will be constructed
		$constructingType = $this->getConstructingType(
			$typeRegistry,
			$resultType,
			$parameterType
		);
		$constructorType = $this->getConstructorType($typeRegistry);

		// Convert tuples to records if needed
		$aType = $this->adjustParameterType(
			$typeRegistry,
			$constructingType,
			$parameterType
		);
		// Make sure the passed parameter is a subtype of the expected type
		if (!($aType->isSubtypeOf($constructingType))) {
			throw new AnalyserException(
				sprintf(
					"Invalid constructor value: %s is expected but %s is passed when initializing %s",
					$constructingType,
					$aType,
					$resultType
				)
			);
		}

		$errorType = null;
		$validatorResult = null;
		if ($resultType instanceof NamedType) {
			$validatorResult = $methodAnalyser->safeAnalyseMethod(
				$constructorType,
				new MethodNameIdentifier('as' . $resultType->name->identifier),
				$parameterType
			);
			if ($validatorResult instanceof ResultType) {
				$errorType = $validatorResult->errorType;
			}
		}
		if ($validatorResult === UnknownMethod::value && $resultType instanceof EnumerationType) {
			$matchType = $typeRegistry->union([
				$resultType,
				$typeRegistry->stringSubset(
					array_keys($resultType->values)
				)
			]);
			if (!$parameterType->isSubtypeOf($matchType)) {
				$errorType = $typeRegistry->core->unknownEnumerationValue;
			}
		}
		$outputType = $this->getValidatorOutputType(
			$typeRegistry,
			$resultType,
			$parameterType
		);
		return $errorType ? $typeRegistry->result(
			$outputType,
			$errorType
		): $outputType;
	}

	private function getValidatorOutputType(TypeRegistry $typeRegistry, Type $type, Type $parameterType): Type {
		return match(true) {
			$type instanceof ResultType && $type->returnType instanceof NothingType =>
				$typeRegistry->result(
					$typeRegistry->nothing,
					$parameterType
				),
			$type instanceof EnumerationType => match(true) {
				$parameterType instanceof StringSubsetType && array_all(
					$parameterType->subsetValues,
					fn(string $value) => array_key_exists($value, $type->values)
				) => $type,
				default => $typeRegistry->result(
					$type,
					$typeRegistry->core->unknownEnumerationValue
				)
			},
			default => $type
		};
	}

	private function getValidatorOutputValue(
		ValueRegistry          $valueRegistry,
		Type                   $type,
		Value $parameter
	): Value {
		$et = function(EnumerationType $type, Value $parameterValue) use ($valueRegistry): Value {
			try {
				if ($parameterValue instanceof EnumerationValue &&
					$parameterValue->type->enumeration->name->equals($type->name)
				) {
					return $parameterValue;
				}
				if ($parameterValue instanceof StringValue) {
					return $type->value(new EnumValueIdentifier($parameterValue->literalValue));
				}
			} catch (UnknownEnumerationValue) {}

			return $valueRegistry->error(
				$valueRegistry->core->unknownEnumerationValue(
					$valueRegistry->record([
						'enumeration' => $valueRegistry->type($type),
						'value' => $parameterValue,
					])
				)
			);
		};
		$parameterValue = $parameter;
		if ($type instanceof ResultType) {
			return $valueRegistry->error($parameterValue);
		}
		return match(true) {
			$type instanceof OpenType => $valueRegistry->openValue(
				$type->name, $parameterValue
			),
			$type instanceof SealedType => $valueRegistry->sealedValue(
				$type->name, $parameterValue
			),
			$type instanceof EnumerationType => $et($type, $parameterValue),
			default => throw new ExecutionException(
				sprintf("Cannot construct a value of type: %s", $type)
			)
		};
	}

	public function executeConstructor(
		TypeRegistry $typeRegistry,
		ValueRegistry $valueRegistry,
		MethodContext $methodContext,
		Type $resultType,
		Value $parameter
	): Value {
		$constructorType = $this->getConstructorType($typeRegistry);

		if ($resultType instanceof NamedType) {
			$constructed = $methodContext->safeExecuteMethod(
				$constructorType->value,
				new MethodNameIdentifier($resultType->name->identifier),
				$parameter
			);
			if ($constructed !== UnknownMethod::value) {
				if ($constructed instanceof ErrorValue) {
					return $constructed;
				}
				$parameter = $constructed;
			}
		}

		return $this->executeValidator(
			$typeRegistry,
			$valueRegistry,
			$methodContext,
			$resultType,
			$parameter
		);
	}

	public function executeValidator(
		TypeRegistry $typeRegistry,
		ValueRegistry $valueRegistry,
		MethodContext $methodContext,
		Type $resultType,
		Value $parameter
	): Value {
		$constructingType = $this->getConstructingType(
			$typeRegistry,
			$resultType,
			$parameter->type
		);

		$parameter = $this->adjustParameterValue(
			$valueRegistry,
			$constructingType,
			$parameter
		);

		$constructorType = $this->getConstructorType($typeRegistry);

		if ($resultType instanceof NamedType) {
			$constructed = $methodContext->safeExecuteMethod(
				$constructorType->value,
				new MethodNameIdentifier('as' . $resultType->name->identifier),
				$parameter
			);
			if ($constructed instanceof ErrorValue) {
				return $constructed;
			}
		}
		return $this->getValidatorOutputValue(
			$valueRegistry,
			$resultType,
			$parameter
		);
	}
}