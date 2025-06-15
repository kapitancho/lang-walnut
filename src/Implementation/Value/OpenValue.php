<?php

namespace Walnut\Lang\Implementation\Value;

use JsonSerializable;
use Walnut\Lang\Blueprint\Code\Analyser\AnalyserContext;
use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Common\Identifier\TypeNameIdentifier;
use Walnut\Lang\Blueprint\Program\Registry\TypeRegistry;
use Walnut\Lang\Blueprint\Type\OpenType;
use Walnut\Lang\Blueprint\Value\OpenValue as OpenValueInterface;
use Walnut\Lang\Blueprint\Value\Value;

final class OpenValue implements OpenValueInterface, JsonSerializable {

    public function __construct(
		private readonly TypeRegistry $typeRegistry,
		private readonly TypeNameIdentifier $typeName,
	    public readonly Value $value
    ) {}

	public OpenType $type {
        get => $this->typeRegistry->open($this->typeName);
    }

	public function equals(Value $other): bool {
		return $other instanceof OpenValueInterface &&
			$this->typeName->equals($other->type->name) &&
			$this->value->equals($other->value);
	}

	/** @throws AnalyserException */
	public function selfAnalyse(AnalyserContext $analyserContext): void {
		$type = $this->typeRegistry->open($this->typeName);
		// @codeCoverageIgnoreStart
		if (!$this->value->type->isSubtypeOf($type->valueType)) {
			throw new AnalyserException(
				sprintf(
					'The value of the open type %s should be a subtype of %s but got %s',
					$this->typeName,
					$type->valueType,
					$this->value->type,
				)
			);
		}
		// @codeCoverageIgnoreEnd
		$this->value->selfAnalyse($analyserContext);
	}

	public function __toString(): string {
		$sv = (string)$this->value;
		return sprintf(
			str_starts_with($sv, '[') ? "%s%s" : "%s{%s}",
			$this->typeName,
			$sv
		);
	}

	public function jsonSerialize(): array {
		return [
			'valueType' => 'Open',
			'typeName' => $this->typeName,
			'value' => $this->value
		];
	}
}