<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\DatabaseConnector;

use BcMath\Number;
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

final readonly class Execute extends DatabaseConnectorPdoMethod {

	protected function getValidator(): callable {
		return fn(SealedType $targetType, Type $parameterType): ResultType =>
			$this->typeRegistry->result(
				$this->typeRegistry->integer(0),
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
				$dsn = $target->value->valueOf('connection')
					->value->values['dsn']->literalValue;
				try {
					$stmt = $this->getPdo($dsn)->prepare($parameter->values['query']->literalValue);
					$stmt->execute(array_map(fn(Value $value): string|float|int|null =>
					($v = $value)->literalValue instanceof Number ? (string)$v->literalValue : $v->literalValue,
						$parameter->values['boundParameters']->values
					));
					$rowCount = $stmt->rowCount();

					return $this->valueRegistry->integer($rowCount);
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