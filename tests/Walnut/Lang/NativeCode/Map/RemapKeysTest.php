<?php

namespace Walnut\Lang\NativeCode\Map;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class RemapKeysTest extends CodeExecutionTestHelper {

	public function testRemapKeysEmpty(): void {
		$result = $this->executeCodeSnippet("[:]->remapKeys(^s: String => String :: s + 'x');");
		$this->assertEquals("[:]", $result);
	}

	public function testRemapKeysNonEmpty(): void {
		$result = $this->executeCodeSnippet("[a: 1, b: 2, c: 5, d: 10, e: 5]->remapKeys(^s: String => String :: s + 'x');");
		$this->assertEquals("[ax: 1, bx: 2, cx: 5, dx: 10, ex: 5]", $result);
	}

	public function testRemapKeysNonEmptyError(): void {
		$result = $this->executeCodeSnippet("[a: 1, b: 2, c: 5, d: 10, e: 5]->remapKeys(^String => Result<String, String> :: @'error');");
		$this->assertEquals("@'error'", $result);
	}

	public function testRemapKeysKeyType(): void {
		$result = $this->executeCodeSnippet(
			"fn[a: 1, b: 2];",
			valueDeclarations: "fn = ^m: Map<String<1>:Integer> => Map<String<3>:Integer> :: 
				m->remapKeys(^k: String<1> => String<3> :: '[' + k + ']');"
		);
		$this->assertEquals("[[a]: 1, [b]: 2]", $result);
	}

	public function testRemapKeysSubsetAndSizeZero(): void {
		$result = $this->executeCodeSnippet(
			"fn[a: 1, b: 2];",
			valueDeclarations: "fn = ^m: Map<String['a', 'b']:Integer, ..2> => Map<String<3>:Integer, ..2> :: 
				m->remapKeys(^k: String['a', 'b'] => String<3> :: 'abc');"
		);
		$this->assertEquals("[abc: 2]", $result);
	}

	public function testRemapKeysSubsetAndSize(): void {
		$result = $this->executeCodeSnippet(
			"fn[a: 1, b: 2];",
			valueDeclarations: "fn = ^m: Map<String['a', 'b']:Integer, 2> => Map<String<3>:Integer, 1..2> :: 
				m->remapKeys(^k: String['a', 'b'] => String<3> :: 'abc');"
		);
		$this->assertEquals("[abc: 2]", $result);
	}

	public function testRemapKeysInvalidParameterType(): void {
		$this->executeErrorCodeSnippet('Invalid parameter type', "[a: 1, b: 'a']->remapKeys(5);");
	}

	public function testRemapKeysInvalidParameterParameterType(): void {
		$this->executeErrorCodeSnippet("The parameter type Boolean of the callback function is not a supertype of String['a', 'b']",
			"[a: 1, b: 'a']->remapKeys(^Boolean => String :: '');");
	}

	public function testRemapKeysInvalidParameterParameterTypeString(): void {
		$this->executeErrorCodeSnippet("The parameter type String<4> of the callback function is not a supertype of String",
			"[a: 1, b: 'a']->remapKeys(^String<4> => String :: '');");
	}

	public function testRemapKeysInvalidParameterReturnType(): void {
		$this->executeErrorCodeSnippet("The return type Boolean of the callback function is not a subtype of String",
			"[a: 1, b: 'a']->remapKeys(^String => Boolean :: true);");
	}

}