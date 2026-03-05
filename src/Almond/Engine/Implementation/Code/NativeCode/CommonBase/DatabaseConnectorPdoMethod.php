<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\CommonBase;

use PDO;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Identifier\TypeName;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\NamedNativeMethod;

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