<?php

namespace Walnut\Lang\Test\NativeCode\Array;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class IndexByTest extends CodeExecutionTestHelper {

	public function testIndexByEmpty(): void {
		$result = $this->executeCodeSnippet(
			"getMap([])",
			valueDeclarations: "getMap = ^arr: Array<Integer, ..5> => Map<String<3>:Integer> :: arr->indexBy(^Any => String['key'] :: 'key');"
		);
		$this->assertEquals("[:]", $result);
	}

	public function testIndexByRange(): void {
		$result = $this->executeCodeSnippet(
			"getMap([1, 2, 3, 4])",
			valueDeclarations: "getMap = ^arr: Array<Integer, 4..7> => Map<String['key']:Integer, 1..7> :: arr->indexBy(^Integer => String['key'] :: 'key');"
		);
		$this->assertEquals("[key: 4]", $result);
	}

	public function testIndexByStringRange(): void {
		$result = $this->executeCodeSnippet(
			"getMap([1, 2, 3, 4])",
			valueDeclarations: "getMap = ^arr: Array<Integer<0..9>, 2..> => Map<String<1>:Integer<0..9>, 1..> :: arr->indexBy(^i: Integer<0..9> => String<1> :: i->asString);"
		);
		$this->assertEquals("[1: 1, 2: 2, 3: 3, 4: 4]", $result);
	}

	public function testIndexBySimple(): void {
		$result = $this->executeCodeSnippet(
			"[1, 2, 3]->indexBy(^i: Integer => String :: 'key' + i->asString);"
		);
		$this->assertEquals("[key1: 1, key2: 2, key3: 3]", $result);
	}

	public function testIndexByWithDuplicateKeys(): void {
		// Later values should overwrite earlier ones with same key - simplify to just test overwriting
		$result = $this->executeCodeSnippet(
			"[0, 1, 2, 3]->indexBy(^i: Integer => String :: {i % 3}->asString);"
		);
		$this->assertEquals("[0: 3, 1: 1, 2: 2]", $result);
	}

	public function testIndexByWithStrings(): void {
		$result = $this->executeCodeSnippet(
			"['a', 'b', 'c']->indexBy(^s: String => String :: s);"
		);
		$this->assertEquals("[a: 'a', b: 'b', c: 'c']", $result);
	}

	public function testIndexByWithRecords(): void {
		$result = $this->executeCodeSnippet(
			"[[id: 'a', name: 'Alice'], [id: 'b', name: 'Bob']]->indexBy(^[id: String, name: String] => String :: #id);"
		);
		$this->assertStringContainsString("a: [id: 'a', name: 'Alice']", $result);
		$this->assertStringContainsString("b: [id: 'b', name: 'Bob']", $result);
	}

	public function testIndexByTypeConstraint(): void {
		$result = $this->executeCodeSnippet(
			"[1, 2, 3]->indexBy(^i: Integer => String :: 'k' + i->asString);"
		);
		$this->assertEquals("[k1: 1, k2: 2, k3: 3]", $result);
	}

	public function testIndexByMinLengthZero(): void {
		$result = $this->executeCodeSnippet(
			"getMap([])",
			valueDeclarations: "getMap = ^arr: Array<Integer> => Map<String:Integer> :: arr->indexBy(^Integer => String :: 'key' + #->asString);"
		);
		$this->assertEquals("[:]", $result);
	}

	public function testIndexByInvalidParameterType(): void {
		$this->executeErrorCodeSnippet('Invalid parameter type', "[1, 2]->indexBy(5);");
	}

	public function testIndexByInvalidParameterParameterType(): void {
		$this->executeErrorCodeSnippet("The parameter type (Integer[1]|String['a']) of the callback function is not a subtype of Boolean",
			"[1, 'a']->indexBy(^Boolean => String :: 'key');");
	}

	public function testIndexByInvalidReturnType(): void {
		$this->executeErrorCodeSnippet("The return type of the callback function must be a subtype of String",
			"[1, 2]->indexBy(^Any => Integer :: 5);");
	}

}
