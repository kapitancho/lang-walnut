<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\CommonBase;

use PDO;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\SealedType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\SealedValue;
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

}