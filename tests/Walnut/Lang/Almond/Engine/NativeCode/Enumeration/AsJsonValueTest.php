<?php

namespace Walnut\Lang\Test\Almond\Engine\NativeCode\Enumeration;

use Walnut\Lang\Test\Almond\Engine\CodeExecutionTestHelper;

final class AsJsonValueTest extends CodeExecutionTestHelper {

	public function testAsJsonValue(): void {
		$result = $this->executeCodeSnippet("MyEnum.A->asJsonValue;", 'MyEnum := (A, B, C);');
		$this->assertEquals("'A'", $result);
	}

	public function testAsJsonValueType(): void {
		$result = $this->executeCodeSnippet("asJ(MyEnum.B);",
			'MyEnum := (A, B, C);',
			'asJ = ^e: MyEnum => JsonValue :: e->as(`JsonValue);'
		);
		$this->assertEquals("'B'", $result);
	}

	public function testAsJsonValueInvalidParameterType(): void {
		$this->executeErrorCodeSnippet('Invalid parameter type', "MyEnum.A->asJsonValue(1);", 'MyEnum := (A, B, C);');
	}

}
