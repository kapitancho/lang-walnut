<?php

namespace Walnut\Lang\Implementation\Value;

use JsonSerializable;
use Walnut\Lang\Blueprint\Code\Analyser\AnalyserContext;
use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Common\Identifier\TypeNameIdentifier;
use Walnut\Lang\Blueprint\Program\Registry\TypeRegistry;
use Walnut\Lang\Blueprint\Type\SealedType;
use Walnut\Lang\Blueprint\Value\SealedValue as SealedValueInterface;
use Walnut\Lang\Blueprint\Value\Value;

final class SealedValue implements SealedValueInterface, JsonSerializable {

    public function __construct(
		private readonly TypeRegistry $typeRegistry,
		private readonly TypeNameIdentifier $typeName,
	    public readonly Value $value
    ) {}

	public SealedType $type {
        get => $this->typeRegistry->sealed($this->typeName);
    }

	public function equals(Value $other): bool {
		return $other instanceof SealedValueInterface &&
			$this->typeName->equals($other->type->name) &&
			$this->value->equals($other->value);
	}


	/** @throws AnalyserException */
	public function selfAnalyse(AnalyserContext $analyserContext): void {
		$type = $this->typeRegistry->sealed($this->typeName);
		// @codeCoverageIgnoreStart
		if (!$this->value->type->isSubtypeOf($type->valueType)) {
			throw new AnalyserException(
				sprintf(
					'The value of the sealed type %s should be a subtype of %s but got %s',
					$this->typeName,
					$type->valueType,
					$this->value->type,
				),
				$this
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
			'valueType' => 'Sealed',
			'typeName' => $this->typeName,
			'value' => $this->value
		];
	}
}