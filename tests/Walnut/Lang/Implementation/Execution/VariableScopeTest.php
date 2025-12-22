<?php

namespace Walnut\Lang\Test\Implementation\Execution;

use PHPUnit\Framework\TestCase;
use Walnut\Lang\Blueprint\Code\Scope\UnknownContextVariable;
use Walnut\Lang\Blueprint\Common\Identifier\VariableNameIdentifier;
use Walnut\Lang\Implementation\AST\Parser\StringEscapeCharHandler;
use Walnut\Lang\Implementation\Code\NativeCode\NativeCodeTypeMapper;
use Walnut\Lang\Implementation\Code\Scope\VariableScope;
use Walnut\Lang\Implementation\Program\Builder\CustomMethodRegistryBuilder;
use Walnut\Lang\Implementation\Program\Builder\TypeRegistryBuilder;
use Walnut\Lang\Implementation\Program\Registry\MainMethodRegistry;
use Walnut\Lang\Implementation\Program\Registry\NestedMethodRegistry;

final class VariableScopeTest extends TestCase {

	private readonly TypeRegistryBuilder $typeRegistry;
	private readonly VariableScope $variableScope;

	public function setUp(): void {
		parent::setUp();
		$this->typeRegistry = new TypeRegistryBuilder(
			new CustomMethodRegistryBuilder(),
			new MainMethodRegistry(
				new NativeCodeTypeMapper(),
				new NestedMethodRegistry(),
				[]
			),
			new StringEscapeCharHandler()
		);
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