<?php

namespace Walnut\Lang\NativeCode\Type;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionException;
use Walnut\Lang\Blueprint\Common\Identifier\TypeNameIdentifier;
use Walnut\Lang\Blueprint\Common\Type\MetaTypeValue;
use Walnut\Lang\Blueprint\Function\NativeMethod;
use Walnut\Lang\Blueprint\Program\Registry\ProgramRegistry;
use Walnut\Lang\Blueprint\Type\EnumerationSubsetType;
use Walnut\Lang\Blueprint\Type\MetaType;
use Walnut\Lang\Blueprint\Type\StringSubsetType;
use Walnut\Lang\Blueprint\Type\StringType;
use Walnut\Lang\Blueprint\Type\Type as TypeInterface;
use Walnut\Lang\Blueprint\Type\TypeType;
use Walnut\Lang\Blueprint\Value\StringValue;
use Walnut\Lang\Blueprint\Value\Value;
use Walnut\Lang\Blueprint\Value\TypeValue;
use Walnut\Lang\Implementation\Type\Helper\BaseType;

final readonly class ValueWithName implements NativeMethod {
	use BaseType;

	public function analyse(
		ProgramRegistry $programRegistry,
		TypeInterface $targetType,
		TypeInterface $parameterType,
	): TypeInterface {
		if ($targetType instanceof TypeType) {
			$refType = $targetType->refType;
			if ($refType instanceof MetaType) {
				if ($refType->value === MetaTypeValue::Enumeration || $refType->value === MetaTypeValue::EnumerationSubset) {
					return $programRegistry->typeRegistry->result(
						$programRegistry->typeRegistry->any,
						$programRegistry->typeRegistry->data(
							new TypeNameIdentifier('UnknownEnumerationValue'),
						)
					);
				}
			}
			if ($refType instanceof EnumerationSubsetType) {
				if ($parameterType instanceof StringType || $parameterType instanceof StringSubsetType) {
					return $programRegistry->typeRegistry->result(
						$programRegistry->typeRegistry->enumeration($refType->enumeration->name),
						$programRegistry->typeRegistry->data(
							new TypeNameIdentifier('UnknownEnumerationValue'),
						)
					);
				}
				// @codeCoverageIgnoreStart
				throw new AnalyserException(sprintf("[%s] Invalid parameter type: %s", __CLASS__, $parameterType));
				// @codeCoverageIgnoreEnd
			}
		}
		// @codeCoverageIgnoreStart
		throw new AnalyserException(sprintf("[%s] Invalid target type: %s", __CLASS__, $targetType));
		// @codeCoverageIgnoreEnd
	}

	public function execute(
		ProgramRegistry $programRegistry,
		Value $target,
		Value $parameter
	): Value {
		$targetValue = $target;
		$parameterValue = $parameter;
		
		if ($parameterValue instanceof StringValue) {
			if ($targetValue instanceof TypeValue) {
				$refType = $targetValue->typeValue;
				if ($refType instanceof EnumerationSubsetType) {
					return ($refType->subsetValues[$parameterValue->literalValue] ??
						$programRegistry->valueRegistry->error(
							$programRegistry->valueRegistry->dataValue(
								new TypeNameIdentifier('UnknownEnumerationValue'),
								$programRegistry->valueRegistry->record([
									'enumeration' => $programRegistry->valueRegistry->type($refType),
									'value' => $parameterValue,
								])
							)
						)
					);
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