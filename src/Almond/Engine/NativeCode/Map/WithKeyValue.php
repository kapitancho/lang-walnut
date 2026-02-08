<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Map;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Method\NativeMethod;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\MapType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\RecordType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\StringSubsetType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\TypeRegistry;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\RecordValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\StringValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\ValueRegistry;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Range\PlusInfinity;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Execution\ExecutionException;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationErrorType;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationFactory;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationFailure;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationSuccess;
use Walnut\Lang\Almond\Engine\Implementation\Code\Type\Helper\BaseType;

final readonly class WithKeyValue implements NativeMethod {
	use BaseType;

	public function __construct(
		private ValidationFactory $validationFactory,
		private TypeRegistry $typeRegistry,
		private ValueRegistry $valueRegistry,
	) {}

	public function validate(Type $targetType, Type $parameterType, mixed $origin): ValidationSuccess|ValidationFailure {
		$targetType = $this->toBaseType($targetType);
		if ($targetType instanceof RecordType || $targetType instanceof MapType) {
			if ($parameterType->isSubtypeOf(
				$this->typeRegistry->record([
					'key' => $this->typeRegistry->string(),
					'value' => $this->typeRegistry->any
				], null)
			)) {
				$pType = $this->toBaseType($parameterType);
				$keyType = $pType instanceof RecordType ? ($pType->types['key'] ?? null) : null;
				if ($targetType instanceof RecordType) {
					if ($keyType instanceof StringSubsetType && count($keyType->subsetValues) === 1) {
						$keyValue = $keyType->subsetValues[0];
						$valueType = $pType instanceof RecordType ? ($pType->types['value'] ?? null) : null;
						return $this->validationFactory->validationSuccess(
							$this->typeRegistry->record(
								$targetType->types + [
									$keyValue => $valueType
								],
								$targetType->restType
							)
						);
					}
					$targetType = $targetType->asMapType();
				}
				$valueType = $pType instanceof RecordType ? ($pType->types['value'] ?? null) : null;
				return $this->validationFactory->validationSuccess(
					$this->typeRegistry->map(
						$this->typeRegistry->union(array_filter([
							$targetType->itemType,
							$valueType
						])),
						$targetType->range->minLength,
						$targetType->range->maxLength === PlusInfinity::value ?
							PlusInfinity::value : $targetType->range->maxLength + 1,
						$this->typeRegistry->union(array_filter([
							$targetType->keyType,
							$keyType
						]))
					)
				);
			}
			return $this->validationFactory->error(
				ValidationErrorType::invalidParameterType,
				sprintf("[%s] Invalid parameter type: %s", __CLASS__, $parameterType),
				origin: $origin
			);
		}
		// @codeCoverageIgnoreStart
		return $this->validationFactory->error(
			ValidationErrorType::invalidTargetType,
			sprintf("[%s] Invalid target type: %s", __CLASS__, $targetType),
			origin: $origin
		);
		// @codeCoverageIgnoreEnd
	}

	public function execute(Value $target, Value $parameter): Value {
		if ($target instanceof RecordValue) {
			if ($parameter instanceof RecordValue) {
				$p = $parameter->values;
				$pKey = $p['key'] ?? null;
				$pValue = $p['value'] ?? null;
				if ($pValue && $pKey instanceof StringValue) {
					$values = $target->values;
					$values[$pKey->literalValue] = $pValue;
					return $this->valueRegistry->record($values);
				}
			}
			// @codeCoverageIgnoreStart
			throw new ExecutionException("Invalid parameter value");
			// @codeCoverageIgnoreEnd
		}
		// @codeCoverageIgnoreStart
		throw new ExecutionException("Invalid target value");
		// @codeCoverageIgnoreEnd
	}
}
