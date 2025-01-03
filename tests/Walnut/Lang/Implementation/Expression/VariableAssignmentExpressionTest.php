<?php

namespace Walnut\Lang\Test\Implementation\Expression;

use PHPUnit\Framework\TestCase;
use Walnut\Lang\Blueprint\Code\Scope\TypedValue;
use Walnut\Lang\Blueprint\Common\Identifier\VariableNameIdentifier;
use Walnut\Lang\Implementation\Code\Analyser\AnalyserContext;
use Walnut\Lang\Implementation\Code\Execution\ExecutionContext;
use Walnut\Lang\Implementation\Code\Expression\ConstantExpression;
use Walnut\Lang\Implementation\Code\Expression\VariableAssignmentExpression;
use Walnut\Lang\Implementation\Code\Scope\VariableScope;
use Walnut\Lang\Implementation\Code\Scope\VariableValueScope;
use Walnut\Lang\Implementation\Program\Builder\TypeRegistryBuilder;
use Walnut\Lang\Implementation\Program\Registry\ValueRegistry;
use Walnut\Lang\Test\EmptyDependencyContainer;

final class VariableAssignmentExpressionTest extends TestCase {
	private readonly TypeRegistryBuilder $typeRegistry;
	private readonly ValueRegistry $valueRegistry;
	private readonly VariableAssignmentExpression $variableAssignmentExpression;

	protected function setUp(): void {
		parent::setUp();
		$this->typeRegistry = new TypeRegistryBuilder();
		$this->valueRegistry = new ValueRegistry($this->typeRegistry, new EmptyDependencyContainer);
		$this->variableAssignmentExpression = 
			new VariableAssignmentExpression(
				$this->typeRegistry,
				new VariableNameIdentifier('x'),
				new ConstantExpression(
					$this->typeRegistry,
					$this->valueRegistry->integer(123)
				)
			);
	}

	public function testVariableName(): void {
		self::assertEquals('x',
			$this->variableAssignmentExpression->variableName->identifier);
	}

	public function testAssignedExpression(): void {
		self::assertInstanceOf(ConstantExpression::class,
			$this->variableAssignmentExpression->assignedExpression);
		self::assertEquals(123,
			(int)(string)$this->variableAssignmentExpression->assignedExpression->value->literalValue);
	}

	public function testAnalyse(): void {
		$result = $this->variableAssignmentExpression->analyse(
			new AnalyserContext(
				new VariableScope([
					'x' => $this->typeRegistry->integer()
				])
			)
		);
		self::assertTrue($result->expressionType->isSubtypeOf($this->typeRegistry->integer()));
		self::assertTrue(
			$result->variableScope->typeOf(
				new VariableNameIdentifier('x')
			)->isSubtypeOf($this->typeRegistry->integer())
		);
	}

	public function testExecute(): void {
		$result = $this->variableAssignmentExpression->execute(
			new ExecutionContext(
				new VariableValueScope([
					'x' => new TypedValue(
						$this->typeRegistry->integer(),
						$this->valueRegistry->integer(123)
					)
				])
			)
		);
		self::assertEquals($this->valueRegistry->integer(123), $result->value);
	}

}