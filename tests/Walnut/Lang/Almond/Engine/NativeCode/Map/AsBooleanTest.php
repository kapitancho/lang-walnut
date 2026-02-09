<?php

namespace Walnut\Lang\Test\Almond\Engine\NativeCode\Map;

use Walnut\Lang\Test\Almond\Engine\CodeExecutionTestHelper;

final class AsBooleanTest extends CodeExecutionTestHelper {

	public function testAsBooleanEmpty(): void {
		$result = $this->executeCodeSnippet("[:]->asBoolean;");
		$this->assertEquals("false", $result);
	}

	public function testAsBooleanNotEmpty(): void {
		$result = $this->executeCodeSnippet("[a: 1, b: 'hello']->asBoolean;");
		$this->assertEquals("true", $result);
	}

	public function testAsBooleanCast(): void {
		$result = $this->executeCodeSnippet("[a: 1, b: 'hello']->as(`Boolean);");
		$this->assertEquals("true", $result);
	}

	public function testAsBooleanType(): void {
		$result = $this->executeCodeSnippet("bool[a: 1, b: 'hello'];",
			valueDeclarations: 'bool = ^a: Map => Boolean :: a->as(`Boolean);'
		);
		$this->assertEquals("true", $result);
	}

	public function testAsBooleanTypeEmpty(): void {
		$result = $this->executeCodeSnippet("bool[:];",
			valueDeclarations: 'bool = ^a: Map<0> => False :: a->as(`Boolean);'
		);
		$this->assertEquals("false", $result);
	}

	public function testAsBooleanTypeNotEmpty(): void {
		$result = $this->executeCodeSnippet("bool[a: 1, b: 'hello'];",
			valueDeclarations: 'bool = ^a: Map<1..> => True :: a->as(`Boolean);'
		);
		$this->assertEquals("true", $result);
	}

	public function testAsBooleanInvalidParameterType(): void {
		$this->executeErrorCodeSnippet('Invalid parameter type', "[a: 1, b: 2]->asBoolean(5);");
	}

}
