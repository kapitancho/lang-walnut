<?php

namespace Walnut\Lang\Test\Implementation\Expression;

use PHPUnit\Framework\TestCase;
use Walnut\Lang\Blueprint\Common\Identifier\VariableNameIdentifier;
use Walnut\Lang\Implementation\Code\Analyser\AnalyserContext;
use Walnut\Lang\Implementation\Code\Execution\ExecutionContext;
use Walnut\Lang\Implementation\Code\Expression\ConstantExpression;
use Walnut\Lang\Implementation\Code\Expression\VariableAssignmentExpression;
use Walnut\Lang\Implementation\Code\Scope\VariableScope;
use Walnut\Lang\Implementation\Code\Scope\VariableValueScope;
use Walnut\Lang\Implementation\Program\Builder\TypeRegistryBuilder;
use Walnut\Lang\Implementation\Program\ProgramContextFactory;
use Walnut\Lang\Implementation\Program\Registry\ProgramRegistry;
use Walnut\Lang\Implementation\Program\Registry\ValueRegistry;

final class VariableAssignmentExpressionTest extends TestCase {
	private readonly TypeRegistryBuilder $typeRegistry;
	private readonly ValueRegistry $valueRegistry;
	private readonly ProgramRegistry $programRegistry;
	private readonly VariableAssignmentExpression $variableAssignmentExpression;

	protected function setUp(): void {
		parent::setUp();
		$this->programRegistry = new ProgramContextFactory()->programContext->programRegistry;
		$this->typeRegistry = $this->programRegistry->typeRegistry;
		$this->valueRegistry = $this->programRegistry->valueRegistry;
		$this->variableAssignmentExpression =
			new VariableAssignmentExpression(
				new VariableNameIdentifier('x'),
				new ConstantExpression(
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
			new AnalyserContext($this->programRegistry->typeRegistry, $this->programRegistry->methodFinder,
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
			new ExecutionContext($this->programRegistry,
				new VariableValueScope([
					'x' =>
						(
							$this->valueRegistry->integer(123)
						)
				])
			)
		);
		self::assertEquals($this->valueRegistry->integer(123), $result->value);
	}

}