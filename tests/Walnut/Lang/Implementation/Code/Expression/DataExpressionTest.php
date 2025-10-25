<?php

namespace Walnut\Lang\Test\Implementation\Code\Expression;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class DataExpressionTest extends CodeExecutionTestHelper {

	public function testData(): void {
		$result = $this->executeCodeSnippet("MyData!42;", "MyData := Integer;");
		$this->assertEquals("MyData!42", $result);
	}

	public function testDataNotASubtype(): void {
		$this->executeErrorCodeSnippet("expected base value of type 'Integer'", "MyData!3.14;", "MyData := Integer;");
	}

	public function testDataValueNotASubtype(): void {
		$this->executeErrorCodeSnippet("expected base value of type 'Integer', but got 'Real[3.14]",
			"v;",
			"MyData := Integer;",
			"v = MyData!3.14;"
		);
	}

}