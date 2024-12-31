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
use Walnut\Lang\Implementation\Program\Registry\ValueRegistry;
use Walnut\Lang\Test\EmptyDependencyContainer;

final class RecordExpressionTest extends TestCase {
	private readonly TypeRegistryBuilder $typeRegistry;
	private readonly ValueRegistry $valueRegistry;
	private readonly RecordExpression $recordExpression;

	protected function setUp(): void {
		parent::setUp();
		$this->typeRegistry = new TypeRegistryBuilder();
		$this->valueRegistry = new ValueRegistry($this->typeRegistry, new EmptyDependencyContainer);
		$this->recordExpression = new RecordExpression(
			$this->typeRegistry,
			$this->valueRegistry,
			[
				'a' => new ConstantExpression(
					$this->typeRegistry,
					$this->valueRegistry->integer(123)
				),
				'b' => new ConstantExpression(
					$this->typeRegistry,
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
			new AnalyserContext(
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
			new ExecutionContext(
				new VariableValueScope([])
			)
		);
		self::assertEquals(
			$this->valueRegistry->record([
				'a' => $this->valueRegistry->integer(123),
				'b' => $this->valueRegistry->string("456")
			]),
			$result->value
		);
	}

}