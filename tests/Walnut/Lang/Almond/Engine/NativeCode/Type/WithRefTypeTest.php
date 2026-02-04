<?php

namespace Walnut\Lang\Test\Almond\Engine\NativeCode\Type;

use Walnut\Lang\Test\Almond\Engine\CodeExecutionTestHelper;

final class WithRefTypeTest extends CodeExecutionTestHelper {

	public function testWithRefTypeType(): void {
		$result = $this->executeCodeSnippet("type{Type<String>}->withRefType(type{Integer});");
		$this->assertEquals("type{Type<Integer>}", $result);
	}

	public function testWithRefTypeShape(): void {
		$result = $this->executeCodeSnippet("type{Shape<String>}->withRefType(type{Integer});");
		$this->assertEquals("type{Shape<Integer>}", $result);
	}

	public function testWithRefTypeMetaType(): void {
		$result = $this->executeCodeSnippet("getWithRefType(type{Type<String>});",
			valueDeclarations: "getWithRefType = ^Type<Type> => Type :: #->withRefType(type{Integer});");
		$this->assertEquals("type{Type<Integer>}", $result);
	}

	public function testWithRefTypeInvalidTargetType(): void {
		$this->executeErrorCodeSnippet('Invalid target type',
			"type{String}->withRefType(type{Integer});");
	}

	public function testWithRefTypeInvalidParameterType(): void {
		$this->executeErrorCodeSnippet('Invalid parameter type',
			"type{Type<String>}->withRefType(42);");
	}

}