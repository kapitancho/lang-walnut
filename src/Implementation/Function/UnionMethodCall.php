<?php

namespace Walnut\Lang\Implementation\Function;

use Walnut\Lang\Blueprint\Code\Execution\ExecutionException;
use Walnut\Lang\Blueprint\Code\Scope\TypedValue;
use Walnut\Lang\Blueprint\Function\Method;
use Walnut\Lang\Blueprint\Function\MethodExecutionContext;
use Walnut\Lang\Blueprint\Type\Type;

final readonly class UnionMethodCall implements Method {

	/**
	 * @param MethodExecutionContext $context
	 * @param list<array{Type, Method}> $methods
	 */
	public function __construct(
		private MethodExecutionContext $context,
		private array $methods
	) {}

	public function analyse(
		Type $targetType,
		Type $parameterType
	): Type {
		return $this->context->typeRegistry->union(
			array_map(
				static fn(array $method): Type => $method[1]->analyse(
					$method[0], $parameterType
				),
				$this->methods
			)
		);
	}

	public function execute(
		TypedValue $target,
		TypedValue $parameter,
	): TypedValue {
		foreach($this->methods as [$methodType, $method]) {
			 if ($target->type->isSubtypeOf($methodType)) {
				 return $method->execute($target, $parameter);
			 }
		}
		echo $target->type, '/', $target->value, '|', $parameter->type, '/', $parameter->value, PHP_EOL;
		var_dump(array_map(fn(array $a) => (string)$a[0], $this->methods));die;
		throw new ExecutionException("Union method call is not executable");
	}

}