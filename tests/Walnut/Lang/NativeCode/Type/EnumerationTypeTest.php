<?php

namespace Walnut\Lang\Test\NativeCode\Type;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class EnumerationTypeTest extends CodeExecutionTestHelper {

	public function testEnumerationType(): void {
		$result = $this->executeCodeSnippet("type{MyEnumerationType[A, C]}->enumerationType;", "MyEnumerationType := (A, B, C);");
		$this->assertEquals("type{MyEnumerationType}", $result);
	}

	public function testEnumerationTypeMetaTypeSubset(): void {
		$result = $this->executeCodeSnippet(
			"getEnumerationType(type{MyEnumerationType[A, C]});",
			"MyEnumerationType := (A, B, C);",
			"getEnumerationType = ^Type<EnumerationSubset> => Type :: #->enumerationType;"
		);
		$this->assertEquals("type{MyEnumerationType}", $result);
	}

	public function testEnumerationTypeMetaTypeValue(): void {
		$result = $this->executeCodeSnippet(
			"getEnumerationType(MyEnumerationType.A->type);",
			"MyEnumerationType := (A, B, C);",
			"getEnumerationType = ^Type<Enumeration> => Type :: #->enumerationType;"
		);
		$this->assertEquals("type{MyEnumerationType}", $result);
	}

}