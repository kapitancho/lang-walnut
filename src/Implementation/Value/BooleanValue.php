<?php

namespace Walnut\Lang\Implementation\Value;

use JsonSerializable;
use Walnut\Lang\Blueprint\Code\Analyser\AnalyserContext;
use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Common\Identifier\EnumValueIdentifier;
use Walnut\Lang\Blueprint\Program\Registry\TypeRegistry;
use Walnut\Lang\Blueprint\Type\BooleanType;
use Walnut\Lang\Blueprint\Type\EnumerationSubsetType;
use Walnut\Lang\Blueprint\Value\BooleanValue as BooleanValueInterface;
use Walnut\Lang\Blueprint\Value\Value;

final class BooleanValue implements BooleanValueInterface, JsonSerializable {

    public function __construct(
        private readonly TypeRegistry        $typeRegistry,
        public readonly EnumValueIdentifier  $name,
	    public readonly bool                 $literalValue
    ) {}

	public EnumerationSubsetType $type {
        get => $this->enumeration->subsetType([$this->name]);
    }

	public BooleanType $enumeration {
        get => $this->typeRegistry->boolean;
    }

	public function selfAnalyse(AnalyserContext $analyserContext): void {}

	public function equals(Value $other): bool {
		return $other instanceof BooleanValueInterface && $this->literalValue === $other->literalValue;
	}

	public function __toString(): string {
		return $this->literalValue ? 'true' : 'false';
	}

	public function jsonSerialize(): array {
		return [
			'valueType' => 'Boolean',
			'value' => $this->literalValue ? 'true' : 'false'
		];
	}
}