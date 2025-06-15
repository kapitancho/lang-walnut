<?php

namespace Walnut\Lang\Implementation\Value;

use JsonSerializable;
use Walnut\Lang\Blueprint\Code\Analyser\AnalyserContext;
use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Common\Identifier\TypeNameIdentifier;
use Walnut\Lang\Blueprint\Program\Registry\TypeRegistry;
use Walnut\Lang\Blueprint\Type\DataType;
use Walnut\Lang\Blueprint\Value\DataValue as DataValueInterface;
use Walnut\Lang\Blueprint\Value\Value;

final class DataValue implements DataValueInterface, JsonSerializable {

    public function __construct(
		private readonly TypeRegistry $typeRegistry,
		private readonly TypeNameIdentifier $typeName,
	    public readonly Value $value
    ) {}

	public DataType $type {
        get => $this->typeRegistry->data($this->typeName);
    }

	/** @throws AnalyserException */
	public function selfAnalyse(AnalyserContext $analyserContext): void {
		$type = $this->typeRegistry->data($this->typeName);
		if (!$this->value->type->isSubtypeOf($type->valueType)) {
			throw new AnalyserException(
				sprintf(
					'The value of the data type %s should be a subtype of %s but got %s',
					$this->typeName,
					$type->valueType,
					$this->value->type,
				)
			);
		}
		$this->value->selfAnalyse($analyserContext);
	}

	public function equals(Value $other): bool {
		return $other instanceof DataValueInterface &&
			$this->typeName->equals($other->type->name) &&
			$this->value->equals($other->value);
	}

	public function __toString(): string {
		$sv = (string)$this->value;
		return sprintf(
			"%s!%s",
			$this->typeName,
			$sv
		);
	}


	public function jsonSerialize(): array {
		return [
			'valueType' => 'Data',
			'typeName' => $this->typeName,
			'value' => $this->value
		];
	}
}