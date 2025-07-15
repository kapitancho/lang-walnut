<?php

namespace Walnut\Lang\Implementation\Value;

use JsonSerializable;
use Walnut\Lang\Blueprint\AST\Parser\EscapeCharHandler;
use Walnut\Lang\Blueprint\Code\Analyser\AnalyserContext;
use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Program\Registry\TypeRegistry;
use Walnut\Lang\Blueprint\Value\StringValue as StringValueInterface;
use Walnut\Lang\Blueprint\Value\Value;
use Walnut\Lang\Implementation\Type\StringSubsetType;

final class StringValue implements StringValueInterface, JsonSerializable {

    public function __construct(
		private readonly TypeRegistry $typeRegistry,
		private readonly EscapeCharHandler $escapeCharHandler,
		public readonly string $literalValue
    ) {}

	public StringSubsetType $type {
		get => $this->typeRegistry->stringSubset([$this->literalValue]);
    }

	public function equals(Value $other): bool {
		return $other instanceof StringValueInterface && $this->literalValue === $other->literalValue;
	}

	/** @throws AnalyserException */
	public function selfAnalyse(AnalyserContext $analyserContext): void {}

	public function __toString(): string {
		return $this->escapeCharHandler->escape($this->literalValue);
	}

	public function jsonSerialize(): array {
		return [
			'valueType' => 'String',
			'value' => $this->literalValue
		];
	}
}