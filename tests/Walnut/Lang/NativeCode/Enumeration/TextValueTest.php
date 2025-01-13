<?php

namespace Walnut\Lang\NativeCode\Enumeration;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class TextValueTest extends CodeExecutionTestHelper {

	public function testTextValue(): void {
		$result = $this->executeCodeSnippet("MyEnum.A->textValue;", 'MyEnum = :[A, B, C];');
		$this->assertEquals("'A'", $result);
	}

	public function testTextValueInvalidParameterType(): void {
		$this->executeErrorCodeSnippet('Invalid parameter type', "MyEnum.A->textValue(3);", 'MyEnum = :[A, B, C];');
	}

}