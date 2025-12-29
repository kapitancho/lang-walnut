<?php

namespace Walnut\Lang\Implementation\Value;

use JsonSerializable;
use Walnut\Lang\Blueprint\AST\Parser\EscapeCharHandler;
use Walnut\Lang\Blueprint\Code\Analyser\AnalyserContext;
use Walnut\Lang\Blueprint\Program\Registry\TypeRegistry;
use Walnut\Lang\Blueprint\Type\BytesType;
use Walnut\Lang\Blueprint\Value\BytesValue as BytesValueInterface;
use Walnut\Lang\Blueprint\Value\Value;

final class BytesValue implements BytesValueInterface, JsonSerializable {

    public function __construct(
		private readonly TypeRegistry $typeRegistry,
		private readonly EscapeCharHandler $escapeCharHandler,
		public readonly string $literalValue
    ) {}

	public BytesType $type {
		get => $this->typeRegistry->bytes(
			$l = strlen($this->literalValue),
			$l
		);
    }

	public function equals(Value $other): bool {
		return $other instanceof BytesValueInterface && $this->literalValue === $other->literalValue;
	}

	public function selfAnalyse(AnalyserContext $analyserContext): void {}

	public function __toString(): string {
		return $this->escapeCharHandler->escape($this->literalValue);
	}

	public function jsonSerialize(): array {
		return [
			'valueType' => 'Bytes',
			'value' => $this->literalValue
		];
	}
}