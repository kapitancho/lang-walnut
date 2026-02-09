<?php

namespace Walnut\Lang\Test\Almond\Engine\NativeCode\Real;

use Walnut\Lang\Test\Almond\Engine\CodeExecutionTestHelper;

final class AsBooleanTest extends CodeExecutionTestHelper {

	public function testAsBooleanEmpty(): void {
		$result = $this->executeCodeSnippet("0->asBoolean;");
		$this->assertEquals("false", $result);
	}

	public function testAsBooleanNotEmpty(): void {
		$result = $this->executeCodeSnippet("3.14->asBoolean;");
		$this->assertEquals("true", $result);
	}

	public function testAsBooleanCast(): void {
		$result = $this->executeCodeSnippet("3.14->as(`Boolean);");
		$this->assertEquals("true", $result);
	}

	public function testAsBooleanType(): void {
		$result = $this->executeCodeSnippet("bool(3.14);",
			valueDeclarations: 'bool = ^a: Real => Boolean :: a->as(`Boolean);'
		);
		$this->assertEquals("true", $result);
	}

	public function testAsBooleanTypeEmpty(): void {
		$result = $this->executeCodeSnippet("bool(0);",
			valueDeclarations: 'bool = ^a: Real<0> => False :: a->as(`Boolean);'
		);
		$this->assertEquals("false", $result);
	}

	public function testAsBooleanTypeNotEmpty(): void {
		$result = $this->executeCodeSnippet("bool(3.14);",
			valueDeclarations: 'bool = ^a: Real<1..> => True :: a->as(`Boolean);'
		);
		$this->assertEquals("true", $result);
	}

	public function testAsBooleanInvalidParameterType(): void {
		$this->executeErrorCodeSnippet('Invalid parameter type', "3.14->asBoolean(5);");
	}

}
