<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\DatabaseConnector;

use PDO;
use PDOException;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\ResultType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\SealedType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\RecordValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\SealedValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Identifier\TypeName;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\CommonBase\DatabaseConnectorPdoMethod;
use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;

final readonly class Query extends DatabaseConnectorPdoMethod {

	protected function getValidator(): callable {
		return fn(SealedType $targetType, Type $parameterType): ResultType =>
			$this->typeRegistry->result(
				$this->typeRegistry->typeByName(
					new TypeName('DatabaseQueryResult')
				),
				$this->typeRegistry->typeByName(
					new TypeName('DatabaseQueryFailure')
				)
			);
	}

	protected function getExecutor(): callable {
		return function(SealedValue $target, RecordValue $parameter): Value {
			if ($parameter->type->isSubtypeOf(
				$this->typeRegistry->typeByName(
					new TypeName('DatabaseQueryCommand')
				)
			)) {
				[$dsn, $query, $bound] = $this->getValues($target, $parameter);
				try {
					$stmt = $this->getPdo($dsn)->prepare($query);
					$stmt->execute($bound);
					$result = [];
					foreach($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
						/** @var array<string, string|float|int|null> $row */
						$result[] = $this->valueRegistry->record(
							array_map(
								fn(string|float|int|null $value): Value => match(gettype($value)) {
									'string' => $this->valueRegistry->string($value),
									'double' => $this->valueRegistry->real($value),
									'integer' => $this->valueRegistry->integer($value),
									'NULL' => $this->valueRegistry->null,
								},
								$row
							)
						);
					}

					return $this->valueRegistry->tuple($result);
				} catch (PDOException $ex) {
					return $this->valueRegistry->error(
						$this->valueRegistry->data(
							new TypeName('DatabaseQueryFailure'),
							$this->valueRegistry->record([
								'query' => $parameter->values['query'],
								'boundParameters' => $parameter->values['boundParameters'],
								'error' => $this->valueRegistry->string($ex->getMessage())
							])
						)
					);
				}
			}
			// @codeCoverageIgnoreStart
			throw new AnalyserException("Invalid parameter value");
			// @codeCoverageIgnoreEnd			
		};
	}
	
}