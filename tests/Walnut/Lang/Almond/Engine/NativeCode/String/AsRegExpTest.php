<?php

namespace Walnut\Lang\Test\Almond\Engine\NativeCode\String;

use Walnut\Lang\Test\Almond\Engine\CodeExecutionTestHelper;

final class AsRegExpTest extends CodeExecutionTestHelper {

	public function testAsRegExpValid(): void {
		$result = $this->executeCodeSnippet("'/^hello ([a-z]+)/'->as(`RegExp);");
		$this->assertEquals("RegExp{'/^hello ([a-z]+)/'}", $result);
	}

	public function testAsRegExpInvalid(): void {
		$result = $this->executeCodeSnippet("'^he'->as(`RegExp);");
		$this->assertEquals("@InvalidRegExp![expression: '^he']", $result);
	}

	public function testAsRegExpTypeSafe(): void {
		$result = $this->executeCodeSnippet(
			"getRegExp('/^hello ([a-z]+)/');",
			valueDeclarations: "getRegExp = ^a: String['/^hello ([a-z]+)/', '/^welcome ([a-z]+)/'] => RegExp :: a->as(`RegExp);"
		);
		$this->assertEquals("RegExp{'/^hello ([a-z]+)/'}", $result);
	}

	public function testAsRegExpTypeUnsafeOk(): void {
		$result = $this->executeCodeSnippet(
			"getRegExp('/^hello ([a-z]+)/');",
			valueDeclarations: "getRegExp = ^a: String => Result<RegExp, InvalidRegExp> :: a->as(`RegExp);"
		);
		$this->assertEquals("RegExp{'/^hello ([a-z]+)/'}", $result);
	}

	public function testAsRegExpTypeUnsafeError(): void {
		$result = $this->executeCodeSnippet(
			"getRegExp('^he');",
			valueDeclarations: "getRegExp = ^a: String => Result<RegExp, InvalidRegExp> :: a->as(`RegExp);"
		);
		$this->assertEquals("@InvalidRegExp![expression: '^he']", $result);
	}

	public function testAsRegExpInvalidParameterType(): void {
		$this->executeErrorCodeSnippet('Invalid parameter type', "'/^hello ([a-z]+)/'->asRegExp(5);");
	}

}
