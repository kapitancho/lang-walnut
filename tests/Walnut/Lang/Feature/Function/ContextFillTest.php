<?php

namespace Walnut\Lang\Test\Feature\Function;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class ContextFillTest extends CodeExecutionTestHelper {

	public function testAliasRecordAsTarget(): void {
		$result = $this->executeCodeSnippet("{[a: -9, b: 'Hello']}->test;", <<<NUT
		MyAlias = [a: Integer, b: ?String, c: ?Boolean];
		MyAlias->test() :: [\$a, \$b, \$c, \$];
	NUT);
		$this->assertEquals("[\n\t-9,\n\t'Hello',\n\t@MapItemNotFound![key: 'c'],\n\t[a: -9, b: 'Hello']\n]", $result);
	}

	public function testAliasRecordAsParameter(): void {
		$result = $this->executeCodeSnippet("test[a: -9, b: 'Hello'];", <<<NUT
		MyAlias = [a: Integer, b: ?String, c: ?Boolean];
	NUT, <<<NUT
		test = ^o: MyAlias :: [#a, #b, #c, #];
	NUT);
		$this->assertEquals("[\n\t-9,\n\t'Hello',\n\t@MapItemNotFound![key: 'c'],\n\t[a: -9, b: 'Hello']\n]", $result);
	}

	public function testOpenRecordAsTarget(): void {
		$result = $this->executeCodeSnippet("{MyOpen[a: -9, b: 'Hello']}->test;", <<<NUT
		MyOpen := #[a: Integer, b: ?String, c: ?Boolean];
		MyOpen->test() :: [\$a, \$b, \$c, \$\$];
	NUT);
		$this->assertEquals("[\n\t-9,\n\t'Hello',\n\t@MapItemNotFound![key: 'c'],\n\t[a: -9, b: 'Hello']\n]", $result);
	}

	public function testOpenRecordAsParameter(): void {
		$result = $this->executeCodeSnippet("test(MyOpen[a: -9, b: 'Hello']);", <<<NUT
		MyOpen := #[a: Integer, b: ?String, c: ?Boolean];
	NUT, <<<NUT
		test = ^o: MyOpen :: [#a, #b, #c, #->value];
	NUT);
		$this->assertEquals("[\n\t-9,\n\t'Hello',\n\t@MapItemNotFound![key: 'c'],\n\t[a: -9, b: 'Hello']\n]", $result);
	}

	public function testSealedRecordAsTarget(): void {
		$result = $this->executeCodeSnippet("{MySealed[a: -9, b: 'Hello']}->test;", <<<NUT
		MySealed := $[a: Integer, b: ?String, c: ?Boolean];
		MySealed->test() :: [\$a, \$b, \$c, \$\$];
	NUT);
		$this->assertEquals("[\n\t-9,\n\t'Hello',\n\t@MapItemNotFound![key: 'c'],\n\t[a: -9, b: 'Hello']\n]", $result);
	}

	public function testSealedRecordAsParameter(): void {
		$this->executeErrorCodeSnippet(
			"Unknown variable '#a'",
			"test(MySealed[a: -9, b: 'Hello']);",
		<<<NUT
			MySealed := $[a: Integer, b: ?String, c: ?Boolean];
		NUT,
		<<<NUT
			test = ^s: MySealed :: [#a];
		NUT);
	}

}