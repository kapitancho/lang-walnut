<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Code\Value;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Expression\Expression;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Method\MethodContext;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\ResultType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\UnionType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\CoreType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\NamedType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\TypeRegistry;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\DataValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\ErrorValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\ValueRegistry;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Execution\ExecutionException;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationErrorType;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationFactory;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationFailure;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationSuccess;
use Walnut\Lang\Almond\Engine\Implementation\AnalyserException;
use Walnut\Lang\Almond\Engine\Implementation\Code\Type\Helper\BaseType;
use Walnut\Lang\Almond\Engine\Implementation\MethodNameIdentifier;
use Walnut\Lang\Almond\Engine\Implementation\UnknownMethod;

final readonly class ValueConverter {
	use BaseType;

	public function __construct(
		private ValidationFactory $validationFactory,
		private TypeRegistry $typeRegistry,
		private ValueRegistry $valueRegistry,
		private MethodContext $methodContext,
	) {}

	public function analyseConvertValueToShape(
		Type $sourceType,
		Type $targetType,
	): Type {
		$shapeTargetType = $this->typeRegistry->shape($targetType);

		if ($sourceType->isSubtypeOf($shapeTargetType)) {
			return $targetType;
		}

		$methodNameString = sprintf('as%s', $targetType);
		if (MethodNameIdentifier::isValidIdentifier($methodNameString)) {
			$methodName = new MethodNameIdentifier($methodNameString);

			$returnType = $methodAnalyser->safeAnalyseMethod(
				$sourceType,
				$methodName,
				$this->typeRegistry->null
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

	/** @throws ExecutionException */
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
			$converted = $this->convertValueToType($sourceValue, $convertType);
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
		Expression|null $origin
	): ValidationSuccess|ValidationFailure {
		if ($sourceType->isSubtypeOf($targetType)) {
			return $this->validationFactory->validationSuccess($sourceType);
		}
		if ($targetType instanceof NamedType) {
			$result = $this->methodContext->validateCast(
				$sourceType,
				$targetType->name,
				null
			);
			if ($result instanceof ValidationFailure) {
				return $this->validationFactory->validationSuccess(
					$this->typeRegistry->result($targetType, $this->typeRegistry->any)
				);
			}
			$resultType = $result->type;
			$errorType = $resultType instanceof ResultType ? $resultType->errorType : null;
			$returnType = $resultType instanceof ResultType ? $resultType->returnType : $resultType;
			if (!$resultType->isSubtypeOf($targetType)) {
				return $this->validationFactory->error(
					ValidationErrorType::invalidReturnType,
					sprintf(
						"Cast method returns '%s' which is not a subtype of '%s'",
						$returnType,
						$targetType
					),
					$origin
				);
			}
			return $this->validationFactory->validationSuccess(
				$errorType ?
					$this->typeRegistry->result($targetType, $errorType) :
					$targetType
			);
		}

		return $this->validationFactory->validationSuccess(
			$this->typeRegistry->result(
				$targetType,
				$this->typeRegistry->core->castNotAvailable
			)
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
		if ($targetType instanceof NamedType) {
			$result = $this->methodContext->validateCast(
				$sourceValue->type,
				$targetType->name,
				null
			);
			if ($result instanceof ValidationFailure) {
				return $this->valueRegistry->error(
					$this->valueRegistry->core->castNotAvailable(
						$this->valueRegistry->record([
							'from' => $this->valueRegistry->type($sourceValue->type),
							'to' => $this->valueRegistry->type($targetType)
						])
					)
				);
			}
			return $this->methodContext->executeCast(
				$sourceValue,
				$targetType->name
			);
		}

		return $this->valueRegistry->error(
			$this->valueRegistry->core->castNotAvailable(
				$this->valueRegistry->record([
					'from' => $this->valueRegistry->type($sourceValue->type),
					'to' => $this->valueRegistry->type($targetType)
				])
			)
		);
	}

}