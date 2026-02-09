<?php

namespace Walnut\Lang\Test\Almond\Engine\NativeCode\Set;

use Walnut\Lang\Test\Almond\Engine\CodeExecutionTestHelper;

final class AsBooleanTest extends CodeExecutionTestHelper {

	public function testAsBooleanEmpty(): void {
		$result = $this->executeCodeSnippet("[;]->asBoolean;");
		$this->assertEquals("false", $result);
	}

	public function testAsBooleanNotEmpty(): void {
		$result = $this->executeCodeSnippet("[1; 'hello']->asBoolean;");
		$this->assertEquals("true", $result);
	}

	public function testAsBooleanCast(): void {
		$result = $this->executeCodeSnippet("[1; 'hello']->as(`Boolean);");
		$this->assertEquals("true", $result);
	}

	public function testAsBooleanType(): void {
		$result = $this->executeCodeSnippet("bool[1; 'hello'];",
			valueDeclarations: 'bool = ^a: Set => Boolean :: a->as(`Boolean);'
		);
		$this->assertEquals("true", $result);
	}

	public function testAsBooleanTypeEmpty(): void {
		$result = $this->executeCodeSnippet("bool[;];",
			valueDeclarations: 'bool = ^a: Set<0> => False :: a->as(`Boolean);'
		);
		$this->assertEquals("false", $result);
	}

	public function testAsBooleanTypeNotEmpty(): void {
		$result = $this->executeCodeSnippet("bool[1; 'hello'];",
			valueDeclarations: 'bool = ^a: Set<1..> => True :: a->as(`Boolean);'
		);
		$this->assertEquals("true", $result);
	}

	public function testAsBooleanInvalidParameterType(): void {
		$this->executeErrorCodeSnippet('Invalid parameter type', "[1; 2]->asBoolean(5);");
	}

}
