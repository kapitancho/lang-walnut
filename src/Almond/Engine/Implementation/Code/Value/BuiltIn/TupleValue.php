<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Code\Value\BuiltIn;

use JsonSerializable;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\TupleType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Error\InvalidArgument;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\TypeRegistry;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\TupleValue as TupleValueInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Engine\Blueprint\Feature\DependencyContainer\DependencyContext;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationRequest;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationResult;
use Walnut\Lang\Almond\Engine\Implementation\Code\Type\UnknownProperty;

final class TupleValue implements TupleValueInterface, JsonSerializable {

	/**
	 * @param TypeRegistry $typeRegistry
	 * @param list<Value> $values
	 * @throws InvalidArgument
	 */
    public function __construct(
        private readonly TypeRegistry $typeRegistry,
	    public readonly array $values
    ) {
		foreach($this->values as $value) {
			/** @phpstan-ignore-next-line instanceof.alwaysTrue */
			if (!$value instanceof Value) {
				InvalidArgument::of(
					Value::class,
					$value,
					'TupleValue must be constructed with a list of Value instances'
				);
			}
		}
    }

	public TupleType $type {
		get => $this->type ??= $this->typeRegistry->tuple(
			array_map(static fn(Value $value): Type => $value->type, $this->values),
	        $this->typeRegistry->nothing
        );
    }

	/** @throws UnknownProperty */
	public function valueOf(int $index): Value|UnknownProperty {
		return $this->values[$index] ?? new UnknownProperty($index, $this);
	}

	public function equals(Value $other): bool {
		if ($other instanceof TupleValueInterface) {
			$thisValues = $this->values;
			$otherValues = $other->values;
			if (count($thisValues) === count($otherValues)) {
				return array_all($thisValues, fn($value, $index) => $value->equals($otherValues[$index]));
			}
		}
		return false;
	}

	public function validate(ValidationRequest $request): ValidationResult {
		$request = $request->ok();
		foreach ($this->values as $value) {
			$request = $value->validate($request);
		}
		return $request;
	}

	public function asString(bool $multiline): string {
		if (count($this->values) === 0) {
			return '[]';
		}
		return sprintf(
			$multiline ? "[\n\t%s\n]" : "[%s]",
			implode(
				$multiline ? ',' . "\n" . "\t" : ', ',
				array_map(
					static fn(Value $value): string => $multiline ?
						str_replace("\n", "\n" . "\t", $value) : $value,
                        $this->values
				)
			)
		);
	}

	public function validateDependencies(DependencyContext $dependencyContext): DependencyContext {
		foreach ($this->values as $value) {
			$dependencyContext = $value->validateDependencies($dependencyContext);
		}
		return $dependencyContext;
	}

	public function __toString(): string {
		$result = $this->asString(false);
		return mb_strlen($result) > 40 ? $this->asString(true) : $result;
	}

	public function jsonSerialize(): array {
		return [
			'valueType' => 'Tuple',
			'value' => $this->values
		];
	}
}