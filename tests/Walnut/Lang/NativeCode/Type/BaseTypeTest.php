<?php

namespace Walnut\Lang\NativeCode\Type;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class BaseTypeTest extends CodeExecutionTestHelper {

	public function testBaseType(): void {
		$result = $this->executeCodeSnippet("type{MySubtype}->baseType;", "MySubtype <: String;");
		$this->assertEquals("type{String}", $result);
	}

	public function testBaseTypeMetaType(): void {
		$result = $this->executeCodeSnippet("getBaseType(type{MySubtype});", "MySubtype <: String; getBaseType = ^Type<Subtype> => Type :: #->baseType;");
		$this->assertEquals("type{String}", $result);
	}

}