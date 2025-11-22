<?php

namespace Walnut\Lang\Test\NativeCode\Map;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class WithoutTest extends CodeExecutionTestHelper {

	public function testWithoutEmpty(): void {
		$result = $this->executeCodeSnippet("[:]->without('a');");
		$this->assertEquals("@ItemNotFound", $result);
	}

	public function testWithoutNotFound(): void {
		$result = $this->executeCodeSnippet("[a: 1, b: 2, c: 5, d: 10, e: 5]->without('r');");
		$this->assertEquals("@ItemNotFound", $result);
	}

	public function testWithoutNonEmpty(): void {
		$result = $this->executeCodeSnippet("[a: 1, b: 2, c: 5, d: 10, e: 5]->without(5);");
		$this->assertEquals("[a: 1, b: 2, d: 10, e: 5]", $result);
	}

	public function testWithoutKeyType(): void {
		$result = $this->executeCodeSnippet(
			"fn[a: 1, b: 2];",
			valueDeclarations: "fn = ^m: Map<String<1>:Integer> => Result<Map<String<1>:Integer>, ItemNotFound> :: 
				m->without(1);"
		);
		$this->assertEquals("[b: 2]", $result);
	}

	public function testWithoutKeyTypeStringSubset(): void {
		$result = $this->executeCodeSnippet(
			"fn[a: 1, b: 2];",
			valueDeclarations: "fn = ^m: Map<String['a', 'b']:Integer> => Result<Map<String['a', 'b']:Integer>, ItemNotFound> :: 
				m->without(1);"
		);
		$this->assertEquals("[b: 2]", $result);
	}

}