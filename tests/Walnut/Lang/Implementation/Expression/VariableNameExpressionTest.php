<?php

namespace Walnut\Lang\Test\Implementation\Expression;

use Walnut\Lang\Blueprint\Common\Identifier\VariableNameIdentifier;
use Walnut\Lang\Implementation\Code\Expression\VariableNameExpression;
use Walnut\Lang\Test\BaseProgramTestHelper;

final class VariableNameExpressionTest extends BaseProgramTestHelper {
	private readonly VariableNameExpression $variableNameExpression;

	protected function setUp(): void {
		parent::setUp();
		$this->variableNameExpression = new VariableNameExpression(
			new VariableNameIdentifier('x')
		);
	}

	public function testVariableName(): void {
		self::assertEquals('x',
			$this->variableNameExpression->variableName->identifier);
	}

	public function testAnalyse(): void {
		$result = $this->variableNameExpression->analyse(
			$this->programRegistry->analyserContext->withAddedVariableType(
				new VariableNameIdentifier('x'),
				$this->typeRegistry->integer()
			)
		);
		self::assertEquals($this->typeRegistry->integer(), $result->expressionType);
	}

	public function testExecute(): void {
		$result = $this->variableNameExpression->execute(
			$this->programRegistry->executionContext
				->withAddedVariableValue(
					new VariableNameIdentifier('x'),
					$this->valueRegistry->integer(123)
				)
		);
		self::assertEquals($this->valueRegistry->integer(123), $result->value);
	}

}