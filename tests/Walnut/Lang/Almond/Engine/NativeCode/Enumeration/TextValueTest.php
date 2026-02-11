<?php

namespace Walnut\Lang\Test\Almond\Engine\NativeCode\Enumeration;

use Walnut\Lang\Test\Almond\Engine\CodeExecutionTestHelper;

final class TextValueTest extends CodeExecutionTestHelper {

	public function testTextValue(): void {
		$result = $this->executeCodeSnippet("MyEnum.A->textValue;", 'MyEnum := (A, B, C);');
		$this->assertEquals("'A'", $result);
	}

	public function testTextValueMetaTypeValue(): void {
		$result = $this->executeCodeSnippet("getTextValue(MyEnum.A);",
			'MyEnum := (A, B, C);',
			'getTextValue = ^Enumeration => String<1..> :: #->textValue;'
		);
		$this->assertEquals("'A'", $result);
	}

	public function testTextValueInvalidParameterType(): void {
		$this->executeErrorCodeSnippet('Invalid parameter type', "MyEnum.A->textValue(3);", 'MyEnum := (A, B, C);');
	}

}