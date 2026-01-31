<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Code\Value\BuiltIn;

use JsonSerializable;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\RecordType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Error\InvalidArgument;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\TypeRegistry;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\RecordValue as RecordValueInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Engine\Blueprint\Feature\DependencyContainer\DependencyContext;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationRequest;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationResult;
use Walnut\Lang\Almond\Engine\Implementation\Code\Type\UnknownProperty;

final class RecordValue implements RecordValueInterface, JsonSerializable {

	/**
	 * @param TypeRegistry $typeRegistry
	 * @param array<string, Value> $values
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
					'RecordValue must be constructed with a list of Value instances'
				);
			}
		}
    }

	public RecordType $type {
		get => $this->type ??= $this->typeRegistry->record(
			array_map(static fn(Value $value): Type => $value->type, $this->values),
	        $this->typeRegistry->nothing
        );
    }

	/** @throws UnknownProperty */
	public function valueOf(string $key): Value|UnknownProperty {
		return $this->values[$key] ?? new UnknownProperty($key, $this);
	}

	public function equals(Value $other): bool {
		if ($other instanceof RecordValueInterface) {
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

	public function validateDependencies(DependencyContext $dependencyContext): DependencyContext {
		foreach ($this->values as $value) {
			$dependencyContext = $value->validateDependencies($dependencyContext);
		}
		return $dependencyContext;
	}

	public function asString(bool $multiline): string {
		if (count($this->values) === 0) {
			return '[:]';
		}
		return sprintf(
			$multiline ? "[\n\t%s\n]" : "[%s]",
			implode(
				$multiline ? ',' . "\n" . "\t" : ', ',
				array_map(
					static fn(string $key, Value $value): string =>
						/** @phpstan-ignore-next-line booleanAnd.leftAlwaysTrue */
					($s = sprintf("%s: %s", $key, $value)) && $multiline ?
						str_replace("\n", "\n" . "\t", $s) : $s,
					array_keys($this->values), $this->values
				)
			)
		);
	}

	public function __toString(): string {
		$result = $this->asString(false);
		return mb_strlen($result) > 40 ? $this->asString(true) : $result;
	}

	public function jsonSerialize(): array {
		return [
			'valueType' => 'Record',
			'value' => $this->values
		];
	}
}