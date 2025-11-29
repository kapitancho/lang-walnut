<?php

namespace Walnut\Lang\Test\NativeCode\Type;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class RefTypeTest extends CodeExecutionTestHelper {

	public function testRefType(): void {
		$result = $this->executeCodeSnippet("type{Type<String>}->refType;");
		$this->assertEquals("type{String}", $result);
	}

	public function testRefTypeShape(): void {
		$result = $this->executeCodeSnippet("type{Shape<String>}->refType;");
		$this->assertEquals("type{String}", $result);
	}

	public function testRefTypeMetaType(): void {
		$result = $this->executeCodeSnippet("getRefType(type{Type<String>});",
			valueDeclarations: "getRefType = ^Type<Type> => Type :: #->refType;");
		$this->assertEquals("type{String}", $result);
	}

	public function testRefTypeInvalidTargetType(): void {
		$this->executeErrorCodeSnippet('Invalid target type', "type{String}->refType;");
	}

}