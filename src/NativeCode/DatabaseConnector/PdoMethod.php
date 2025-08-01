<?php

namespace Walnut\Lang\NativeCode\DatabaseConnector;

use PDO;

abstract readonly class PdoMethod {

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