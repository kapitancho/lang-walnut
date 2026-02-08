<?php

namespace Walnut\Lang\Test\Almond\Engine\NativeCode\Map;

use Walnut\Lang\Test\Almond\Engine\CodeExecutionTestHelper;

final class FindFirstKeyValueTest extends CodeExecutionTestHelper {

	public function testFindFirstKeyValueEmpty(): void {
		$result = $this->executeCodeSnippet("[:]->findFirstKeyValue(^Any => Boolean :: true);");
		$this->assertEquals("@ItemNotFound", $result);
	}

	public function testFindFirstKeyValueNonEmpty(): void {
		$result = $this->executeCodeSnippet("[a: 1, b: 2, c: 5, d: 10, e: 5]->findFirstKeyValue(^[key: String, value: Integer] => Boolean :: {#value > 4} || {#key == 'e'});");
		$this->assertEquals("[key: 'c', value: 5]", $result);
	}


	public function testFindFirstKeyValueKeyType(): void {
		$result = $this->executeCodeSnippet(
			"fn[a: 1, b: 2];",
			valueDeclarations: "fn = ^m: Map<String<1>:Integer> => Result<[key: String<1>, value: Integer], ItemNotFound> :: 
				m->findFirstKeyValue(^[key: String<1>, value: Integer] => Boolean :: #value > #key->length);"
		);
		$this->assertEquals("[key: 'b', value: 2]", $result);
	}

	public function testFindFirstKeyValueInvalidParameterType(): void {
		$this->executeErrorCodeSnippet('Invalid parameter type', "[a: 1, b: 'a']->findFirstKeyValue(5);");
	}

	public function testFindFirstKeyValueInvalidParameterParameterType(): void {
		$this->executeErrorCodeSnippet("The parameter type Boolean of the callback function is not a subtype",
			"[a: 1, b: 'a']->findFirstKeyValue(^Boolean => Boolean :: true);");
	}

	public function testFindFirstKeyValueInvalidParameterReturnType(): void {
		$this->executeErrorCodeSnippet("Invalid parameter type",
			"[a: 1, b: 'a']->findFirstKeyValue(^Any => Real :: 3.14);");
	}

}