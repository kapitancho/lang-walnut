<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\CommonBase;

use PDO;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\SealedType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\IntegerValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\RealValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\RecordValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\SealedValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\StringValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Identifier\TypeName;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\NamedNativeMethod;

/** @extends NamedNativeMethod<SealedType, Type, SealedValue, Value> */
abstract readonly class DatabaseConnectorPdoMethod extends NamedNativeMethod {

	protected function getExpectedTypeName(): TypeName {
		return new TypeName('DatabaseConnector');
	}

	protected function validateParameterType(Type $parameterType, Type $targetType): null|string {
		return $parameterType->isSubtypeOf(
			$this->typeRegistry->typeByName(
				new TypeName('DatabaseQueryCommand')
			)
		) ? null : sprintf(
			"Expected a parameter of type %s, got %s",
			'DatabaseQueryCommand',
			$parameterType
		);
	}

	protected function getPdo(string $dsn): PDO {
		/** @var array<string, PDO> $pdoInstances */
		static $pdoInstances = [];
		if (!isset($pdoInstances[$dsn])) {
			$pdoInstances[$dsn] = new PDO($dsn, options: [
				PDO::ATTR_EMULATE_PREPARES => false,
			]);
			$pdoInstances[$dsn]->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		}
		return $pdoInstances[$dsn];
	}

	/** @return array{string, string, list<string|null>|array<string, string|null>} */
	protected function getValues(SealedValue $target, RecordValue $parameter): array {
		/** @var string $dsn */
		/** @phpstan-ignore-next-line */
		$dsn = $target->value->valueOf('connection')->value->values['dsn']->literalValue;
		/** @var string $query */
		/** @phpstan-ignore-next-line */
		$query = $parameter->values['query']->literalValue;
		/** @phpstan-ignore-next-line */
		/** @var list<Value>|array<string, Value> $boundParameters */
		$boundParameters = $parameter->values['boundParameters']->values;

		$bound = array_map(fn(Value $value): string|null =>
			match(true) {
				$value instanceof RealValue, $value instanceof IntegerValue => (string)$value->literalValue,
				$value instanceof StringValue => $value->literalValue,
				default => null
			},
			$boundParameters
		);

		return [$dsn, $query, $bound];
	}

}