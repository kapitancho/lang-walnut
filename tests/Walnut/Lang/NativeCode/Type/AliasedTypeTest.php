<?php

namespace Walnut\Lang\Test\NativeCode\Type;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class AliasedTypeTest extends CodeExecutionTestHelper {

	public function testAliasedType(): void {
		$result = $this->executeCodeSnippet("type{MyAliasedType}->aliasedType;", "MyAliasedType = String;");
		$this->assertEquals("type{String}", $result);
	}

	public function testAliasedTypeMetaType(): void {
		$result = $this->executeCodeSnippet(
			"getAliasedType(type{MyAliasedType});",
			"MyAliasedType = String;",
			"getAliasedType = ^Type<Alias> => Type :: #->aliasedType;",
		);
		$this->assertEquals("type{String}", $result);
	}

}