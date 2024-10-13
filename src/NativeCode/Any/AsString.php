<?php

namespace Walnut\Lang\NativeCode\Any;

use Walnut\Lang\Blueprint\Code\Scope\TypedValue;
use Walnut\Lang\Blueprint\Function\NativeMethod;
use Walnut\Lang\Blueprint\Function\MethodExecutionContext;
use Walnut\Lang\Blueprint\Identifier\TypeNameIdentifier;
use Walnut\Lang\Blueprint\Range\PlusInfinity;
use Walnut\Lang\Blueprint\Type\BooleanType;
use Walnut\Lang\Blueprint\Type\FalseType;
use Walnut\Lang\Blueprint\Type\IntegerType;
use Walnut\Lang\Blueprint\Type\NullType;
use Walnut\Lang\Blueprint\Type\RealType;
use Walnut\Lang\Blueprint\Type\ResultType;
use Walnut\Lang\Blueprint\Type\StringSubsetType;
use Walnut\Lang\Blueprint\Type\StringType;
use Walnut\Lang\Blueprint\Type\TrueType;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Type\TypeType;
use Walnut\Lang\Blueprint\Value\BooleanValue;
use Walnut\Lang\Blueprint\Value\IntegerValue;
use Walnut\Lang\Blueprint\Value\MutableValue;
use Walnut\Lang\Blueprint\Value\NullValue;
use Walnut\Lang\Blueprint\Value\RealValue;
use Walnut\Lang\Blueprint\Value\StringValue;
use Walnut\Lang\Blueprint\Value\SubtypeValue;
use Walnut\Lang\Blueprint\Value\TypeValue;
use Walnut\Lang\Blueprint\Value\Value;
use Walnut\Lang\Implementation\Type\AliasType;
use Walnut\Lang\Implementation\Type\EnumerationType;
use Walnut\Lang\Implementation\Type\IntegerSubsetType;
use Walnut\Lang\Implementation\Type\RealSubsetType;
use Walnut\Lang\Implementation\Value\EnumerationValue;

final readonly class AsString implements NativeMethod {
	public function __construct(
		private MethodExecutionContext $context
	) {}

	public function analyse(
		Type $targetType,
		Type $parameterType
	): StringType|ResultType {
		while ($targetType instanceof AliasType) {
			$targetType = $targetType->aliasedType();
		}
		[$minLength, $maxLength] = match (true) {
			$targetType instanceof IntegerType,
			$targetType instanceof IntegerSubsetType => [
				1,
				$targetType->range()->maxValue() === PlusInfinity::value ? 1000 :
					max(1,
						(int)ceil(log10(abs($targetType->range()->maxValue))),
						(int)ceil(log10(abs($targetType->range()->minValue))) +
							($targetType->range()->minValue() < 0 ? 1 : 0)
					)

			],
			$targetType instanceof RealType,
			$targetType instanceof RealSubsetType => [1, 1000],
			$targetType instanceof BooleanType => [4, 5],
			$targetType instanceof NullType, $targetType instanceof TrueType => [4, 4],
			$targetType instanceof FalseType => [5, 5],
			$targetType instanceof StringType,
			$targetType instanceof StringSubsetType => [
				$targetType->range()->minLength(),
				$targetType->range()->maxLength(),
			],
			$targetType instanceof TypeType => [1, PlusInfinity::value],
			$targetType instanceof EnumerationType => [
				min(array_map(static fn(EnumerationValue $value): int => mb_strlen($value->name()), $targetType->values())),
				max(array_map(static fn(EnumerationValue $value): int => mb_strlen($value->name()), $targetType->values()))
			],
			default => ($c = $this->context->typeRegistry()->result(
				$this->context->typeRegistry()->string(),
				$this->context->typeRegistry()->sealed(new TypeNameIdentifier("CastNotAvailable"))
			)) ? [0, PlusInfinity::value] : null
			//throw new AnalyserException(sprintf("[%s] Invalid target type: %s", __CLASS__, $targetType))
		};
		if ($c ??= null) {
			return $c;
		}
		return $this->context->typeRegistry()->string($minLength, $maxLength);
	}

	public function execute(
		TypedValue $target,
		TypedValue $parameter
	): TypedValue {
		$targetValue = $target->value;

		$result = $this->evaluate($targetValue);
        return TypedValue::forValue($result === null ?
	        $this->context->valueRegistry()->error(
				$this->context->valueRegistry()->sealedValue(
					new TypeNameIdentifier("CastNotAvailable"),
					$this->context->valueRegistry()->record([
						'from' => $this->context->valueRegistry()->type($targetValue->type()),
						'to' => $this->context->valueRegistry()->type($this->context->typeRegistry()->string())
					])
				)
			) :
	        $this->context->valueRegistry()->string(
                $this->evaluate($targetValue)
            )
        );
	}

    private function evaluate(Value $value): string|null {
        return match (true) {
            $value instanceof IntegerValue => (string)$value->literalValue(),
            $value instanceof RealValue => (string) $value->literalValue(),
            $value instanceof StringValue => $value->literalValue(),
            $value instanceof BooleanValue => $value->literalValue() ? 'true' : 'false',
            $value instanceof NullValue => 'null',
            $value instanceof TypeValue => (string)$value->typeValue(),
            $value instanceof SubtypeValue => $this->evaluate($value->baseValue()),
            $value instanceof MutableValue => $this->evaluate($value->value()),
	        $value instanceof EnumerationValue => $value->name(),
            //TODO: check for cast to jsonValue (+subtype as well)
            //TODO: error values
            //default => throw new ExecutionException("Invalid target value")
	        default => null
        };
    }
}