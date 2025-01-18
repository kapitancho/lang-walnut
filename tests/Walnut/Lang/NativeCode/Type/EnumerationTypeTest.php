<?php

namespace Walnut\Lang\NativeCode\Type;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class EnumerationTypeTest extends CodeExecutionTestHelper {

	public function testEnumerationType(): void {
		$result = $this->executeCodeSnippet("type{MyEnumerationType[A, C]}->enumerationType;", "MyEnumerationType = :[A, B, C];");
		$this->assertEquals("type{MyEnumerationType}", $result);
	}

	public function testEnumerationTypeMetaType(): void {
		$result = $this->executeCodeSnippet("getEnumerationType(type{MyEnumerationType[A, C]});",
			"MyEnumerationType = :[A, B, C]; getEnumerationType = ^Type<EnumerationSubset> => Type :: #->enumerationType;");
		$this->assertEquals("type{MyEnumerationType}", $result);
	}

}