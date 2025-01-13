<?php

namespace Walnut\Lang\Test\Implementation\Program;

use Walnut\Lang\Blueprint\Common\Identifier\VariableNameIdentifier;
use Walnut\Lang\Blueprint\Program\InvalidEntryPoint;
use Walnut\Lang\Blueprint\Value\Value;
use Walnut\Lang\Test\BaseProgramTestHelper;

final class ProgramTest extends BaseProgramTestHelper {

	public function setUp(): void {
		parent::setUp();
	}

	private function doCall(): Value {
		return $this->program->getEntryPoint(
			new VariableNameIdentifier('main'),
			$this->typeRegistry->string(),
			$this->typeRegistry->integer(),
		)->call($this->valueRegistry->null);
	}

	public function testExecuteOk(): void {
		$this->programContext->globalScopeBuilder->addVariable(
			new VariableNameIdentifier('main'),
			$this->valueRegistry->function(
				$this->typeRegistry->string(),
				$this->typeRegistry->nothing,
				$this->typeRegistry->integer(),
				$this->expressionRegistry->functionBody(
					$this->expressionRegistry->constant(
						$this->valueRegistry->integer(42)
					)
				)
			)
		);
		$result = $this->doCall();
		$this->assertEquals('42', (string)$result->literalValue);
	}

	public function testErrorFunctionIsNotDefined(): void {
		try {
			$this->doCall();
		} catch (InvalidEntryPoint $e) {
			$this->assertEquals("function is not defined", $e->failReason);
		}
	}

	public function testErrorValueIsNotAFunction(): void {
		$this->programContext->globalScopeBuilder->addVariable(
			new VariableNameIdentifier('main'),
			$this->valueRegistry->integer(42)
		);
		try {
			$this->doCall();
		} catch (InvalidEntryPoint $e) {
			$this->assertEquals("value is not a function", $e->failReason);
		}
	}

	public function testErrorWrongParameterType(): void {
		$this->programContext->globalScopeBuilder->addVariable(
			new VariableNameIdentifier('main'),
			$this->valueRegistry->function(
				$this->typeRegistry->integer(),
				$this->typeRegistry->nothing,
				$this->typeRegistry->integer(),
				$this->expressionRegistry->functionBody(
					$this->expressionRegistry->constant(
						$this->valueRegistry->integer(42)
					)
				)
			)
		);
		try {
			$this->doCall();
		} catch (InvalidEntryPoint $e) {
			$this->assertEquals("wrong parameter type: String expected, Integer given", $e->failReason);
		}
	}

	public function testErrorWrongReturnType(): void {
		$this->programContext->globalScopeBuilder->addVariable(
			new VariableNameIdentifier('main'),
			$this->valueRegistry->function(
				$this->typeRegistry->string(),
				$this->typeRegistry->nothing,
				$this->typeRegistry->real(),
				$this->expressionRegistry->functionBody(
					$this->expressionRegistry->constant(
						$this->valueRegistry->real(4.2)
					)
				)
			)
		);
		try {
			$this->doCall();
		} catch (InvalidEntryPoint $e) {
			$this->assertEquals("wrong return type", $e->failReason);
		}
	}
}