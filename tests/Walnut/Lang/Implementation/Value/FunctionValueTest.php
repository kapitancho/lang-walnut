<?php

namespace Walnut\Lang\Test\Implementation\Value;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Common\Identifier\VariableNameIdentifier;
use Walnut\Lang\Implementation\Code\Analyser\AnalyserContext;
use Walnut\Lang\Implementation\Code\Execution\ExecutionContext;
use Walnut\Lang\Implementation\Code\Scope\VariableScope;
use Walnut\Lang\Implementation\Code\Scope\VariableValueScope;
use Walnut\Lang\Test\Implementation\BaseProgramTestHelper;

final class FunctionValueTest extends BaseProgramTestHelper {

	public function testReturnTypeOk(): void {
		$this->expectNotToPerformAssertions();
		$fn = $this->valueRegistry->function(
			$this->typeRegistry->integer(10, 20),
			null,
			$this->typeRegistry->nothing,
			$this->typeRegistry->integer(),
			$this->expressionRegistry->functionBody(
				$this->expressionRegistry->variableName(new VariableNameIdentifier('#'))
			)
		);
		$fn->selfAnalyse(new AnalyserContext($this->programRegistry, VariableScope::empty()));
	}

	public function testReturnTypeNotOk(): void {
		$this->expectException(AnalyserException::class);
		$fn = $this->valueRegistry->function(
			$this->typeRegistry->integer(),
			null,
			$this->typeRegistry->nothing,
			$this->typeRegistry->integer(10, 20),
			$this->expressionRegistry->functionBody(
				$this->expressionRegistry->variableName(new VariableNameIdentifier('#'))
			)
		);
		$fn->selfAnalyse(new AnalyserContext($this->programRegistry, VariableScope::empty()));
	}

	public function testReturnValueOk(): void {
		$fn = $this->valueRegistry->function(
			$this->typeRegistry->integer(10, 20),
			null,
			$this->typeRegistry->nothing,
			$this->typeRegistry->integer(),
			$this->expressionRegistry->functionBody(
				$this->expressionRegistry->variableName(new VariableNameIdentifier('#'))
			)
		);
		$result = $fn->execute(
			new ExecutionContext($this->programRegistry, VariableValueScope::empty()),
			$int = $this->valueRegistry->integer(15)
		);
		$this->assertTrue($result->equals($int));
	}

	public function testReturnValueDirectReturnOk(): void {
		$fn = $this->valueRegistry->function(
			$this->typeRegistry->integer(10, 20),
			null,
			$this->typeRegistry->nothing,
			$this->typeRegistry->integer(),
			$this->expressionRegistry->functionBody(
				$this->expressionRegistry->return(
					$this->expressionRegistry->variableName(new VariableNameIdentifier('#'))
				)
			)
		);
		$result = $fn->execute(
			new ExecutionContext($this->programRegistry, VariableValueScope::empty()),
			$int = $this->valueRegistry->integer(15)
		);
		$this->assertTrue($result->equals($int));
	}
}