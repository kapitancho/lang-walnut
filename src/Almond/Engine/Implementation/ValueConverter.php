<?php

namespace Walnut\Lang\Almond\Engine\Implementation;

use Walnut\Lang\Almond\Engine\Blueprint\Execution\ExecutionException;
use Walnut\Lang\Almond\Engine\Blueprint\Identifier\MethodName;
use Walnut\Lang\Almond\Engine\Blueprint\Method\MethodContext;
use Walnut\Lang\Almond\Engine\Blueprint\Registry\TypeRegistry;
use Walnut\Lang\Almond\Engine\Blueprint\Registry\ValueRegistry;
use Walnut\Lang\Almond\Engine\Blueprint\Type\ResultType;
use Walnut\Lang\Almond\Engine\Blueprint\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Value\Value;

final readonly class ValueConverter {

	public function __construct(
		private TypeRegistry $typeRegistry,
		private ValueRegistry $valueRegistry,
		private MethodContext $methodContext,
	) {}

	public function analyseConvertValueToShape(
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
		Type $sourceType,
		Type $targetType,
	): Type {
		if ($sourceType->isSubtypeOf($targetType)) {
			return $sourceType;
		}
		$methodNameString = sprintf('as%s', $targetType);
		if (MethodName::isValidIdentifier($methodNameString)) {
			$methodName = new MethodName($methodNameString);

			$returnType = $this->methodContext->validateMethod(
				$sourceType,
				$methodName,
				$this->typeRegistry->null,
				$origin
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

	/** @throws ExecutionException */
	public function convertValueToType(
		Value $sourceValue,
		Type $targetType
	): Value {
		if ($sourceValue->type->isSubtypeOf($targetType)) {
			return $sourceValue;
		}
		$methodNameString = sprintf('as%s', $targetType);
		if (MethodName::isValidIdentifier($methodNameString)) {
			$methodName = new MethodName($methodNameString);

			return $this->methodContext->executeMethod(
				$sourceValue,
				$methodName,
				$this->valueRegistry->null
			);
		}

		return $this->valueRegistry->error(
			$this->valueRegistry->core->castNotAvailable(
				$this->valueRegistry->record([
					'from' => $programRegistry->valueRegistry->type($sourceValue->type),
					'to' => $programRegistry->valueRegistry->type($targetType)
				])
			)
		);
	}

}