<?php

namespace Walnut\Lang\Implementation\Compilation;

use Walnut\Lang\Blueprint\Code\Expression\Expression;
use Walnut\Lang\Blueprint\Code\Expression\MatchExpression;
use Walnut\Lang\Blueprint\Code\Expression\MatchExpressionDefault as MatchExpressionDefaultInterface;
use Walnut\Lang\Blueprint\Code\Expression\MatchExpressionPair as MatchExpressionPairInterface;
use Walnut\Lang\Blueprint\Code\Expression\MethodCallExpression;
use Walnut\Lang\Blueprint\Common\Identifier\MethodNameIdentifier;
use Walnut\Lang\Blueprint\Common\Identifier\TypeNameIdentifier;
use Walnut\Lang\Blueprint\Compilation\CodeBuilder as MyCodeBuilderInterface;
use Walnut\Lang\Blueprint\Program\Registry\ExpressionRegistry;
use Walnut\Lang\Blueprint\Program\Registry\TypeRegistry;
use Walnut\Lang\Blueprint\Program\Registry\ValueRegistry;
use Walnut\Lang\Implementation\Code\Expression\MatchExpressionDefault;
use Walnut\Lang\Implementation\Code\Expression\MatchExpressionEquals;
use Walnut\Lang\Implementation\Code\Expression\MatchExpressionIsSubtypeOf;
use Walnut\Lang\Implementation\Code\Expression\MatchExpressionPair;

final readonly class CodeBuilder implements MyCodeBuilderInterface {

	public function __construct(
		public TypeRegistry $typeRegistry,
		public ValueRegistry $valueRegistry,
		private ExpressionRegistry $expressionRegistry
	) {}

	/** @param list<MatchExpressionPairInterface|MatchExpressionDefaultInterface> $pairs */
	public function matchTrue(array $pairs): MatchExpression {
		return $this->expressionRegistry->match(
			$this->expressionRegistry->constant($this->valueRegistry->true),
			new MatchExpressionEquals, array_map(
				fn(MatchExpressionPairInterface|MatchExpressionDefaultInterface $pair): MatchExpressionPairInterface|MatchExpressionDefaultInterface => match(true) {
					$pair instanceof MatchExpressionPairInterface => new MatchExpressionPair(
						$this->expressionRegistry->methodCall(
							$pair->matchExpression,
							new MethodNameIdentifier('asBoolean'),
							$this->expressionRegistry->constant(
								$this->valueRegistry->null
							)
						),
						$pair->valueExpression
					),
					$pair instanceof MatchExpressionDefaultInterface => $pair
				},
				$pairs
			)
		);
	}

	/** @param list<MatchExpressionPairInterface|MatchExpressionDefaultInterface> $pairs */
	public function matchType(Expression $condition, array $pairs): MatchExpression {
		return $this->expressionRegistry->match($condition, new MatchExpressionIsSubtypeOf, $pairs);
	}

	/** @param list<MatchExpressionPairInterface|MatchExpressionDefaultInterface> $pairs */
	public function matchValue(Expression $condition, array $pairs): MatchExpression {
		return $this->expressionRegistry->match($condition, new MatchExpressionEquals, $pairs);
	}

	public function matchIf(Expression $condition, Expression $then, Expression $else): MatchExpression {
		return $this->expressionRegistry->match(
			$this->expressionRegistry->methodCall(
				$condition,
				new MethodNameIdentifier('asBoolean'),
				$this->expressionRegistry->constant(
					$this->valueRegistry->null
				)
			),
			new MatchExpressionEquals, [
			new MatchExpressionPair(
				$this->expressionRegistry->constant($this->valueRegistry->true),
				$then
			),
			new MatchExpressionDefault($else)
		]);
	}

	public function functionCall(Expression $target, Expression $parameter): MethodCallExpression {
		return $this->expressionRegistry->methodCall(
			$target,
			new MethodNameIdentifier('invoke'),
			$parameter
		);
	}

	public function constructorCall(TypeNameIdentifier $typeName, Expression $parameter): MethodCallExpression {
		return $this->expressionRegistry->methodCall(
			$parameter,
			new MethodNameIdentifier('construct'),
			$this->expressionRegistry->constant(
				$this->valueRegistry->type(
					$this->typeRegistry->typeByName($typeName)
				)
			)
		);
	}

	public function propertyAccess(Expression $target, int|string $propertyName): MethodCallExpression {
		return $this->expressionRegistry->methodCall(
			$target,
			new MethodNameIdentifier('item'),
			$this->expressionRegistry->constant(
				is_int($propertyName) ?
					$this->valueRegistry->integer($propertyName) :
					$this->valueRegistry->string($propertyName)
			)
		);
	}
}