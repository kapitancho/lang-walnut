<?php

namespace Walnut\Lang\Test\Almond\Engine\Implementation\Expression;

use Walnut\Lang\Test\Almond\Engine\CodeExecutionTestHelper;

final class DataExpressionTest extends CodeExecutionTestHelper {

	public function testData(): void {
		$result = $this->executeCodeSnippet("MyData!42;", "MyData := Integer;");
		$this->assertEquals("MyData!42", $result);
	}

	public function testDataNotASubtype(): void {
		$this->executeErrorCodeSnippet("Expression of type 'Real[3.14]' is not compatible with expected type 'MyData'.", "MyData!3.14;", "MyData := Integer;");
	}

	public function testDataUnknownType(): void {
		$this->executeErrorCodeSnippet("Type 'UnknownType' is not defined", "MyData!3.14;", "MyData := UnknownType;");
	}

	public function testDataUnknownDataType(): void {
		$this->executeErrorCodeSnippet("Data type 'OtherData' not found for data expression", "OtherData!3.14;", "MyData := Integer;");
	}

	public function testDataValueNotASubtype(): void {
		$this->executeErrorCodeSnippet("Expression of type 'Real[3.14]' is not compatible with expected type 'MyData'.",
			"v;",
			"MyData := Integer;",
			"v = MyData!3.14;"
		);
	}

}