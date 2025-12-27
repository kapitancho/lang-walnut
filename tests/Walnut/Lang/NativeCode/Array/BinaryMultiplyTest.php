<?php

namespace Walnut\Lang\NativeCode\Array;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class BinaryMultiplyTest extends CodeExecutionTestHelper {

	public function testBinaryMultiplyOk(): void {
		$result = $this->executeCodeSnippet("['hello', 42] * 3;");
		$this->assertEquals("['hello', 42, 'hello', 42, 'hello', 42]", $result);
	}

	public function testBinaryMultiplyInfinity(): void {
		$result = $this->executeCodeSnippet(
			"mul3['hello'];",
			valueDeclarations: "mul3 = ^arr: Array<String> => Array<String> :: arr * 3;"
		);
		$this->assertEquals("['hello', 'hello', 'hello']", $result);
	}

	public function testBinaryMultiplyRange(): void {
		$result = $this->executeCodeSnippet(
			"mul[mul: 2, arr: ['hello', 'world']];",
			valueDeclarations: "mul = ^[mul: Integer<2..3>, arr: Array<String, 2..5>] => Array<String, 4..15> :: #arr * #mul;"
		);
		$this->assertEquals("['hello', 'world', 'hello', 'world']", $result);
	}

	public function testBinaryMultiplyTuple(): void {
		$result = $this->executeCodeSnippet(
			"mul3['hello', 42];",
			valueDeclarations: "mul3 = ^arr: [String, Real] => [String, Real, String, Real, String, Real] :: arr * 3;"
		);
		$this->assertEquals("['hello', 42, 'hello', 42, 'hello', 42]", $result);
	}

	public function testBinaryMultiplyZero(): void {
		$result = $this->executeCodeSnippet("['hello'] * 0;");
		$this->assertEquals("[]", $result);
	}

	public function testBinaryMultiplyInvalidParameterValue(): void {
		$this->executeErrorCodeSnippet('Invalid parameter type', "['hello'] * {-3};");
	}

	public function testBinaryMultiplyInvalidParameterType(): void {
		$this->executeErrorCodeSnippet('Invalid parameter type', "['hello'] * false;");
	}

}