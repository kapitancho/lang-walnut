<?php

namespace Walnut\Lang\Test\Implementation\Expression;

use PHPUnit\Framework\TestCase;
use Walnut\Lang\Blueprint\Code\Execution\FunctionReturn;
use Walnut\Lang\Implementation\Code\Analyser\AnalyserContext;
use Walnut\Lang\Implementation\Code\Execution\ExecutionContext;
use Walnut\Lang\Implementation\Code\Expression\ConstantExpression;
use Walnut\Lang\Implementation\Code\Expression\NoErrorExpression;
use Walnut\Lang\Implementation\Code\Scope\VariableScope;
use Walnut\Lang\Implementation\Code\Scope\VariableValueScope;
use Walnut\Lang\Implementation\Program\Builder\TypeRegistryBuilder;
use Walnut\Lang\Implementation\Program\ProgramContextFactory;
use Walnut\Lang\Implementation\Program\Registry\ProgramRegistry;
use Walnut\Lang\Implementation\Program\Registry\ValueRegistry;

final class NoErrorExpressionTest extends TestCase {
	private readonly TypeRegistryBuilder $typeRegistry;
	private readonly ValueRegistry $valueRegistry;
	private readonly ProgramRegistry $programRegistry;
	private readonly NoErrorExpression $noErrorExpression;
	private readonly NoErrorExpression $errorExpression;

	protected function setUp(): void {
		parent::setUp();
		$this->programRegistry = new ProgramContextFactory()->programContext->programRegistry;
		$this->typeRegistry = $this->programRegistry->typeRegistry;
		$this->valueRegistry = $this->programRegistry->valueRegistry;
		$this->noErrorExpression = new NoErrorExpression(
			new ConstantExpression(
				$this->valueRegistry->integer(123)
			),
		);
		$this->errorExpression = new NoErrorExpression(
			new ConstantExpression(
				$this->valueRegistry->error(
					$this->valueRegistry->integer(123)
				)
			),
		);
	}

	public function testReturnedExpression(): void {
		self::assertInstanceOf(ConstantExpression::class,
			$this->noErrorExpression->targetExpression);
	}

	public function testAnalyse(): void {
		$result = $this->noErrorExpression->analyse($this->programRegistry->analyserContext);
		self::assertTrue($result->returnType()->isSubtypeOf(
			$this->typeRegistry->integer()
		));
	}

	public function testExecute(): void {
		$this->expectNotToPerformAssertions();
		$this->noErrorExpression->execute($this->programRegistry->executionContext);
	}

	public function testExecuteOnError(): void {
		$this->expectException(FunctionReturn::class);
		$this->errorExpression->execute($this->programRegistry->executionContext);
	}

	public function testExecuteResult(): void {
		try {
			$this->errorExpression->execute($this->programRegistry->executionContext);
		} catch (FunctionReturn $e) {
			self::assertEquals(
				$this->valueRegistry->error(
					$this->valueRegistry->integer(123)
				),
				$e->typedValue
			);
			return;
		}
	}

}