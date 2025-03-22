<?php

namespace Walnut\Lang\Implementation\Code\NativeCode;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Common\Identifier\EnumValueIdentifier;
use Walnut\Lang\Blueprint\Common\Identifier\MethodNameIdentifier;
use Walnut\Lang\Blueprint\Common\Identifier\TypeNameIdentifier;
use Walnut\Lang\Blueprint\Function\Method;
use Walnut\Lang\Blueprint\Program\Registry\ProgramRegistry;
use Walnut\Lang\Blueprint\Program\Registry\TypeRegistry;
use Walnut\Lang\Blueprint\Program\Registry\ValueRegistry;
use Walnut\Lang\Blueprint\Type\AliasType;
use Walnut\Lang\Blueprint\Type\AtomType;
use Walnut\Lang\Blueprint\Type\CustomType;
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

	private function getConstructorType(ProgramRegistry $programRegistry): AtomType {
		return $programRegistry->typeRegistry->atom(
			new TypeNameIdentifier('Constructor')
		);
	}

	private function getConstructingType(TypeRegistry $typeRegistry, Type $type, Type $parameterType): Type {
		return match(true) {
			$type instanceof CustomType => $type->valueType,
			$type instanceof AtomType => $typeRegistry->null,
			$type instanceof AliasType => $type->aliasedType,
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
		ProgramRegistry $programRegistry,
		Type $resultType,
		Type $parameterType
	): Type {
		$constructorType = $this->getConstructorType($programRegistry);

		$errorType = null;
		$validatorInputType = $parameterType;
		if ($resultType instanceof NamedType) {
			$constructorMethod = $programRegistry->methodFinder->methodForType(
				$this->getConstructorType($programRegistry),
				new MethodNameIdentifier($resultType->name->identifier)
			);
			if ($constructorMethod instanceof Method) {
				$constructorResult = $constructorMethod->analyse(
					$programRegistry,
					$constructorType,
					$parameterType
				);
				if ($constructorResult instanceof ResultType) {
					$validatorInputType = $constructorResult->returnType;
					$errorType = $constructorResult->errorType;
				} else {
					$validatorInputType = $constructorResult;
				}
			}
		}
		$validatorType = $this->analyseValidator(
			$programRegistry,
			$resultType,
			$validatorInputType
		);

		return $errorType ? $programRegistry->typeRegistry->result(
			$validatorType,
			$errorType
		): $validatorType;
	}

	public function analyseValidator(
		ProgramRegistry $programRegistry,
		Type $resultType,
		Type $parameterType
	): Type {
		// This is the expected input type on top of which the value will be constructed
		$constructingType = $this->getConstructingType(
			$programRegistry->typeRegistry,
			$resultType,
			$parameterType
		);
		$constructorType = $this->getConstructorType($programRegistry);

		// Convert tuples to records if needed
		$aType = $this->adjustParameterType(
			$programRegistry->typeRegistry,
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
		$validatorMethod = $resultType instanceof NamedType ? $programRegistry->methodFinder->methodForType(
			$constructorType,
			new MethodNameIdentifier('as' . $resultType->name->identifier)
		) : null;
		$errorType = null;
		if ($validatorMethod instanceof Method) {
			$validatorResult = $validatorMethod->analyse(
				$programRegistry,
				$constructorType,
				$parameterType
			);
			if ($validatorResult instanceof ResultType) {
				$errorType = $validatorResult->errorType;
			}
		} elseif ($resultType instanceof EnumerationType) {
			$matchType = $programRegistry->typeRegistry->union([
				$resultType,
				$programRegistry->typeRegistry->stringSubset(
					array_keys($resultType->values)
				)
			]);
			if (!$parameterType->isSubtypeOf($matchType)) {
				$errorType = $programRegistry->typeRegistry->open(
					new TypeNameIdentifier('UnknownEnumerationValue')
				);
			}
		}
		$outputType = $this->getValidatorOutputType(
			$programRegistry->typeRegistry,
			$resultType,
			$parameterType
		);
		return $errorType ? $programRegistry->typeRegistry->result(
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
					$typeRegistry->open(new TypeNameIdentifier('UnknownEnumerationValue'))
				)
			},
			default => $type
		};
	}

	private function getValidatorOutputValue(
		TypeRegistry           $typeRegistry,
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
				$valueRegistry->openValue(
					new TypeNameIdentifier('UnknownEnumerationValue'),
					$valueRegistry->record([
						'enumeration' => $valueRegistry->type($type),
						'value' => $parameterValue,
					])
				)
			);
		};
		$parameterValue = $parameter;
		if ($type instanceof ResultType) {
			return (
				$valueRegistry->error($parameterValue)
			);
		}
		return (
			match(true) {
				$type instanceof OpenType => $valueRegistry->openValue(
					$type->name, $parameterValue
				),
				$type instanceof SealedType => $valueRegistry->sealedValue(
					$type->name, $parameterValue
				),
				$type instanceof AtomType => $type->value,
				$type instanceof EnumerationType => $et($type, $parameterValue),
			}
		);
	}

	public function executeConstructor(
		ProgramRegistry        $programRegistry,
		Type                   $resultType,
		Value $parameter
	): Value {
		$constructorType = $this->getConstructorType($programRegistry);
		$constructorMethod = $resultType instanceof NamedType ? $programRegistry->methodFinder->methodForType(
			$constructorType,
			new MethodNameIdentifier($resultType->name->identifier)
		) : null;

		if ($constructorMethod instanceof Method) {
			$parameter = $constructorMethod->execute(
				$programRegistry,
				($constructorType->value),
				$parameter,
			);
			$resultValue = $parameter;
			if ($resultValue instanceof ErrorValue) {
				return $parameter;
			}
		}
		return $this->executeValidator(
			$programRegistry,
			$resultType,
			$parameter
		);
	}

	public function executeValidator(
		ProgramRegistry        $programRegistry,
		Type                   $resultType,
		Value $parameter
	): Value {
		$constructingType = $this->getConstructingType(
			$programRegistry->typeRegistry,
			$resultType,
			$parameter->type
		);
		$constructorType = $this->getConstructorType($programRegistry);
		$validatorMethod = $resultType instanceof NamedType ? $programRegistry->methodFinder->methodForType(
			$constructorType,
			new MethodNameIdentifier('as' . $resultType->name->identifier)
		) : null;

		$parameter = $this->adjustParameterValue(
			$programRegistry->valueRegistry,
			$constructingType,
			$parameter
		);

		if ($validatorMethod instanceof Method) {
			$result = $validatorMethod->execute(
				$programRegistry,
				($constructorType->value),
				$parameter
			);
			$resultValue = $result;
			if ($resultValue instanceof ErrorValue) {
				return $result;
			}
		}
		return $this->getValidatorOutputValue(
			$programRegistry->typeRegistry,
			$programRegistry->valueRegistry,
			$resultType,
			$parameter
		);
	}
}