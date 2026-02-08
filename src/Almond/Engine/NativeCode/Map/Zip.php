<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Map;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Method\NativeMethod;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\MapType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\NothingType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\OptionalKeyType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\RecordType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\TypeRegistry;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\RecordValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\ValueRegistry;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Range\PlusInfinity;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Execution\ExecutionException;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationErrorType;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationFactory;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationFailure;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationSuccess;
use Walnut\Lang\Almond\Engine\Implementation\Code\Type\Helper\BaseType;

final readonly class Zip implements NativeMethod {
	use BaseType;

	public function __construct(
		private ValidationFactory $validationFactory,
		private TypeRegistry $typeRegistry,
		private ValueRegistry $valueRegistry,
	) {}

	public function validate(Type $targetType, Type $parameterType, mixed $origin): ValidationSuccess|ValidationFailure {
		$type = $this->toBaseType($targetType);
		$pType = $this->toBaseType($parameterType);
		if ($type instanceof RecordType && $pType instanceof RecordType) {
			$resultType = [];
			$keys = array_values(array_unique(array_merge(
				array_keys($type->types), array_keys($pType->types)
			)));
			foreach ($keys as $key) {
				$tg = $type->types[$key] ?? $type->restType;
				$pr = $pType->types[$key] ?? $pType->restType;
				$isOptional = false;
				if ($tg instanceof OptionalKeyType) {
					$tg = $tg->valueType;
					$isOptional = true;
				}
				if ($pr instanceof OptionalKeyType) {
					$pr = $pr->valueType;
					$isOptional = true;
				}
				if (!$tg instanceof NothingType && !$pr instanceof NothingType) {
					$tuple = $this->typeRegistry->tuple([$tg, $pr], null);
					$resultType[$key] = $isOptional ? $this->typeRegistry->optionalKey($tuple) : $tuple;
				}
			}
			return $this->validationFactory->validationSuccess(
				$this->typeRegistry->record($resultType,
					$type->restType instanceof NothingType || $pType->restType instanceof NothingType ?
						$this->typeRegistry->nothing : $this->typeRegistry->tuple([
						$type->restType,
						$pType->restType,
					], null)
				)
			);
		}
		$type = $type instanceof RecordType ? $type->asMapType() : $type;
		$pType = $pType instanceof RecordType ? $pType->asMapType() : $pType;
		if ($type instanceof MapType) {
			if ($pType instanceof MapType) {
				return $this->validationFactory->validationSuccess(
					$this->typeRegistry->map(
						$this->typeRegistry->tuple([
							$type->itemType,
							$pType->itemType,
						], null),
						min($type->range->minLength, $pType->range->minLength),
						match(true) {
							$type->range->maxLength === PlusInfinity::value => $pType->range->maxLength,
							$pType->range->maxLength === PlusInfinity::value => $type->range->maxLength,
							default => min($type->range->maxLength, $pType->range->maxLength)
						},
						$this->typeRegistry->intersection([
							$type->keyType,
							$pType->keyType
						])
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
				$values = $target->values;
				$pValues = $parameter->values;
				$result = [];
				foreach($values as $key => $value) {
					$pValue = $pValues[$key] ?? null;
					if (!$pValue) {
						continue;
					}
					$result[$key] = $this->valueRegistry->tuple([$value, $pValue]);
				}
				return $this->valueRegistry->record($result);
			}
			// @codeCoverageIgnoreStart
			throw new ExecutionException("Invalid parameter type");
			// @codeCoverageIgnoreEnd
		}
		// @codeCoverageIgnoreStart
		throw new ExecutionException("Invalid target value");
		// @codeCoverageIgnoreEnd
	}
}
