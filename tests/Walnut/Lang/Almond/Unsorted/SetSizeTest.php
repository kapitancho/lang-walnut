<?php

namespace Walnut\Lang\Test\Almond\Unsorted;

use Walnut\Lang\Test\Almond\Engine\CodeExecutionTestHelper;

final class SetSizeTest extends CodeExecutionTestHelper {

	public function testSetSizeDynamic(): void {
		$result = $this->executeCodeSnippet("getSet[1, 3, 4, 3];", valueDeclarations: <<<NUT
			getSet = ^[a: Integer, b: Integer, c: Integer, d: Integer] => Set<Integer, 1..4> :: [#a; #b; #c; #d];
		NUT);
		$this->assertEquals("[1; 3; 4]", $result);
	}

	public function testSetSizeMixed(): void {
		$result = $this->executeCodeSnippet("getSet[1, 3, 4];", valueDeclarations: <<<NUT
			getSet = ^[a: Integer, b: Integer, c: Integer] => Set<Integer, 2..4> :: [#a; #b; 1; 5; 1];
		NUT);
		$this->assertEquals("[1; 3; 5]", $result);
	}

	public function testSetSizeConstant(): void {
		$result = $this->executeCodeSnippet("getSet();", valueDeclarations: <<<NUT
			getSet = ^ => Set<Integer, 3..3> :: [1; 3; 1; 5; 1];
		NUT);
		$this->assertEquals("[1; 3; 5]", $result);
	}

}