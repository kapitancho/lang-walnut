<?php

namespace Walnut\Lang\NativeCode\Enumeration;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class EnumerationTest extends CodeExecutionTestHelper {

	public function testEnumeration(): void {
		$result = $this->executeCodeSnippet("MyEnum.A->enumeration;", 'MyEnum := (A, B, C);');
		$this->assertEquals("type{MyEnum}", $result);
	}

	public function testEnumerationMetaTypeValue(): void {
		$result = $this->executeCodeSnippet("getEnumeration(MyEnum.A);",
			"MyEnum := (A, B, C); getEnumeration = ^EnumerationValue => Type<Enumeration> :: #->enumeration;");
		$this->assertEquals("type{MyEnum}", $result);
	}

}