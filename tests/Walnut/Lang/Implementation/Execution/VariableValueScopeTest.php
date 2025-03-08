<?php

namespace Walnut\Lang\Test\Implementation\Execution;

use PHPUnit\Framework\TestCase;
use Walnut\Lang\Blueprint\Code\Scope\TypedValue;
use Walnut\Lang\Blueprint\Code\Scope\UnknownContextVariable;
use Walnut\Lang\Blueprint\Code\Scope\UnknownVariable;
use Walnut\Lang\Blueprint\Common\Identifier\VariableNameIdentifier;
use Walnut\Lang\Implementation\Code\NativeCode\NativeCodeTypeMapper;
use Walnut\Lang\Implementation\Code\Scope\VariableValueScope;
use Walnut\Lang\Implementation\Program\Builder\CustomMethodRegistryBuilder;
use Walnut\Lang\Implementation\Program\Builder\TypeRegistryBuilder;
use Walnut\Lang\Implementation\Program\Registry\MainMethodRegistry;
use Walnut\Lang\Implementation\Program\Registry\NestedMethodRegistry;
use Walnut\Lang\Implementation\Program\Registry\ValueRegistry;
use Walnut\Lang\Test\EmptyDependencyContainer;

final class VariableValueScopeTest extends TestCase {

	private readonly TypeRegistryBuilder $typeRegistry;
	private readonly ValueRegistry $valueRegistry;
	private readonly VariableValueScope $variableValueScope;

	public function setUp(): void {
		parent::setUp();
		$this->typeRegistry = new TypeRegistryBuilder(
			new CustomMethodRegistryBuilder(),
			new MainMethodRegistry(
				new NativeCodeTypeMapper(),
				new NestedMethodRegistry(),
				[]
			)
		);
		$this->valueRegistry = new ValueRegistry($this->typeRegistry);
		$this->variableValueScope = new VariableValueScope([
			'x' => TypedValue::forValue(
				$this->valueRegistry->integer(123)
			)->withType(
				$this->typeRegistry->integer()
			)
		]);
	}

	public function testEmptyScope(): void {
		self::assertEquals([], VariableValueScope::empty()->variables());
	}

	public function testVariables(): void {
		self::assertEquals(['x'], $this->variableValueScope->variables());
	}

	public function testFindVariable(): void {
		self::assertEquals(
			TypedValue::forValue(
				$this->valueRegistry->integer(123)
			)->withType(
				$this->typeRegistry->integer()
			),
			$this->variableValueScope->findTypedValueOf(new VariableNameIdentifier('x'))
		);
	}

	public function testFindVariableNotFound(): void {
		self::assertEquals(
			UnknownVariable::value,
			$this->variableValueScope->findTypedValueOf(new VariableNameIdentifier('y'))
		);
	}

	public function testTypeOf(): void {
		self::assertEquals(
			$this->typeRegistry->integer(),
			$this->variableValueScope->typeOf(new VariableNameIdentifier('x'))
		);
	}

	public function testValueOf(): void {
		self::assertEquals(
			$this->valueRegistry->integer(123),
			$this->variableValueScope->valueOf(new VariableNameIdentifier('x'))
		);
	}

	public function testTypeOfUnknown(): void {
		$this->expectException(UnknownContextVariable::class);
		$this->variableValueScope->typeOf(new VariableNameIdentifier('y'));
	}

	public function testTypedValueOfUnknown(): void {
		$this->expectException(UnknownContextVariable::class);
		$this->variableValueScope->typedValueOf(new VariableNameIdentifier('y'));
	}

	public function testWithAddedValues(): void {
		$variableValueScope = $this->variableValueScope->withAddedVariableValue(
			new VariableNameIdentifier('y'),
			TypedValue::forValue(
				$this->valueRegistry->string('abc')
			)->withType(
				$this->typeRegistry->string()
			)
		);
		self::assertEquals(['x', 'y'], $variableValueScope->variables());
		self::assertEquals(
			$this->typeRegistry->string(),
			$variableValueScope->typeOf(new VariableNameIdentifier('y'))
		);
		self::assertEquals(
			$this->valueRegistry->string('abc'),
			$variableValueScope->valueOf(new VariableNameIdentifier('y'))
		);
	}

	public function testWithAddedTypes(): void {
		$variableValueScope = $this->variableValueScope->withAddedVariableType(
			new VariableNameIdentifier('y'),
			$this->typeRegistry->string(),
		);
		self::assertEquals(['x', 'y'], $variableValueScope->variables());
		self::assertEquals(
			$this->typeRegistry->string(),
			$variableValueScope->typeOf(new VariableNameIdentifier('y'))
		);
	}

}