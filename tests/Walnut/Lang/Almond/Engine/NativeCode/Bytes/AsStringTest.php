<?php

namespace Walnut\Lang\Test\Almond\Engine\NativeCode\Bytes;

use Walnut\Lang\Test\Almond\Engine\CodeExecutionTestHelper;

final class AsStringTest extends CodeExecutionTestHelper {

	public function testAsStringValid(): void {
		$result = $this->executeCodeSnippet('"hello"->asString;');
		$this->assertEquals("'hello'", $result);
	}

	public function testAsStringInvalid(): void {
		$result = $this->executeCodeSnippet('"\FF"->asString;');
		$this->assertEquals("@InvalidString", $result);
	}

	public function testAsStringType(): void {
		$result = $this->executeCodeSnippet('asStr("hello");',
			valueDeclarations: "asStr = ^b: Bytes => Result<String, InvalidString> :: b->as(`String);"
		);
		$this->assertEquals("'hello'", $result);
	}

	public function testAsStringLengthRange(): void {
		$result = $this->executeCodeSnippet('asStr("hello");',
			valueDeclarations: "asStr = ^b: Bytes<5..16> => Result<String<2..16>, InvalidString> :: b->as(`String);"
		);
		$this->assertEquals("'hello'", $result);
	}

	public function testAsStringInvalidParameterType(): void {
		$this->executeErrorCodeSnippet(
			"Invalid parameter type",
			'"hello"->asString(1);'
		);
	}

}