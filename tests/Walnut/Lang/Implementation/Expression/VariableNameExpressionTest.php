<?php

namespace Walnut\Lang\Test\Implementation\Expression;

use PHPUnit\Framework\TestCase;
use Walnut\Lang\Blueprint\Code\Scope\TypedValue;
use Walnut\Lang\Blueprint\Identifier\VariableNameIdentifier;
use Walnut\Lang\Implementation\Code\Analyser\AnalyserContext;
use Walnut\Lang\Implementation\Code\Execution\ExecutionContext;
use Walnut\Lang\Implementation\Code\Expression\VariableNameExpression;
use Walnut\Lang\Implementation\Code\Scope\VariableScope;
use Walnut\Lang\Implementation\Code\Scope\VariableValueScope;
use Walnut\Lang\Implementation\Program\Builder\TypeRegistryBuilder;
use Walnut\Lang\Implementation\Program\Registry\ValueRegistry;
use Walnut\Lang\Test\EmptyDependencyContainer;

final class VariableNameExpressionTest extends TestCase {
	private readonly TypeRegistryBuilder $typeRegistry;
	private readonly ValueRegistry $valueRegistry;
	private readonly VariableNameExpression $variableNameExpression;

	protected function setUp(): void {
		parent::setUp();
		$this->typeRegistry = new TypeRegistryBuilder();
		$this->valueRegistry = new ValueRegistry($this->typeRegistry, new EmptyDependencyContainer);
		$this->variableNameExpression = new VariableNameExpression(
			$this->typeRegistry,
			new VariableNameIdentifier('x')
		);
	}

	public function testVariableName(): void {
		self::assertEquals('x',
			$this->variableNameExpression->variableName()->identifier);
	}

	public function testAnalyse(): void {
		$result = $this->variableNameExpression->analyse(
			new AnalyserContext(new VariableScope([
				'x' => $this->typeRegistry->integer()
			]))
		);
		self::assertEquals($this->typeRegistry->integer(), $result->expressionType());
	}

	public function testExecute(): void {
		$result = $this->variableNameExpression->execute(
			new ExecutionContext(
				new VariableValueScope([
					'x' => new TypedValue(
						$this->typeRegistry->integer(),
						$this->valueRegistry->integer(123)
					)
				])
			)
		);
		self::assertEquals($this->valueRegistry->integer(123), $result->value());
	}

}