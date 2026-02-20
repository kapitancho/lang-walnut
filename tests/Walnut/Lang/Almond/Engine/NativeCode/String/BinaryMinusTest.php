<?php

namespace Walnut\Lang\Test\Almond\Engine\NativeCode\String;

use Walnut\Lang\Test\Almond\Engine\CodeExecutionTestHelper;

final class BinaryMinusTest extends CodeExecutionTestHelper {

	public function testBinaryMinusOk(): void {
		$result = $this->executeCodeSnippet("'hello' - 'l';");
		$this->assertEquals("'heo'", $result);
	}

	public function testBinaryMinusNotFound(): void {
		$result = $this->executeCodeSnippet("'hello' - 'q';");
		$this->assertEquals("'hello'", $result);
	}

	public function testBinaryMinusStringMultiple(): void {
		$result = $this->executeCodeSnippet("'hello world, call hello!' - 'ell';");
		$this->assertEquals("'ho world, call ho!'", $result);
	}

	public function testBinaryMinusInfinity(): void {
		$result = $this->executeCodeSnippet(
			"min('hello');",
			valueDeclarations: "min = ^str: String<5..20> => String<..20> :: str - 'e';"
		);
		$this->assertEquals("'hllo'", $result);
	}

	public function testBinaryMinusEmptyArray(): void {
		$result = $this->executeCodeSnippet("'hello world' - [];");
		$this->assertEquals("'hello world'", $result);
	}

	public function testBinaryMinusNonEmptyArray(): void {
		$result = $this->executeCodeSnippet("'hello world' - ['o', 'el', 'y'];");
		$this->assertEquals("'hl wrld'", $result);
	}

	public function testBinaryMinusEmptySet(): void {
		$result = $this->executeCodeSnippet("'hello world' - [;];");
		$this->assertEquals("'hello world'", $result);
	}

	public function testBinaryMinusNonEmptySet(): void {
		$result = $this->executeCodeSnippet("'hello world' - ['o'; 'el'; 'y'; 'o'];");
		$this->assertEquals("'hl wrld'", $result);
	}

	public function testBinaryMinusInvalidParameterStringType(): void {
		$this->executeErrorCodeSnippet("The parameter type String[''] is not a valid type for String subtraction. Expected String<1..>, Array<String<1..>> or Set<String<1..>>.", "'hello ' - '';");
	}

	public function testBinaryMinusInvalidParameterArrayType(): void {
		$this->executeErrorCodeSnippet("The parameter type [String['x'], Integer[12], String['w']] is not a valid type for String subtraction. Expected String<1..>, Array<String<1..>> or Set<String<1..>>.", "'hello ' - ['x', 12, 'w'];");
	}

	public function testBinaryMinusInvalidParameterSetType(): void {
		$this->executeErrorCodeSnippet("The parameter type Set<(Integer[12]|String['x', 'w']), 3> is not a valid type for String subtraction. Expected String<1..>, Array<String<1..>> or Set<String<1..>>.", "'hello ' - ['x'; 12; 'w'];");
	}

	public function testBinaryMinusInvalidParameterType(): void {
		$this->executeErrorCodeSnippet('The parameter type False is not a valid type for String subtraction. Expected String<1..>, Array<String<1..>> or Set<String<1..>>.', "'hello ' - false;");
	}

}