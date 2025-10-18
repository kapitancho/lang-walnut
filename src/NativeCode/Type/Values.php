<?php

namespace Walnut\Lang\NativeCode\Type;

use BcMath\Number;
use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionException;
use Walnut\Lang\Blueprint\Common\Type\MetaTypeValue;
use Walnut\Lang\Blueprint\Function\NativeMethod;
use Walnut\Lang\Blueprint\Program\Registry\ProgramRegistry;
use Walnut\Lang\Blueprint\Type\EnumerationSubsetType;
use Walnut\Lang\Blueprint\Type\IntegerSubsetType;
use Walnut\Lang\Blueprint\Type\MetaType;
use Walnut\Lang\Blueprint\Type\NullType;
use Walnut\Lang\Blueprint\Type\RealSubsetType;
use Walnut\Lang\Blueprint\Type\StringSubsetType;
use Walnut\Lang\Blueprint\Type\Type as TypeInterface;
use Walnut\Lang\Blueprint\Type\TypeType;
use Walnut\Lang\Blueprint\Value\IntegerValue;
use Walnut\Lang\Blueprint\Value\NullValue;
use Walnut\Lang\Blueprint\Value\RealValue;
use Walnut\Lang\Blueprint\Value\StringValue;
use Walnut\Lang\Blueprint\Value\Value;
use Walnut\Lang\Blueprint\Value\TypeValue;

final readonly class Values implements NativeMethod {

	public function analyse(
		ProgramRegistry $programRegistry,
		TypeInterface $targetType,
		TypeInterface $parameterType,
	): TypeInterface {
		if ($parameterType instanceof NullType) {
			if ($targetType instanceof TypeType) {
				$refType = $targetType->refType;
				if ($refType instanceof MetaType) {
					$t = match($refType->value) {
						MetaTypeValue::EnumerationValue,
						MetaTypeValue::Enumeration,
						MetaTypeValue::EnumerationSubset
							=> $programRegistry->typeRegistry->any,
						MetaTypeValue::IntegerSubset => $programRegistry->typeRegistry->integer(),
						MetaTypeValue::RealSubset => $programRegistry->typeRegistry->real(),
						MetaTypeValue::StringSubset => $programRegistry->typeRegistry->string(),
						default => null
					};
					if ($t) {
						return $programRegistry->typeRegistry->array($t, 1);
					}
				}
				if ($refType instanceof IntegerSubsetType ||
					$refType instanceof RealSubsetType ||
					$refType instanceof StringSubsetType ||
					$refType instanceof EnumerationSubsetType
				) {
					$t = match(true) {
						$refType instanceof IntegerSubsetType => $programRegistry->typeRegistry->integer(),
						$refType instanceof RealSubsetType => $programRegistry->typeRegistry->real(),
						$refType instanceof StringSubsetType => $programRegistry->typeRegistry->string(),
						$refType instanceof EnumerationSubsetType => $programRegistry->typeRegistry->enumeration(
							$refType->enumeration->name,
						),
					};
					$l = count($refType->subsetValues);
					return $programRegistry->typeRegistry->array($t, $l, $l);
				}
			}
			// @codeCoverageIgnoreStart
			throw new AnalyserException(sprintf("[%s] Invalid target type: %s", __CLASS__, $targetType));
			// @codeCoverageIgnoreEnd
		}
		throw new AnalyserException(sprintf("[%s] Invalid parameter type: %s", __CLASS__, $parameterType));
	}

	public function execute(
		ProgramRegistry $programRegistry,
		Value $target,
		Value $parameter
	): Value {
		$targetValue = $target;
		$parameterValue = $parameter;
		
		if ($parameterValue instanceof NullValue) {
			if ($targetValue instanceof TypeValue) {
				$refType = $targetValue->typeValue;
				if ($refType instanceof EnumerationSubsetType) {
					return ($programRegistry->valueRegistry->tuple(
						array_values(
							array_unique(
								$refType->subsetValues
							)
						)
					));
				} elseif ($refType instanceof IntegerSubsetType) {
					return ($programRegistry->valueRegistry->tuple(
						array_map(
							fn(Number $value): IntegerValue => $programRegistry->valueRegistry->integer($value),
							array_values(
								array_unique(
									$refType->subsetValues
								)
							)
						)
					));
				} elseif ($refType instanceof RealSubsetType) {
					return ($programRegistry->valueRegistry->tuple(
						array_map(
							fn(Number $value): RealValue => $programRegistry->valueRegistry->real($value),
							array_values(
								array_unique(
									$refType->subsetValues
								)
							)
						)
					));
				} elseif ($refType instanceof StringSubsetType) {
					return ($programRegistry->valueRegistry->tuple(
						array_map(
							fn(string $value): StringValue => $programRegistry->valueRegistry->string($value),
							array_values(
								array_unique(
									$refType->subsetValues
								)
							)
						)
					));
				}
			}
			// @codeCoverageIgnoreStart
			throw new ExecutionException("Invalid target value");
			// @codeCoverageIgnoreEnd
		}
		// @codeCoverageIgnoreStart
		throw new ExecutionException("Invalid parameter value");
		// @codeCoverageIgnoreEnd
	}

}