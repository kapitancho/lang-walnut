<?php

namespace Walnut\Lang\Implementation\Code\NativeCode;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionException;
use Walnut\Lang\Blueprint\Common\Identifier\MethodNameIdentifier;
use Walnut\Lang\Blueprint\Function\UnknownMethod;
use Walnut\Lang\Blueprint\Program\Registry\MethodAnalyser;
use Walnut\Lang\Blueprint\Program\Registry\ProgramRegistry;
use Walnut\Lang\Blueprint\Program\Registry\TypeRegistry;
use Walnut\Lang\Blueprint\Type\CoreType;
use Walnut\Lang\Blueprint\Type\ResultType;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Type\UnionType;
use Walnut\Lang\Blueprint\Value\DataValue;
use Walnut\Lang\Blueprint\Value\ErrorValue;
use Walnut\Lang\Blueprint\Value\Value;
use Walnut\Lang\Implementation\Type\Helper\BaseType;

final readonly class ValueConverter {
	use BaseType;

	public function analyseConvertValueToShape(
		TypeRegistry $typeRegistry,
		MethodAnalyser $methodAnalyser,
		Type $sourceType,
		Type $targetType,
	): Type {
		$shapeTargetType = $typeRegistry->shape($targetType);

		if ($sourceType->isSubtypeOf($shapeTargetType)) {
			return $targetType;
		}

		$methodNameString = sprintf('as%s', $targetType);
		if (MethodNameIdentifier::isValidIdentifier($methodNameString)) {
			$methodName = new MethodNameIdentifier($methodNameString);

			$returnType = $methodAnalyser->safeAnalyseMethod(
				$sourceType,
				$methodName,
				$typeRegistry->null
			);
			if ($returnType !== UnknownMethod::value) {
				if ($returnType instanceof ResultType) {
					throw new AnalyserException(
						sprintf(
							"Cannot convert value of type '%s' to shape '%s' because the cast may return an error of type %s",
							$sourceType,
							$targetType,
							$returnType->errorType
						)
					);
				}
				// @codeCoverageIgnoreStart
				if (!$returnType->isSubtypeOf($targetType)) {
					throw new AnalyserException(sprintf(
						"Cast method '%s' returns '%s' which is not a subtype of '%s'",
						$methodName,
						$returnType,
						$targetType
					));
				}
				return $returnType;
				// @codeCoverageIgnoreEnd
			}
		}
		throw new AnalyserException(
			sprintf(
				"Cannot convert value of type '%s' to shape '%s'",
				$sourceType,
				$targetType
			)
		);
	}

	private function isError(Value $result): bool {
		return
			$result instanceof ErrorValue &&
			$result->errorValue instanceof DataValue &&
			$result->errorValue->type->name->equals(CoreType::CastNotAvailable->typeName());
	}

	public function convertValueToShape(
		ProgramRegistry $programRegistry,
		Value $sourceValue,
		Type $targetType
	): Value {
		if ($sourceValue->type->isSubtypeOf($targetType)) {
			return $sourceValue;
		}
		$tv = $sourceValue;
		while ($tv instanceof DataValue) {
			if ($tv->type->valueType->isSubtypeOf($targetType)) {
				return $tv->value;
			}
			$tv = $tv->value;
		}
		$baseType = $this->toBaseType($targetType);

		$convertTypes = $baseType instanceof UnionType ? $baseType->types : [];
		foreach([$targetType, ... $convertTypes] as $convertType) {
			$converted = $this->convertValueToType($programRegistry, $sourceValue, $convertType);
			if (!$this->isError($converted)) {
				return $converted;
			}
		}

		// @codeCoverageIgnoreStart
		throw new ExecutionException("Invalid parameter value");
		// @codeCoverageIgnoreEnd
	}

	public function analyseConvertValueToType(
		TypeRegistry $typeRegistry,
		MethodAnalyser $methodAnalyser,
		Type $sourceType,
		Type $targetType,
	): Type {
		if ($sourceType->isSubtypeOf($targetType)) {
			return $sourceType;
		}
		$methodNameString = sprintf('as%s', $targetType);
		if (MethodNameIdentifier::isValidIdentifier($methodNameString)) {
			$methodName = new MethodNameIdentifier($methodNameString);

			$returnType = $methodAnalyser->safeAnalyseMethod(
				$sourceType,
				$methodName,
				$typeRegistry->null
			);
			if ($returnType !== UnknownMethod::value) {
				$errorType = $returnType instanceof ResultType ? $returnType->errorType : null;
				$returnType = $returnType instanceof ResultType ? $returnType->returnType : $returnType;
				if (!$returnType->isSubtypeOf($targetType)) {
					// @codeCoverageIgnoreStart
					throw new AnalyserException(sprintf(
						"Cast method '%s' returns '%s' which is not a subtype of '%s'",
						$methodName,
						$returnType,
						$targetType
					));
					// @codeCoverageIgnoreEnd
				}
				return $errorType ? $typeRegistry->result($targetType, $errorType) : $targetType;
			}
		}

		return $typeRegistry->result(
			$targetType,
			$typeRegistry->any
			//$typeRegistry->withName(new TypeNameIdentifier('CastNotAvailable'))
		);
	}

	public function convertValueToType(
		ProgramRegistry $programRegistry,
		Value $sourceValue,
		Type $targetType
	): Value {
		if ($sourceValue->type->isSubtypeOf($targetType)) {
			return $sourceValue;
		}
		$methodNameString = sprintf('as%s', $targetType);
		if (MethodNameIdentifier::isValidIdentifier($methodNameString)) {
			$methodName = new MethodNameIdentifier($methodNameString);

			$result = $programRegistry->methodContext->safeExecuteMethod(
				$sourceValue,
				$methodName,
				$programRegistry->valueRegistry->null
			);
			if ($result !== UnknownMethod::value) {
				return $result;
			}
		}

		return $programRegistry->valueRegistry->error(
			$programRegistry->valueRegistry->core->castNotAvailable(
				$programRegistry->valueRegistry->record([
					'from' => $programRegistry->valueRegistry->type($sourceValue->type),
					'to' => $programRegistry->valueRegistry->type($targetType)
				])
			)
		);
	}

}