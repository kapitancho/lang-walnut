<?php

namespace Walnut\Lang\Implementation\Value;

use BcMath\Number;
use JsonSerializable;
use Walnut\Lang\Blueprint\Code\Analyser\AnalyserContext;
use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Program\Registry\TypeRegistry;
use Walnut\Lang\Blueprint\Value\IntegerValue as IntegerValueInterface;
use Walnut\Lang\Blueprint\Value\RealValue as RealValueInterface;
use Walnut\Lang\Blueprint\Value\Value;
use Walnut\Lang\Implementation\Type\IntegerSubsetType;

final class IntegerValue implements IntegerValueInterface, JsonSerializable {

    public function __construct(
		private readonly TypeRegistry $typeRegistry,
		public readonly Number $literalValue
    ) {}

	public IntegerSubsetType $type {
		get => $this->typeRegistry->integerSubset([$this->literalValue]);
    }

	private function normalize(Number $value): string {
		$v = $value->value;
		if (str_contains($v, '.')) {
			$v = rtrim($v, '0');
			$v = rtrim($v, '.');
		}
		return $v;
	}

	public function asRealValue(): RealValue {
		return new RealValue($this->typeRegistry, $this->literalValue);
	}

	public function equals(Value $other): bool {
		return (($other instanceof RealValueInterface || $other instanceof IntegerValueInterface) &&
			$this->normalize($this->literalValue) === $this->normalize($other->literalValue));
	}


	/** @throws AnalyserException */
	public function selfAnalyse(AnalyserContext $analyserContext): void {}

	public function __toString(): string {
		return (string)$this->literalValue;
	}

	public function jsonSerialize(): array {
		return [
			'valueType' => 'Integer',
			'value' => (int)(string)$this->literalValue
		];
	}
}