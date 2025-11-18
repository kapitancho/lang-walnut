<?php

namespace Walnut\Lang\Implementation\Value;

use JsonSerializable;
use Walnut\Lang\Blueprint\Code\Analyser\AnalyserContext;
use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Program\Registry\TypeRegistry;
use Walnut\Lang\Blueprint\Type\NullType;
use Walnut\Lang\Blueprint\Value\NullValue as NullValueInterface;
use Walnut\Lang\Blueprint\Value\Value;

final class NullValue implements NullValueInterface, JsonSerializable {

    public function __construct(
        private readonly TypeRegistry $typeRegistry
    ) {}

	public NullType $type {
        get => $this->typeRegistry->null;
    }

	public null $literalValue {
		get => null;
	}

	public function equals(Value $other): bool {
		return $other instanceof NullValueInterface;
	}

	public function selfAnalyse(AnalyserContext $analyserContext): void {}

	public function __toString(): string {
		return 'null';
	}

	public function jsonSerialize(): array {
		return [
			'valueType' => 'Null'
		];
	}

}