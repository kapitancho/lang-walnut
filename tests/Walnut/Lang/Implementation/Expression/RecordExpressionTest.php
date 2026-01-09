<?php

namespace Walnut\Lang\Test\Implementation\Expression;

use PHPUnit\Framework\TestCase;
use Walnut\Lang\Implementation\Code\Analyser\AnalyserContext;
use Walnut\Lang\Implementation\Code\Execution\ExecutionContext;
use Walnut\Lang\Implementation\Code\Expression\ConstantExpression;
use Walnut\Lang\Implementation\Code\Expression\RecordExpression;
use Walnut\Lang\Implementation\Code\Scope\VariableScope;
use Walnut\Lang\Implementation\Code\Scope\VariableValueScope;
use Walnut\Lang\Implementation\Program\Builder\TypeRegistryBuilder;
use Walnut\Lang\Implementation\Program\ProgramContextFactory;
use Walnut\Lang\Implementation\Program\Registry\ProgramRegistry;
use Walnut\Lang\Implementation\Program\Registry\ValueRegistry;

final class RecordExpressionTest extends TestCase {
	private readonly TypeRegistryBuilder $typeRegistry;
	private readonly ValueRegistry $valueRegistry;
	private readonly ProgramRegistry $programRegistry;
	private readonly RecordExpression $recordExpression;

	protected function setUp(): void {
		parent::setUp();
		$this->programRegistry = new ProgramContextFactory()->programContext->programRegistry;
		$this->typeRegistry = $this->programRegistry->typeRegistry;
		$this->valueRegistry = $this->programRegistry->valueRegistry;
		$this->recordExpression = new RecordExpression(
			[
				'a' => new ConstantExpression(
					$this->valueRegistry->integer(123)
				),
				'b' => new ConstantExpression(
					$this->valueRegistry->string("456")
				)
			]
		);
	}

	public function testValues(): void {
		self::assertCount(2, $this->recordExpression->values);
	}

	public function testAnalyse(): void {
		$result = $this->recordExpression->analyse(
			new AnalyserContext($this->programRegistry->typeRegistry, $this->programRegistry->methodFinder,
				new VariableScope([])
			)
		);
		self::assertTrue($result->expressionType->isSubtypeOf(
			$this->typeRegistry->record([
				'a' => $this->typeRegistry->integer(),
				'b' => $this->typeRegistry->string()
			])
		));
	}

	public function testExecute(): void {
		$result = $this->recordExpression->execute(
			new ExecutionContext($this->programRegistry,
				new VariableValueScope([])
			)
		);
		self::assertEquals(
			(string)$this->valueRegistry->record([
				'a' => $this->valueRegistry->integer(123),
				'b' => $this->valueRegistry->string("456")
			]),
			(string)$result->value
		);
	}

}