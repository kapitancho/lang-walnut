<?php

namespace Walnut\Lang\Test\Implementation\Expression;

use PHPUnit\Framework\TestCase;
use Walnut\Lang\Blueprint\Code\Execution\FunctionReturn;
use Walnut\Lang\Implementation\Code\Analyser\AnalyserContext;
use Walnut\Lang\Implementation\Code\Execution\ExecutionContext;
use Walnut\Lang\Implementation\Code\Expression\ConstantExpression;
use Walnut\Lang\Implementation\Code\Expression\ReturnExpression;
use Walnut\Lang\Implementation\Code\Scope\VariableScope;
use Walnut\Lang\Implementation\Code\Scope\VariableValueScope;
use Walnut\Lang\Implementation\Program\Builder\TypeRegistryBuilder;
use Walnut\Lang\Implementation\Program\Registry\ValueRegistry;
use Walnut\Lang\Test\EmptyDependencyContainer;

final class ReturnExpressionTest extends TestCase {
	private readonly TypeRegistryBuilder $typeRegistry;
	private readonly ValueRegistry $valueRegistry;
	private readonly ReturnExpression $returnExpression;

	protected function setUp(): void {
		parent::setUp();
		$this->typeRegistry = new TypeRegistryBuilder();
		$this->valueRegistry = new ValueRegistry($this->typeRegistry, new EmptyDependencyContainer);
		$this->returnExpression = new ReturnExpression(
			$this->typeRegistry,
			new ConstantExpression(
				$this->typeRegistry,
				$this->valueRegistry->integer(123)
			),
		);
	}

	public function testReturnedExpression(): void {
		self::assertInstanceOf(ConstantExpression::class,
			$this->returnExpression->returnedExpression());
	}

	public function testAnalyse(): void {
		$result = $this->returnExpression->analyse(new AnalyserContext(new VariableScope([])));
		self::assertTrue($result->returnType()->isSubtypeOf(
			$this->typeRegistry->integer()
		));
	}

	public function testExecute(): void {
		$this->expectException(FunctionReturn::class);
		$this->returnExpression->execute(new ExecutionContext(new VariableValueScope([])));
	}

	public function testExecuteResult(): void {
		try {
			$this->returnExpression->execute(new ExecutionContext(new VariableValueScope([])));
		} catch (FunctionReturn $e) {
			self::assertEquals(
				$this->valueRegistry->integer(123),
				$e->value
			);
			return;
		}
	}

}