<?php

namespace Walnut\Lang\NativeCode\Array;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class ProductTest extends CodeExecutionTestHelper {

	public function testProductEmpty(): void {
		$result = $this->executeCodeSnippet("[]->product;");
		$this->assertEquals("1", $result);
	}

	public function testProductNonEmpty(): void {
		$result = $this->executeCodeSnippet("[3, 2]->product;");
		$this->assertEquals("6", $result);
	}

	public function testProductNonEmptyReal(): void {
		$result = $this->executeCodeSnippet("[1.5, 3.14]->product;");
		$this->assertEquals("4.71", $result);
	}

	public function testProductReturnType(): void {
		$result = $this->executeCodeSnippet(
			"myProduct[1.6, 2];",
			valueDeclarations:  "myProduct = ^arr: Array<Real<1.4..5.1>, 2..3> => Real<(0..)> :: arr->product;"
		);
		$this->assertEquals("3.2", $result);
	}

	public function testProductInvalidType(): void {
		$this->executeErrorCodeSnippet('Invalid target type', "['hello','world', 'hi', 'hello']->product;");
	}
}