<?php

namespace Walnut\Lang\Blueprint\Compilation;

use Walnut\Lang\Blueprint\Code\Expression\Expression;
use Walnut\Lang\Blueprint\Code\Expression\MatchExpression;
use Walnut\Lang\Blueprint\Code\Expression\MatchExpressionDefault;
use Walnut\Lang\Blueprint\Code\Expression\MatchExpressionPair;
use Walnut\Lang\Blueprint\Code\Expression\MethodCallExpression;
use Walnut\Lang\Blueprint\Common\Identifier\TypeNameIdentifier;

interface MyCodeBuilder {
	/** @param list<MatchExpressionPair|MatchExpressionDefault> $pairs */
	public function matchTrue(array $pairs): MatchExpression;
	/** @param list<MatchExpressionPair|MatchExpressionDefault> $pairs */
	public function matchType(Expression $condition, array $pairs): MatchExpression;
	/** @param list<MatchExpressionPair|MatchExpressionDefault> $pairs */
	public function matchValue(Expression $condition, array $pairs): MatchExpression;
	public function matchIf(Expression $condition, Expression $then, Expression $else): MatchExpression;

	public function functionCall(Expression $target, Expression $parameter): MethodCallExpression;
	public function constructorCall(
		TypeNameIdentifier $typeName,
		Expression $parameter
	): MethodCallExpression;
	public function propertyAccess(Expression $target, int|string $propertyName): MethodCallExpression;
}