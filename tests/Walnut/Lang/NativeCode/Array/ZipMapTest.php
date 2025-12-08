<?php

namespace Walnut\Lang\NativeCode\Array;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class ZipMapTest extends CodeExecutionTestHelper {

	public function testZipMapEmptyEmpty(): void {
		$result = $this->executeCodeSnippet("[]->zipMap([]);");
		$this->assertEquals("[:]", $result);
	}

	public function testZipMapEmptyNonEmpty(): void {
		$result = $this->executeCodeSnippet("[]->zipMap[1];");
		$this->assertEquals("[:]", $result);
	}

	public function testZipMapNonEmptyEmpty(): void {
		$result = $this->executeCodeSnippet("['a']->zipMap([]);");
		$this->assertEquals("[:]", $result);
	}

	public function testZipMapNonEmptyEqualSize(): void {
		$result = $this->executeCodeSnippet("['a', 'bcd', 'ef']->zipMap[1, 3, 2];");
		$this->assertEquals("[a: 1, bcd: 3, ef: 2]", $result);
	}

	public function testZipMapNonEmptyShorterParam(): void {
		$result = $this->executeCodeSnippet("['a', 'bcd', 'ef', 'ghij']->zipMap[1, 3, 2];");
		$this->assertEquals("[a: 1, bcd: 3, ef: 2]", $result);
	}

	public function testZipMapNonEmptyLongerParam(): void {
		$result = $this->executeCodeSnippet("['a', 'bcd', 'ef']->zipMap[1, 3, 2, 4];");
		$this->assertEquals("[a: 1, bcd: 3, ef: 2]", $result);
	}

	public function testZipMapReturnTypeFinite(): void {
		$result = $this->executeCodeSnippet("zipMap[['a', 'bcd', 'ef'], [1, 3, 2]]",
			valueDeclarations: "
				zipMap = ^p: [Array<String<1..3>, 2..5>, Array<Integer<0..10>, 3..6>] => Map<String<1..3>:Integer<0..10>, 1..5> :: 
					p.0->zipMap(p.1);
			"
		);
		$this->assertEquals("[a: 1, bcd: 3, ef: 2]", $result);
	}

	public function testZipMapReturnTypeInfiniteTarget(): void {
		$result = $this->executeCodeSnippet("zipMap[['a', 'bcd', 'ef'], [1, 3, 2]]",
			valueDeclarations: "
				zipMap = ^p: [Array<String<1..3>, 2..>, Array<Integer<0..10>, 3..6>] => Map<String<1..3>:Integer<0..10>, 1..6> :: 
					p.0->zipMap(p.1);
			"
		);
		$this->assertEquals("[a: 1, bcd: 3, ef: 2]", $result);
	}

	public function testZipMapReturnTypeInfiniteParameter(): void {
		$result = $this->executeCodeSnippet("zipMap[['a', 'bcd', 'ef'], [1, 3, 2]]",
			valueDeclarations: "
				zipMap = ^p: [Array<String<1..3>, ..5>, Array<Integer<0..10>, 3..>] => Map<String<1..3>:Integer<0..10>, ..5> :: 
					p.0->zipMap(p.1);
			"
		);
		$this->assertEquals("[a: 1, bcd: 3, ef: 2]", $result);
	}

	public function testZipMapInvalidTargetType(): void {
		$this->executeErrorCodeSnippet('Invalid target type', "[1, 'a']->zipMap[1, 2];");
	}

	public function testZipMapInvalidParameterType(): void {
		$this->executeErrorCodeSnippet('Invalid parameter type', "['a', 'bcd', 'ef']->zipMap(5);");
	}

}