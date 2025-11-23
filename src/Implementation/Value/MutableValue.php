<?php

namespace Walnut\Lang\Implementation\Value;

use JsonSerializable;
use Walnut\Lang\Blueprint\Code\Analyser\AnalyserContext;
use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Program\Registry\TypeRegistry;
use Walnut\Lang\Blueprint\Type\MutableType as MutableTypeInterface;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Value\MutableValue as MutableValueInterface;
use Walnut\Lang\Blueprint\Value\Value;

final class MutableValue implements MutableValueInterface, JsonSerializable {

    public function __construct(
		private readonly TypeRegistry $typeRegistry,
		public readonly Type $targetType,
	    public Value $value
    ) {}

	public MutableTypeInterface $type {
        get => $this->typeRegistry->mutable($this->targetType);
    }

	/** @throws AnalyserException */
	public function selfAnalyse(AnalyserContext $analyserContext): void {
		if (!$this->value->type->isSubtypeOf($this->targetType)) {
			throw new AnalyserException(
				sprintf(
					'The value of the mutable type should be a subtype of %s but got %s',
					$this->targetType,
					$this->value->type,
				),
				$this
			);
		}
		$this->value->selfAnalyse($analyserContext);
	}

	public function equals(Value $other): bool {
		return $other instanceof MutableValueInterface &&
			$this->targetType->isSubtypeOf($other->targetType) &&
			$other->targetType->isSubtypeOf($this->targetType) &&
			$this->value->equals($other->value);
	}

	public function __toString(): string {
		return sprintf(
			"mutable{%s, %s}",
			$this->targetType,
			$this->value
		);
	}

	public function jsonSerialize(): array {
		return [
			'valueType' => 'Mutable',
			'targetType' => $this->targetType,
			'value' => $this->value
		];
	}
}