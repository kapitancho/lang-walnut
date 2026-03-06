<?php

namespace Walnut\Lang\Test\Almond\Engine\NativeCode\String;

use Walnut\Lang\Test\Almond\Engine\CodeExecutionTestHelper;

final class AsUuidTest extends CodeExecutionTestHelper {

	public function testAsUuidValid(): void {
		$result = $this->executeCodeSnippet("'00000000-0000-4000-9000-000000000000'->as(`Uuid);");
		$this->assertEquals("Uuid('00000000-0000-4000-9000-000000000000')", $result);
	}

	public function testAsUuidInvalid(): void {
		$result = $this->executeCodeSnippet("'00000000-0000-4000-0000-000000000000'->as(`Uuid);");
		$this->assertEquals("@InvalidUuid![\n\tvalue: '00000000-0000-4000-0000-000000000000'\n]", $result);
	}

	public function testAsUuidTypeSafe(): void {
		$result = $this->executeCodeSnippet(
			"getUuid('00000000-0000-4000-9000-000000000000');",
			valueDeclarations: "getUuid = ^a: String['00000000-0000-4000-9000-000000000000', '00000000-0000-4000-9000-000000000222'] => Uuid :: a->as(`Uuid);"
		);
		$this->assertEquals("Uuid('00000000-0000-4000-9000-000000000000')", $result);
	}

	public function testAsUuidTypeUnsafeOk(): void {
		$result = $this->executeCodeSnippet(
			"getUuid('00000000-0000-4000-9000-000000000000');",
			valueDeclarations: "getUuid = ^a: String => Result<Uuid, InvalidUuid> :: a->as(`Uuid);"
		);
		$this->assertEquals("Uuid('00000000-0000-4000-9000-000000000000')", $result);
	}

	public function testAsUuidTypeUnsafeError(): void {
		$result = $this->executeCodeSnippet(
			"getUuid('00000000-0000-4000-0000-000000000000');",
			valueDeclarations: "getUuid = ^a: String => Result<Uuid, InvalidUuid> :: a->as(`Uuid);"
		);
		$this->assertEquals("@InvalidUuid![\n\tvalue: '00000000-0000-4000-0000-000000000000'\n]", $result);
	}

	public function testAsUuidInvalidParameterType(): void {
		$this->executeErrorCodeSnippet('Invalid parameter type', "'00000000-0000-4000-9000-000000000000'->asUuid(5);");
	}

}
