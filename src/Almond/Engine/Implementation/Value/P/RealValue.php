<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Value\P;

use BcMath\Number;
use JsonSerializable;
use Walnut\Lang\Almond\Engine\Blueprint\Dependency\DependencyContext;
use Walnut\Lang\Almond\Engine\Blueprint\Registry\TypeRegistry;
use Walnut\Lang\Almond\Engine\Blueprint\Type\RealSubsetType as RealSubsetTypeInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Validation\ValidationRequest;
use Walnut\Lang\Almond\Engine\Blueprint\Validation\ValidationResult;
use Walnut\Lang\Almond\Engine\Blueprint\Value\IntegerValue as IntegerValueInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Value\RealValue as RealValueInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Value\Value;

final class RealValue implements RealValueInterface, JsonSerializable {

	public function __construct(
		private readonly TypeRegistry $typeRegistry,
		public readonly Number $literalValue
	) {}

	public RealSubsetTypeInterface $type {
		get => $this->typeRegistry->realSubset([$this->literalValue]);
	}

	private function normalize(Number $value): string {
		$v = $value->value;
		if (str_contains($v, '.')) {
			$v = rtrim($v, '0');
			$v = rtrim($v, '.');
		}
		return $v;
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
		return $this->normalize($this->literalValue);
	}

	public function jsonSerialize(): array {
		return [
			'valueType' => 'Real',
			'value' => (float)(string)$this->literalValue
		];
	}
}
