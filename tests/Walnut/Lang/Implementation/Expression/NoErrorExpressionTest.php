<?php

namespace Walnut\Lang\Implementation\Expression;

use PHPUnit\Framework\TestCase;
use Walnut\Lang\Blueprint\Code\Execution\FunctionReturn;
use Walnut\Lang\Implementation\Code\Analyser\AnalyserContext;
use Walnut\Lang\Implementation\Code\Execution\ExecutionContext;
use Walnut\Lang\Implementation\Code\Expression\ConstantExpression;
use Walnut\Lang\Implementation\Code\Expression\NoErrorExpression;
use Walnut\Lang\Implementation\Code\Scope\VariableScope;
use Walnut\Lang\Implementation\Code\Scope\VariableValueScope;
use Walnut\Lang\Implementation\Program\Builder\TypeRegistryBuilder;
use Walnut\Lang\Implementation\Program\Registry\ValueRegistry;
use Walnut\Lang\Test\EmptyDependencyContainer;

final class NoErrorExpressionTest extends TestCase {
	private readonly TypeRegistryBuilder $typeRegistry;
	private readonly ValueRegistry $valueRegistry;
	private readonly NoErrorExpression $noErrorExpression;
	private readonly NoErrorExpression $errorExpression;

	protected function setUp(): void {
		parent::setUp();
		$this->typeRegistry = new TypeRegistryBuilder();
		$this->valueRegistry = new ValueRegistry($this->typeRegistry, new EmptyDependencyContainer);
		$this->noErrorExpression = new NoErrorExpression(
			$this->typeRegistry,
			new ConstantExpression(
				$this->typeRegistry,
				$this->valueRegistry->integer(123)
			),
		);
		$this->errorExpression = new NoErrorExpression(
			$this->typeRegistry,
			new ConstantExpression(
				$this->typeRegistry,
				$this->valueRegistry->error(
					$this->valueRegistry->integer(123)
				)
			),
		);
	}

	public function testReturnedExpression(): void {
		self::assertInstanceOf(ConstantExpression::class,
			$this->noErrorExpression->targetExpression());
	}

	public function testAnalyse(): void {
		$result = $this->noErrorExpression->analyse(new AnalyserContext(new VariableScope([])));
		self::assertTrue($result->returnType()->isSubtypeOf(
			$this->typeRegistry->integer()
		));
	}

	public function testExecute(): void {
		$this->expectNotToPerformAssertions();
		$this->noErrorExpression->execute(new ExecutionContext(new VariableValueScope([])));
	}

	public function testExecuteOnError(): void {
		$this->expectException(FunctionReturn::class);
		$this->errorExpression->execute(new ExecutionContext(new VariableValueScope([])));
	}

	public function testExecuteResult(): void {
		try {
			$this->errorExpression->execute(new ExecutionContext(new VariableValueScope([])));
		} catch (FunctionReturn $e) {
			self::assertEquals(
				$this->valueRegistry->error(
					$this->valueRegistry->integer(123)
				),
				$e->value
			);
			return;
		}
	}

}