<?php

namespace Walnut\Lang\Test\Implementation\Value;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Common\Identifier\VariableNameIdentifier;
use Walnut\Lang\Implementation\Code\Scope\VariableValueScope;
use Walnut\Lang\Test\Implementation\BaseProgramTestHelper;

final class FunctionValueTest extends BaseProgramTestHelper {

	public function testReturnTypeOk(): void {
		$this->expectNotToPerformAssertions();
		$fn = $this->valueRegistry->function(
			$this->typeRegistry->nameAndType(
				$this->typeRegistry->integer(10, 20),
				null
			),
			$this->typeRegistry->nameAndType(
				$this->typeRegistry->nothing,
				null
			),
			$this->typeRegistry->integer(),
			$this->expressionRegistry->functionBody(
				$this->expressionRegistry->variableName(new VariableNameIdentifier('#'))
			)
		);
		$fn->selfAnalyse($this->analyserContext);
	}

	public function testReturnTypeWithContextOk(): void {
		$this->expectNotToPerformAssertions();
		$fn = $this->valueRegistry->function(
			$this->typeRegistry->nameAndType(
				$this->typeRegistry->null,
				null
			),
			$this->typeRegistry->nameAndType(
				$this->typeRegistry->nothing,
				null
			),
			$this->typeRegistry->any,
			$this->expressionRegistry->functionBody(
				$this->expressionRegistry->tuple([
					$this->expressionRegistry->variableName(new VariableNameIdentifier('self')),
					$this->expressionRegistry->variableName(new VariableNameIdentifier('y'))
				])
			)
		)->withSelfReferenceAs(new VariableNameIdentifier('self'))
		->withVariableValueScope(
			VariableValueScope::empty()->withAddedVariableValue(
				new VariableNameIdentifier('y'),
				$this->valueRegistry->integer(15)
			)
		);
		$fn->selfAnalyse($this->analyserContext->withAddedVariableType(
			new VariableNameIdentifier('y'),
			$this->typeRegistry->integer()
		));
	}

	public function testReturnTypeWithContextExecuteOk(): void {
		$fn = $this->valueRegistry->function(
			$this->typeRegistry->nameAndType(
				$this->typeRegistry->null,
				null
			),
			$this->typeRegistry->nameAndType(
				$this->typeRegistry->nothing,
				null
			),
			$this->typeRegistry->any,
			$this->expressionRegistry->functionBody(
				$this->expressionRegistry->tuple([
					$this->expressionRegistry->variableName(new VariableNameIdentifier('self')),
					$this->expressionRegistry->variableName(new VariableNameIdentifier('y'))
				])
			)
		)->withSelfReferenceAs(new VariableNameIdentifier('self'));
		$result = $fn->execute(
			$this->executionContext
				->withAddedVariableValue(
					new VariableNameIdentifier('y'),
					$this->valueRegistry->integer(15)
				),
			$this->valueRegistry->null
		);
		$this->assertTrue($result->equals(
			$this->valueRegistry->tuple([
				$fn,
				$this->valueRegistry->integer(15)
			])
		));
	}

	public function testReturnTypeNotOk(): void {
		$this->expectException(AnalyserException::class);
		$fn = $this->valueRegistry->function(
			$this->typeRegistry->nameAndType(
				$this->typeRegistry->integer(),
				null
			),
			$this->typeRegistry->nameAndType(
				$this->typeRegistry->nothing,
				null
			),
			$this->typeRegistry->integer(10, 20),
			$this->expressionRegistry->functionBody(
				$this->expressionRegistry->variableName(new VariableNameIdentifier('#'))
			)
		);
		$fn->selfAnalyse($this->analyserContext);
	}

	public function testReturnValueOk(): void {
		$fn = $this->valueRegistry->function(
			$this->typeRegistry->nameAndType(
				$this->typeRegistry->integer(10, 20),
				null
			),
			$this->typeRegistry->nameAndType(
				$this->typeRegistry->nothing,
				null
			),
			$this->typeRegistry->integer(),
			$this->expressionRegistry->functionBody(
				$this->expressionRegistry->variableName(new VariableNameIdentifier('#'))
			)
		);
		$result = $fn->execute(
			$this->executionContext,
			$int = $this->valueRegistry->integer(15)
		);
		$this->assertTrue($result->equals($int));
	}

	public function testReturnValueDirectReturnOk(): void {
		$fn = $this->valueRegistry->function(
			$this->typeRegistry->nameAndType(
				$this->typeRegistry->integer(10, 20),
				null
			),
			$this->typeRegistry->nameAndType(
				$this->typeRegistry->nothing,
				null
			),
			$this->typeRegistry->integer(),
			$this->expressionRegistry->functionBody(
				$this->expressionRegistry->return(
					$this->expressionRegistry->variableName(new VariableNameIdentifier('#'))
				)
			)
		);
		$result = $fn->execute(
			$this->executionContext,
			$int = $this->valueRegistry->integer(15)
		);
		$this->assertTrue($result->equals($int));
	}
}