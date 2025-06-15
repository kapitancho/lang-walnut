<?php

namespace Walnut\Lang\Implementation\Value;

use JsonSerializable;
use Walnut\Lang\Blueprint\Code\Analyser\AnalyserContext;
use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Program\Registry\TypeRegistry;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Type\TypeType;
use Walnut\Lang\Blueprint\Value\TypeValue as TypeValueInterface;
use Walnut\Lang\Blueprint\Value\Value;

final class TypeValue implements TypeValueInterface, JsonSerializable {

    public function __construct(
		private readonly TypeRegistry $typeRegistry,
		public readonly Type $typeValue
    ) {}

	public TypeType $type {
		get => $this->typeRegistry->type($this->typeValue);
    }

	public function equals(Value $other): bool {
		return $other instanceof TypeValueInterface &&
			$this->typeValue->isSubtypeOf($other->typeValue) &&
			$other->typeValue->isSubtypeOf($this->typeValue);
	}

	/** @throws AnalyserException */
	public function selfAnalyse(AnalyserContext $analyserContext): void {}

	public function __toString(): string {
		$val = (string)$this->typeValue;
		return sprintf(
			"type%s",
			str_starts_with($val, '[') && str_ends_with($val, ']') ?
				$val : '{' . $val . '}'
		);
	}

	public function jsonSerialize(): array {
		return [
			'valueType' => 'Type',
			'value' => $this->typeValue
		];
	}
}