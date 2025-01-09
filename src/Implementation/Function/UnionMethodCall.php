<?php

namespace Walnut\Lang\Implementation\Function;

use Walnut\Lang\Blueprint\Code\Execution\ExecutionException;
use Walnut\Lang\Blueprint\Code\Scope\TypedValue;
use Walnut\Lang\Blueprint\Function\Method;
use Walnut\Lang\Blueprint\Program\Registry\ProgramRegistry;
use Walnut\Lang\Blueprint\Type\Type;

final readonly class UnionMethodCall implements Method {

	/**
	 * @param list<array{Type, Method}> $methods
	 */
	public function __construct(
		private array $methods
	) {}

	public function analyse(
		ProgramRegistry $programRegistry,
		Type $targetType,
		Type $parameterType
	): Type {
		return $programRegistry->typeRegistry->union(
			array_map(
				static fn(array $method): Type => $method[1]->analyse(
					$programRegistry, $method[0], $parameterType
				),
				$this->methods
			)
		);
	}

	public function execute(
		ProgramRegistry $programRegistry,
		TypedValue $target,
		TypedValue $parameter,
	): TypedValue {
		foreach($this->methods as [$methodType, $method]) {
			 if ($target->type->isSubtypeOf($methodType)) {
				 return $method->execute($target, $parameter);
			 }
		}
		throw new ExecutionException("Union method call is not executable");
	}

}