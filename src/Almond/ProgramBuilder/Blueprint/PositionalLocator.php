<?php

namespace Walnut\Lang\Almond\ProgramBuilder\Blueprint;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Expression\Expression;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Function\FunctionBody;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Method\Userland\UserlandMethod;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Identifier\EnumerationValueName;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Identifier\MethodName;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Identifier\TypeName;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Identifier\VariableName;

interface PositionalLocator {
	/**
	 * Returns all engine elements whose source range contains $offset
	 * (inclusive on both ends), ordered from narrowest range to widest.
	 *
	 * @return list<Expression|Type|Value|FunctionBody|UserlandMethod|TypeName|VariableName|MethodName|EnumerationValueName>
	 */
	public function elementsAtOffset(string $moduleName, int $offset): array;

	/**
	 * Returns the first element in $moduleName for which $predicate returns true,
	 * iterating in ascending start-offset order.
	 * $predicate receives ($element, int $startOffset, int $endOffset).
	 *
	 * @param callable(Expression|Type|Value|FunctionBody|UserlandMethod|TypeName|VariableName|MethodName|EnumerationValueName, int, int): bool $predicate
	 * @return Expression|Type|Value|FunctionBody|UserlandMethod|TypeName|VariableName|MethodName|EnumerationValueName|null
	 */
	public function findFirst(string $moduleName, callable $predicate): mixed;

	/**
	 * Returns the last element in $moduleName for which $predicate returns true,
	 * iterating in descending start-offset order (i.e. finds the closest-before match).
	 * $predicate receives ($element, int $startOffset, int $endOffset).
	 *
	 * @param callable(Expression|Type|Value|FunctionBody|UserlandMethod|TypeName|VariableName|MethodName|EnumerationValueName, int, int): bool $predicate
	 * @return Expression|Type|Value|FunctionBody|UserlandMethod|TypeName|VariableName|MethodName|EnumerationValueName|null
	 */
	public function findLast(string $moduleName, callable $predicate): mixed;

	/**
	 * Returns all indexed entries for $moduleName as [startOffset, endOffset, element] triples.
	 * Order is unspecified. Use this for full-module scans (folding ranges, inlay hints, etc.).
	 *
	 * @return list<array{0: int, 1: int, 2: Expression|Type|Value|FunctionBody|UserlandMethod|TypeName|VariableName|MethodName|EnumerationValueName}>
	 */
	public function allElements(string $moduleName): array;
}
