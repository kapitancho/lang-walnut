<?php

namespace Walnut\Lang\Test\Almond\Engine\NativeCode\Any;

use Walnut\Lang\Test\Almond\Engine\CodeExecutionTestHelper;

final class BinaryOrElseTest extends CodeExecutionTestHelper {

	public function testBinaryOrElseWithAny(): void {
		$result = $this->executeCodeSnippet(
			"404 ?? 504;",
		);
		$this->assertEquals("404", $result);
	}

	public function testBinaryOrElseWithErrorTransform(): void {
		$result = $this->executeCodeSnippet(
			"makeError(404) ?? 504;",
			valueDeclarations: "
				makeError = ^code: Integer => Result<String, Integer> :: @code;
			"
		);
		$this->assertEquals("504", $result);
	}

	public function testBinaryOrElseWithNoTransform(): void {
		$result = $this->executeCodeSnippet(
			"makeError(404) ?? 504;",
			valueDeclarations: "
				makeError = ^code: Integer => Result<String, Integer> :: code->asString;
			"
		);
		$this->assertEquals("'404'", $result);
	}

	public function testBinaryOrElseWithStringTransform(): void {
		$result = $this->executeCodeSnippet(
			"makeError('failed') ?? 'ERROR';",
			valueDeclarations: "
				makeError = ^msg: String => Result<Integer, String> :: @msg;
			"
		);
		$this->assertEquals("'ERROR'", $result);
	}

	public function testBinaryOrElseOptionalTransform(): void {
		$result = $this->executeCodeSnippet(
			"makeEmpty(404) ?? 504;",
			valueDeclarations: "
				makeEmpty = ^code: Integer => Optional<String> :: empty;
			"
		);
		$this->assertEquals("504", $result);
	}

	public function testBinaryOrElseOptionalNoTransform(): void {
		$result = $this->executeCodeSnippet(
			"makeEmpty(404) ?? 504;",
			valueDeclarations: "
				makeEmpty = ^code: Integer => Optional<String> :: code->asString;
			"
		);
		$this->assertEquals("'404'", $result);
	}

	public function testBinaryOrElseTypeCheckBoth(): void {
		$result = $this->executeCodeSnippet(
			"orElse[a: empty, b: @false];",
			valueDeclarations: "
				orElse = ^[a: Optional<String>, b: Result<Integer, Boolean>] 
					=> String|Integer|3.14 :: #a ?? #b ?? 3.14;
			"
		);
		$this->assertEquals("3.14", $result);
	}

	public function testBinaryOrElseTypeCheckFirst(): void {
		$result = $this->executeCodeSnippet(
			"orElse[a: 'hello', b: @false];",
			valueDeclarations: "
				orElse = ^[a: Optional<String>, b: Result<Integer, Boolean>] 
					=> String|Integer|3.14 :: #a ?? #b ?? 3.14;
			"
		);
		$this->assertEquals("'hello'", $result);
	}

	public function testBinaryOrElseTypeCheckSecond(): void {
		$result = $this->executeCodeSnippet(
			"orElse[a: empty, b: 42];",
			valueDeclarations: "
				orElse = ^[a: Optional<String>, b: Result<Integer, Boolean>] 
					=> String|Integer|3.14 :: #a ?? #b ?? 3.14;
			"
		);
		$this->assertEquals("42", $result);
	}

}
