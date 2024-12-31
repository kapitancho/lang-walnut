<?php

namespace Walnut\Lang\Test\Implementation\Expression;

use PHPUnit\Framework\TestCase;
use Walnut\Lang\Implementation\Code\Analyser\AnalyserContext;
use Walnut\Lang\Implementation\Code\Execution\ExecutionContext;
use Walnut\Lang\Implementation\Code\Expression\ConstantExpression;
use Walnut\Lang\Implementation\Code\Expression\TupleExpression;
use Walnut\Lang\Implementation\Code\Scope\VariableScope;
use Walnut\Lang\Implementation\Code\Scope\VariableValueScope;
use Walnut\Lang\Implementation\Program\Builder\TypeRegistryBuilder;
use Walnut\Lang\Implementation\Program\Registry\ValueRegistry;
use Walnut\Lang\Test\EmptyDependencyContainer;

final class TupleExpressionTest extends TestCase {
	private readonly TypeRegistryBuilder $typeRegistry;
	private readonly ValueRegistry $valueRegistry;
	private readonly TupleExpression $tupleExpression;

	protected function setUp(): void {
		parent::setUp();
		$this->typeRegistry = new TypeRegistryBuilder();
		$this->valueRegistry = new ValueRegistry($this->typeRegistry, new EmptyDependencyContainer);
		$this->tupleExpression = new TupleExpression(
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
		self::assertCount(2, $this->tupleExpression->values);
	}

	public function testAnalyse(): void {
		$result = $this->tupleExpression->analyse(new AnalyserContext(VariableScope::empty()));
		self::assertTrue($result->expressionType->isSubtypeOf(
			$this->typeRegistry->tuple([
				$this->typeRegistry->integer(),
				$this->typeRegistry->string()
			])
		));
	}

	public function testExecute(): void {
		$result = $this->tupleExpression->execute(new ExecutionContext(new VariableValueScope([])));
		self::assertEquals(
			$this->valueRegistry->tuple([
				$this->valueRegistry->integer(123),
				$this->valueRegistry->string("456")
			]),
			$result->value
		);
	}

}