<?php

namespace Walnut\Lang\Test\Almond\Engine\NativeCode\Mutable;

use Walnut\Lang\Test\Almond\Engine\CodeExecutionTestHelper;

final class RemapKeysTest extends CodeExecutionTestHelper {

	public function testRemapKeysEmpty(): void {
		$result = $this->executeCodeSnippet("mutable{Map, [:]}->REMAPKEYS(^s: String => String :: s + 'x');");
		$this->assertEquals("mutable{Map, [:]}", $result);
	}

	public function testRemapKeysNonEmpty(): void {
		$result = $this->executeCodeSnippet("mutable{Map, [a: 1, b: 2, c: 5, d: 10, e: 5]}->REMAPKEYS(^s: String => String :: s + 'x');");
		$this->assertEquals("mutable{Map, [ax: 1, bx: 2, cx: 5, dx: 10, ex: 5]}", $result);
	}

	public function testRemapKeysSubsetAndSizeZero(): void {
		$result = $this->executeCodeSnippet(
			"fn(mutable{Map<String['a', 'b']:Integer, ..2>, [a: 1, b: 2]});",
			valueDeclarations: "fn = ^m: Mutable<Map<String['a', 'b']:Integer, ..2>> => Mutable<Map<String['a', 'b']:Integer, ..2>> ::
				m->REMAPKEYS(^k: String['a', 'b', 'c'] => String['a'] :: 'a');"
		);
		$this->assertEquals("mutable{Map<String['a', 'b']:Integer, ..2>, [a: 2]}", $result);
	}

	public function testRemapKeysSubsetAndSize(): void {
		$result = $this->executeCodeSnippet(
			"fn(mutable{Map<String['a', 'b']:Integer, 1..2>, [a: 1, b: 2]});",
			valueDeclarations: "fn = ^m: Mutable<Map<String['a', 'b']:Integer, 1..2>> => Mutable<Map<String['a', 'b']:Integer, 1..2>> ::
				m->REMAPKEYS(^k: String['a', 'b'] => String['a', 'b'] :: k);"
		);
		$this->assertEquals("mutable{Map<String['a', 'b']:Integer, 1..2>, [a: 1, b: 2]}", $result);
	}

	public function testRemapKeysInvalidTargetType(): void {
		$this->executeErrorCodeSnippet('Invalid target type: REMAPKEYS can only be used on maps with a minimum size of 0 or 1',
			"mutable{Map<3..5>, [a: 1, b: 'a', c: false]}->REMAPKEYS(^s: String => String :: s);");
	}

	public function testRemapKeysInvalidParameterType(): void {
		$this->executeErrorCodeSnippet('Invalid parameter type', "mutable{Map, [a: 1, b: 'a']}->REMAPKEYS(5);");
	}

	public function testRemapKeysInvalidParameterParameterType(): void {
		$this->executeErrorCodeSnippet("The parameter type Boolean of the callback function is not a supertype of String",
			"mutable{Map, [a: 1, b: 'a']}->REMAPKEYS(^Boolean => String :: '');");
	}

	public function testRemapKeysInvalidParameterParameterTypeString(): void {
		$this->executeErrorCodeSnippet("The parameter type String<4> of the callback function is not a supertype of String",
			"mutable{Map, [a: 1, b: 'a']}->REMAPKEYS(^String<4> => String :: '');");
	}

	public function testRemapKeysInvalidParameterReturnType(): void {
		$this->executeErrorCodeSnippet("The return type Boolean of the callback function is not a subtype of String",
			"mutable{Map, [a: 1, b: 'a']}->REMAPKEYS(^String => Boolean :: true);");
	}

	public function testRemapKeysReturnTypeMustMatchKeyType(): void {
		$this->executeErrorCodeSnippet("The return type String<3> of the callback function is not a subtype of String<1>",
			"mutable{Map<String<1>:Integer>, [a: 1, b: 2]}->REMAPKEYS(^String<1> => String<3> :: 'abc');");
	}

}
