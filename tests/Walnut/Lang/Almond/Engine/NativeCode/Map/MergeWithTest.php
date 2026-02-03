<?php

namespace Walnut\Lang\Test\Almond\Engine\NativeCode\Map;

use Walnut\Lang\Test\Almond\Engine\CodeExecutionTestHelper;

final class MergeWithTest extends CodeExecutionTestHelper {

	public function testMergeWithEmpty(): void {
		$result = $this->executeCodeSnippet("[:]->mergeWith([:]);");
		$this->assertEquals("[:]", $result);
	}

	public function testMergeWithNonEmpty(): void {
		$result = $this->executeCodeSnippet("[a: 1, b: 2]->mergeWith[b: 3, c: 4];");
		$this->assertEquals("[a: 1, b: 3, c: 4]", $result);
	}

	public function testMergeWithKeyType(): void {
		$result = $this->executeCodeSnippet(
			"fn[a: 1, b: 2];",
			valueDeclarations: "fn = ^m: Map<String<1>:Integer> => Map<String<1..2>:Integer> :: 
				m->mergeWith[cd: 3];"
		);
		$this->assertEquals("[a: 1, b: 2, cd: 3]", $result);
	}

	public function testMergeWithKeyTypeStringSubset(): void {
		$result = $this->executeCodeSnippet(
			"fn[a: 1, b: 2];",
			valueDeclarations: "fn = ^m: Map<String['a', 'b']:Integer> => Map<String['a', 'b', 'cd']:Integer> :: 
				m->mergeWith[cd: 3];"
		);
		$this->assertEquals("[a: 1, b: 2, cd: 3]", $result);
	}

	public function testMergeWithInvalidType(): void {
		$this->executeErrorCodeSnippet('Invalid parameter type', "[a: 1, b: 2]->mergeWith(3);");
	}
}