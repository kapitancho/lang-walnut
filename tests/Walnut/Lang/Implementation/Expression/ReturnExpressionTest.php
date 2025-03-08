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
use Walnut\Lang\Implementation\Program\ProgramContextFactory;
use Walnut\Lang\Implementation\Program\Registry\ProgramRegistry;
use Walnut\Lang\Implementation\Program\Registry\ValueRegistry;

final class ReturnExpressionTest extends TestCase {
	private readonly TypeRegistryBuilder $typeRegistry;
	private readonly ValueRegistry $valueRegistry;
	private readonly ProgramRegistry $programRegistry;
	private readonly ReturnExpression $returnExpression;

	protected function setUp(): void {
		parent::setUp();
		$this->programRegistry = new ProgramContextFactory()->programContext->programRegistry;
		$this->typeRegistry = $this->programRegistry->typeRegistry;
		$this->valueRegistry = $this->programRegistry->valueRegistry;
		$this->returnExpression = new ReturnExpression(
			new ConstantExpression(
				$this->valueRegistry->integer(123)
			),
		);
	}

	public function testReturnedExpression(): void {
		self::assertInstanceOf(ConstantExpression::class,
			$this->returnExpression->returnedExpression);
	}

	public function testAnalyse(): void {
		$result = $this->returnExpression->analyse(new AnalyserContext($this->programRegistry, new VariableScope([])));
		self::assertTrue($result->returnType()->isSubtypeOf(
			$this->typeRegistry->integer()
		));
	}

	public function testExecute(): void {
		$this->expectException(FunctionReturn::class);
		$this->returnExpression->execute(new ExecutionContext($this->programRegistry, new VariableValueScope([])));
	}

	public function testExecuteResult(): void {
		try {
			$this->returnExpression->execute(new ExecutionContext($this->programRegistry, new VariableValueScope([])));
		} catch (FunctionReturn $e) {
			self::assertEquals(
				$this->valueRegistry->integer(123),
				$e->typedValue->value
			);
			return;
		}
	}

}