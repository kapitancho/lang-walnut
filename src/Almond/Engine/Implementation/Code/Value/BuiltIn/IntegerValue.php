<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Code\Value\BuiltIn;

use BcMath\Number;
use JsonSerializable;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\IntegerSubsetType as IntegerSubsetTypeInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\TypeRegistry;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\IntegerValue as IntegerValueInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\RealValue as RealValueInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Engine\Blueprint\Feature\DependencyContainer\DependencyContext;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationRequest;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationResult;

final class IntegerValue implements IntegerValueInterface, JsonSerializable {

	public function __construct(
		private readonly TypeRegistry $typeRegistry,
		public readonly Number $literalValue
	) {}

	public IntegerSubsetTypeInterface $type {
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
		return ($other instanceof RealValueInterface || $other instanceof IntegerValueInterface) &&
			$this->normalize($this->literalValue) === $this->normalize($other->literalValue);
	}

	public function validate(ValidationRequest $request): ValidationResult {
		return $request->ok();
	}

	public function validateDependencies(DependencyContext $dependencyContext): DependencyContext {
		return $dependencyContext;
	}

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
