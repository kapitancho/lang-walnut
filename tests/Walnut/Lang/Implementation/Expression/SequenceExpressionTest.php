<?php

namespace Walnut\Lang\Test\Implementation\Expression;

use PHPUnit\Framework\TestCase;
use Walnut\Lang\Implementation\Code\Analyser\AnalyserContext;
use Walnut\Lang\Implementation\Code\Execution\ExecutionContext;
use Walnut\Lang\Implementation\Code\Expression\ConstantExpression;
use Walnut\Lang\Implementation\Code\Expression\ReturnExpression;
use Walnut\Lang\Implementation\Code\Expression\SequenceExpression;
use Walnut\Lang\Implementation\Code\Scope\VariableScope;
use Walnut\Lang\Implementation\Code\Scope\VariableValueScope;
use Walnut\Lang\Implementation\Program\Builder\TypeRegistryBuilder;
use Walnut\Lang\Implementation\Program\Registry\ValueRegistry;
use Walnut\Lang\Test\EmptyDependencyContainer;

final class SequenceExpressionTest extends TestCase {
	private readonly TypeRegistryBuilder $typeRegistry;
	private readonly ValueRegistry $valueRegistry;
	private readonly SequenceExpression $sequenceExpression;

	protected function setUp(): void {
		parent::setUp();
		$this->typeRegistry = new TypeRegistryBuilder();
		$this->valueRegistry = new ValueRegistry($this->typeRegistry, new EmptyDependencyContainer);
		$this->sequenceExpression = new SequenceExpression(
			$this->typeRegistry,
			$this->valueRegistry,
			[
				new ConstantExpression(
					$this->typeRegistry,
					$this->valueRegistry->integer(123)
				),
				new ConstantExpression(
					$this->typeRegistry,
					$this->valueRegistry->string("456")
				)
			]
		);
	}

	public function testValues(): void {
		self::assertCount(2, $this->sequenceExpression->expressions());
	}

	public function testAnalyse(): void {
		$result = $this->sequenceExpression->analyse(new AnalyserContext(new VariableScope([])));
		self::assertTrue($result->expressionType()->isSubtypeOf(
			$this->typeRegistry->string()
		));
	}

	public function testExecute(): void {
		$result = $this->sequenceExpression->execute(
			new ExecutionContext(new VariableValueScope([]))
		);
		self::assertEquals(
			$this->valueRegistry->string("456"),
			$result->value()
		);
	}

	public function testAnalyseWithReturn(): void {
		$result = (new SequenceExpression(
			$this->typeRegistry,
			$this->valueRegistry,
			[
				new ReturnExpression(
					$this->typeRegistry,
					new ConstantExpression(
						$this->typeRegistry,
						$this->valueRegistry->integer(123)
					)
				),
				new ConstantExpression(
					$this->typeRegistry,
					$this->valueRegistry->string("456")
				)
			]
		))->analyse(new AnalyserContext(new VariableScope([])));
		self::assertTrue($result->expressionType()->isSubtypeOf(
			$this->typeRegistry->integer()
		));
	}

}