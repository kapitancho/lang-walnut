<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Code\Value\BuiltIn;

use JsonSerializable;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\SetType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Error\InvalidArgument;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\TypeRegistry;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\SetValue as SetValueInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Engine\Blueprint\Feature\DependencyContainer\DependencyContext;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationRequest;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationResult;

final class SetValue implements SetValueInterface, JsonSerializable {

	/** @var list<Value> */
	public readonly array $values;
	/** @var array<string, Value> */
	public readonly array $valueSet;

	/**
	 * @param TypeRegistry $typeRegistry
	 * @param list<Value> $values
	 * @throws InvalidArgument
	 */
    public function __construct(
        private readonly TypeRegistry $typeRegistry,
	    array $values
    ) {
		$set = [];
		foreach($values as $value) {
			/** @phpstan-ignore-next-line instanceof.alwaysTrue */
			if (!$value instanceof Value) {
				InvalidArgument::of(
					Value::class,
					$value,
					'TupleValue must be constructed with a list of Value instances'
				);
			}
			$set[(string)$value] = $value;
		}
		$this->valueSet = $set;
		$this->values = array_values($set);
    }

	public SetType $type {
		get => $this->type ??= $this->typeRegistry->set(
			$this->typeRegistry->union(
				array_map(
					static fn(Value $value): Type =>
						$value->type, $this->values
				)
			),
			count($this->values),
			count($this->values)
        );
    }

	public function validate(ValidationRequest $request): ValidationResult {
		$request = $request->ok();
		foreach ($this->values as $value) {
			$request = $value->validate($request);
		}
		return $request;
	}

	public function validateDependencies(DependencyContext $dependencyContext): DependencyContext {
		foreach ($this->values as $value) {
			$dependencyContext = $value->validateDependencies($dependencyContext);
		}
		return $dependencyContext;
	}

	public function equals(Value $other): bool {
		if ($other instanceof SetValueInterface) {
			$thisValues = $this->valueSet;
			$otherValues = $other->valueSet;
			if (count($thisValues) === count($otherValues)) {
				return array_all(array_keys($thisValues),
					fn(string $key) => array_key_exists($key, $otherValues));
			}
		}
		return false;
	}

	public function asString(bool $multiline): string {
		return match(count($this->values)) {
			0 => '[;]',
			1 => sprintf(
				"[%s;]",
				$this->values[0]
			),
			default => sprintf(
				$multiline ? "[\n\t%s\n]" : "[%s]",
				implode(
					$multiline ? ';' . "\n" . "\t" : '; ',
					array_map(
						static fn(Value $value): string => $multiline ?
							str_replace("\n", "\n" . "\t", $value) : $value,
	                        $this->values
					)
				)
			)
		};
	}

	public function __toString(): string {
		$result = $this->asString(false);
		return mb_strlen($result) > 40 ? $this->asString(true) : $result;
	}

	public function jsonSerialize(): array {
		return [
			'valueType' => 'Set',
			'value' => $this->values
		];
	}

}