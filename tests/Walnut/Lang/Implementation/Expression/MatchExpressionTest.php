<?php

namespace Walnut\Lang\Test\Implementation\Expression;

use PHPUnit\Framework\TestCase;
use Walnut\Lang\Implementation\Code\Analyser\AnalyserContext;
use Walnut\Lang\Implementation\Code\Execution\ExecutionContext;
use Walnut\Lang\Implementation\Code\Expression\ConstantExpression;
use Walnut\Lang\Implementation\Code\Expression\MatchExpression;
use Walnut\Lang\Implementation\Code\Expression\MatchExpressionDefault;
use Walnut\Lang\Implementation\Code\Expression\MatchExpressionEquals;
use Walnut\Lang\Implementation\Code\Expression\MatchExpressionIsSubtypeOf;
use Walnut\Lang\Implementation\Code\Expression\MatchExpressionPair;
use Walnut\Lang\Implementation\Code\Scope\VariableScope;
use Walnut\Lang\Implementation\Code\Scope\VariableValueScope;
use Walnut\Lang\Implementation\Program\Builder\TypeRegistryBuilder;
use Walnut\Lang\Implementation\Program\Registry\ValueRegistry;
use Walnut\Lang\Test\EmptyDependencyContainer;

final class MatchExpressionTest extends TestCase {
	private readonly TypeRegistryBuilder $typeRegistry;
	private readonly ValueRegistry $valueRegistry;
	private readonly MatchExpression $matchExpression;

	protected function setUp(): void {
		parent::setUp();
		$this->typeRegistry = new TypeRegistryBuilder();
		$this->valueRegistry = new ValueRegistry($this->typeRegistry, new EmptyDependencyContainer);
		$this->matchExpression = new MatchExpression(
			$this->typeRegistry,
			$this->valueRegistry,
			new ConstantExpression(
				$this->typeRegistry,
				$this->valueRegistry->integer(123)
			),
			new MatchExpressionEquals(),
			[
				new MatchExpressionPair(
					new ConstantExpression(
						$this->typeRegistry,
						$this->valueRegistry->string("123")
					),
					new ConstantExpression(
						$this->typeRegistry,
						$this->valueRegistry->string("456")
					)
				),
				new MatchExpressionPair(
					new ConstantExpression(
						$this->typeRegistry,
						$this->valueRegistry->integer(123)
					),
					new ConstantExpression(
						$this->typeRegistry,
						$this->valueRegistry->true()
					)
				),
			]
		);
	}

	public function testTarget(): void {
		self::assertInstanceOf(
			ConstantExpression::class,
			$this->matchExpression->target()
		);
	}

	public function testPairs(): void {
		self::assertCount(2, $this->matchExpression->pairs());
	}

	public function testOperation(): void {
		self::assertInstanceOf(
			MatchExpressionEquals::class,
			$this->matchExpression->operation()
		);
	}

	public function testAnalyse(): void {
		$result = $this->matchExpression->analyse(new AnalyserContext(new VariableScope([])));
		self::assertTrue($result->expressionType()->isSubtypeOf(
			$this->typeRegistry->union([
				$this->typeRegistry->string(),
				$this->typeRegistry->true()
			]),
		));
	}

	public function testExecute(): void {
		$result = $this->matchExpression->execute(new ExecutionContext(new VariableValueScope([])));
		self::assertTrue(
			$this->valueRegistry->true()->equals($result->value())
		);
	}

	public function testIsSubtypeOf(): void {
		$result = (new MatchExpression(
			$this->typeRegistry,
			$this->valueRegistry,
			new ConstantExpression(
				$this->typeRegistry,
				$this->valueRegistry->integer(123)
			),
			new MatchExpressionIsSubtypeOf(),
			[
				new MatchExpressionPair(
					new ConstantExpression(
						$this->typeRegistry,
						$this->valueRegistry->type(
							$this->typeRegistry->string()
						)
					),
					new ConstantExpression(
						$this->typeRegistry,
						$this->valueRegistry->string("456")
					)
				),
				new MatchExpressionPair(
					new ConstantExpression(
						$this->typeRegistry,
						$this->valueRegistry->type(
							$this->typeRegistry->integer()
						)
					),
					new ConstantExpression(
						$this->typeRegistry,
						$this->valueRegistry->true()
					)
				),
			]
		))->execute(new ExecutionContext(new VariableValueScope([])));
		self::assertTrue($result->value()->equals(
			$this->valueRegistry->true()
		));
	}

	public function testDefaultMatch(): void {
		$result = (new MatchExpression(
			$this->typeRegistry,
			$this->valueRegistry,
			new ConstantExpression(
				$this->typeRegistry,
				$this->valueRegistry->integer(123)
			),
			new MatchExpressionEquals(),
			[
				new MatchExpressionPair(
					new ConstantExpression(
						$this->typeRegistry,
						$this->valueRegistry->string("123")
					),
					new ConstantExpression(
						$this->typeRegistry,
						$this->valueRegistry->string("456")
					)
				),
				new MatchExpressionDefault(
					new ConstantExpression(
						$this->typeRegistry,
						$this->valueRegistry->true()
					)
				),
			],
		))->execute(new ExecutionContext(new VariableValueScope([])));
		self::assertTrue($result->value()->equals(
			$this->valueRegistry->true()
		));
	}

	public function testNoMatch(): void {
		$result = (new MatchExpression(
			$this->typeRegistry,
			$this->valueRegistry,
			new ConstantExpression(
				$this->typeRegistry,
				$this->valueRegistry->integer(123)
			),
			new MatchExpressionEquals(),
			[
				new MatchExpressionPair(
					new ConstantExpression(
						$this->typeRegistry,
						$this->valueRegistry->string("123")
					),
					new ConstantExpression(
						$this->typeRegistry,
						$this->valueRegistry->string("456")
					)
				),
				new MatchExpressionPair(
					new ConstantExpression(
						$this->typeRegistry,
						$this->valueRegistry->integer(456)
					),
					new ConstantExpression(
						$this->typeRegistry,
						$this->valueRegistry->true()
					)
				),
			],
		))->execute(new ExecutionContext(new VariableValueScope([])));
		self::assertTrue($result->value()->equals(
			$this->valueRegistry->null()
		));
	}

}