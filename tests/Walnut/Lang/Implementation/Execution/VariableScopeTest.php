<?php

namespace Walnut\Lang\Test\Implementation\Execution;

use Walnut\Lang\Blueprint\Code\Scope\UnknownContextVariable;
use Walnut\Lang\Blueprint\Common\Identifier\VariableNameIdentifier;
use Walnut\Lang\Implementation\Code\Scope\VariableScope;
use Walnut\Lang\Test\BaseProgramTestHelper;

final class VariableScopeTest extends BaseProgramTestHelper {

	private readonly VariableScope $variableScope;

	public function setUp(): void {
		parent::setUp();
		$this->variableScope = new VariableScope([
			'x' => $this->typeRegistry->integer()
		]);
	}

	public function testEmptyScope(): void {
		self::assertEquals([], VariableScope::empty()->variables());
	}

	public function testVariables(): void {
		self::assertEquals(['x'], $this->variableScope->variables());
	}

	public function testTypeOf(): void {
		self::assertEquals($this->typeRegistry->integer(),
			$this->variableScope->typeOf(new VariableNameIdentifier('x'))
		);
	}

	public function testTypeOfUnknown(): void {
		$this->expectException(UnknownContextVariable::class);
		$this->variableScope->typeOf(new VariableNameIdentifier('y'));
	}

	public function testWithAddedVariablePairs(): void {
		$variableScope = $this->variableScope->withAddedVariableType(
			new VariableNameIdentifier('y'),
			$this->typeRegistry->string()
		);
		self::assertEquals(['x', 'y'], $variableScope->variables());
		self::assertEquals($this->typeRegistry->string(),
			$variableScope->typeOf(new VariableNameIdentifier('y'))
		);
	}

}