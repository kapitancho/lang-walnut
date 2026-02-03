<?php

namespace Walnut\Lang\Test\Almond\Engine\NativeCode\Set;

use Walnut\Lang\Test\Almond\Engine\CodeExecutionTestHelper;

final class BinaryMultiplyTest extends CodeExecutionTestHelper {

	public function testBinaryMultiplyEmpty(): void {
		$result = $this->executeCodeSnippet("[;] * [;];");
		$this->assertEquals("[;]", $result);
	}

	public function testBinaryMultiplyEmptyLeft(): void {
		$result = $this->executeCodeSnippet("[;] * [1; 2];");
		$this->assertEquals("[;]", $result);
	}

	public function testBinaryMultiplyEmptyRight(): void {
		$result = $this->executeCodeSnippet("[1; 2] * [;];");
		$this->assertEquals("[;]", $result);
	}

	public function testBinaryMultiplyNonEmpty(): void {
		$result = $this->executeCodeSnippet("[1; 2] * [3; 4];");
		$this->assertEquals("[[1, 3]; [1, 4]; [2, 3]; [2, 4]]", $result);
	}

	public function testBinaryMultiplySingleElements(): void {
		$result = $this->executeCodeSnippet("[1;] * [2;];");
		$this->assertEquals("[[1, 2];]", $result);
	}

	public function testBinaryMultiplyMixedTypes(): void {
		$result = $this->executeCodeSnippet("['a'; 'b'] * [1; 2; 3];");
		$this->assertEquals("[\n\t['a', 1];\n\t['a', 2];\n\t['a', 3];\n\t['b', 1];\n\t['b', 2];\n\t['b', 3]\n]", $result);
	}

	public function testBinaryMultiplyBounds(): void {
		$result = $this->executeCodeSnippet(
			"fn[1; 2; 3];",
			valueDeclarations: "fn = ^s: Set<Integer, 3> => Set<[Integer, Integer], 9> :: s * [10; 20; 30];"
		);
		$this->assertEquals("[\n\t[1, 10];\n\t[1, 20];\n\t[1, 30];\n\t[2, 10];\n\t[2, 20];\n\t[2, 30];\n\t[3, 10];\n\t[3, 20];\n\t[3, 30]\n]", $result);
	}

	public function testBinaryMultiplyBoundsRange(): void {
		$result = $this->executeCodeSnippet(
			"fn[[1; 2], [10; 20]]",
			valueDeclarations: "fn = ^s: [Set<Integer, 2..3>, Set<Integer, 2..4>] => Set<[Integer, Integer], 4..12> :: s.0 * s.1;"
		);
		$this->assertEquals("[[1, 10]; [1, 20]; [2, 10]; [2, 20]]", $result);
	}

	public function testBinaryMultiplyInvalidParameterType(): void {
		$this->executeErrorCodeSnippet('Invalid parameter type', "[1; 2] * 5;");
	}

}
