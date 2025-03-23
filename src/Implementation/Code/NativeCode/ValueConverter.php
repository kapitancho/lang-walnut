<?php

namespace Walnut\Lang\Implementation\Code\NativeCode;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionException;
use Walnut\Lang\Blueprint\Common\Identifier\IdentifierException;
use Walnut\Lang\Blueprint\Common\Identifier\MethodNameIdentifier;
use Walnut\Lang\Blueprint\Common\Identifier\TypeNameIdentifier;
use Walnut\Lang\Blueprint\Function\Method;
use Walnut\Lang\Blueprint\Program\Registry\ProgramRegistry;
use Walnut\Lang\Blueprint\Type\OpenType;
use Walnut\Lang\Blueprint\Type\ResultType;
use Walnut\Lang\Blueprint\Type\ShapeType;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Type\UnionType;
use Walnut\Lang\Blueprint\Value\ErrorValue;
use Walnut\Lang\Blueprint\Value\OpenValue;
use Walnut\Lang\Blueprint\Value\Value;
use Walnut\Lang\Implementation\Type\Helper\BaseType;

final readonly class ValueConverter {
	use BaseType;

	public function analyseConvertValueToShape(
		ProgramRegistry $programRegistry,
		Type $sourceType,
		Type $targetType,
	): Type {
		$shapeTargetType = $programRegistry->typeRegistry->shape($targetType);

		if ($sourceType->isSubtypeOf($shapeTargetType)) {
			return $targetType;
		}
		try {
			$methodName = new MethodNameIdentifier(sprintf('as%s',$targetType));
			$method = $programRegistry->methodFinder->methodForType($sourceType, $methodName);
			if ($method instanceof Method) {
				$returnType = $method->analyse(
					$programRegistry,
					$sourceType,
					$programRegistry->typeRegistry->null
				);
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
				return $returnType;
			}
		} catch (IdentifierException) {}

		$bType = $this->toBaseType($sourceType);
		if ($bType instanceof OpenType) {
			try {
				return $this->analyseConvertValueToShape($programRegistry, $bType->valueType, $targetType);
			} catch (AnalyserException) {}
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
			$result->errorValue instanceof OpenValue &&
			$result->errorValue->type->name->equals(
				new TypeNameIdentifier('CastNotAvailable')
			);
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
		while ($tv instanceof OpenValue) {
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
		ProgramRegistry $programRegistry,
		Type $sourceType,
		Type $targetType,
	): Type {
		if ($sourceType->isSubtypeOf($targetType)) {
			return $sourceType;
		}
		try {
			$methodName = new MethodNameIdentifier(sprintf('as%s',$targetType));
			$method = $programRegistry->methodFinder->methodForType($sourceType, $methodName);

			if ($method instanceof Method) {
				$returnType = $method->analyse(
					$programRegistry,
					$sourceType,
					$programRegistry->typeRegistry->null
				);
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
				return $errorType ? $programRegistry->typeRegistry->result($targetType, $errorType) : $targetType;
			}
		} catch (IdentifierException) {}

		return $programRegistry->typeRegistry->result(
			$targetType,
			$programRegistry->typeRegistry->any
			//$programRegistry->typeRegistry->withName(new TypeNameIdentifier('CastNotAvailable'))
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
		try {
			$methodName = new MethodNameIdentifier(sprintf('as%s',$targetType));
			$method = $programRegistry->methodFinder->methodForValue($sourceValue, $methodName);

			if ($method instanceof Method) {
				return $method->execute(
					$programRegistry,
					$sourceValue,
					$programRegistry->valueRegistry->null
				);
			}
		} catch (IdentifierException) {}

		return $programRegistry->valueRegistry->error(
			$programRegistry->valueRegistry->openValue(
				new TypeNameIdentifier('CastNotAvailable'),
				$programRegistry->valueRegistry->record([
					'from' => $programRegistry->valueRegistry->type($sourceValue->type),
					'to' => $programRegistry->valueRegistry->type($targetType)
				])
			)
		);
	}

}