<?php

namespace Walnut\Lang\Test\NativeCode\Map;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class ItemTest extends CodeExecutionTestHelper {

	/*public function testItemEmpty(): void {
		$this->executeErrorCodeSnippet(
			"No property exists that matches the type",
			"getItem('r');",
			valueDeclarations:  "getItem = ^s: String :: [:]->item(s);"
		);
	}*/

	public function testItemNonEmpty(): void {
		$result = $this->executeCodeSnippet("[a: 1, b: 2]->item('b');");
		$this->assertEquals("2", $result);
	}

	public function testItemIntersectionType(): void {
		$result = $this->executeCodeSnippet(
			"getItem[a: 'hello', b: 2, c: true];",
			valueDeclarations: "getItem = ^m: [a: String, b: Real, ...]&[b: Integer, c: Boolean, ...] => Integer :: m->item('b');"
		);
		$this->assertEquals("2", $result);
	}

	public function testItemOptionalKey(): void {
		$result = $this->executeCodeSnippet(
			"getItem[a: 'hello', b: 2];",
			valueDeclarations: "getItem = ^m: [a: String, b: ?Real] => Result<Real, MapItemNotFound> :: m->item('b');"
		);
		$this->assertEquals("2", $result);
	}

	public function testItemOptionalKeyMissing(): void {
		$result = $this->executeCodeSnippet(
			"getItem[a: 'hello'];",
			valueDeclarations: "getItem = ^m: [a: String, b: ?Real] => Result<Real, MapItemNotFound> :: m->item('b');"
		);
		$this->assertEquals("@MapItemNotFound![key: 'b']", $result);
	}

	public function testItemMetaType(): void {
		$result = $this->executeCodeSnippet(
			"getItem[a: 1, b: 2];",
			valueDeclarations: "getItem = ^m: Record :: m->item('b');"
		);
		$this->assertEquals("2", $result);
	}

	public function testItemMetaTypeMissing(): void {
		$result = $this->executeCodeSnippet(
			"getItem[a: 1, b: 2];",
			valueDeclarations: "getItem = ^m: Record :: m->item('c');"
		);
		$this->assertEquals("@MapItemNotFound![key: 'c']", $result);
	}

	public function testItemNonEmptyIndexOutOfRange(): void {
		$result = $this->executeCodeSnippet("getItem('r');", valueDeclarations: "getItem = ^s: String :: [a: 1, b: 2, c: 5, d: 10, e: 5]->item(s);");
		$this->assertEquals("@MapItemNotFound![key: 'r']", $result);
	}

	public function testItemIntersection(): void {
		$result = $this->executeCodeSnippet("getSb[a: 'hello', b: 10, c: 15, d: false];",
			"
				R = [a: String, b: Integer, c: Integer<5..20>, ... Any];
				Q = [b: Real, c: Integer<10..30>, d: Boolean, ... Any];
				S = R & Q;
			",
			"getSb = ^ ~S => Integer :: s.b;");
		$this->assertEquals("10", $result);
	}

	public function testItemInvalidKeyType(): void {
		$this->executeErrorCodeSnippet(
			"Map<String<2>:Real> expected",
			"f[xy: 3.14, wrong: -1.25];",
			valueDeclarations: "f = ^m: Map<String<2>:Real> => Real :: m->values->sum;");
	}

	public function testItemInvalidParameterValue(): void {
		$this->executeErrorCodeSnippet("Invalid parameter type",
			"[a: 'a', b: 1, c: 2]->item(5)");
	}

	public function testItemTypeWithRestSubset(): void {
		$result = $this->executeCodeSnippet("getValue[a: 6, b: true, c: false];",
			"MyRecord = [a: Integer, ... Boolean];",
			"getValue = ^t: MyRecord => Integer :: t.a;"
		);
		$this->assertEquals("6", $result);
	}

}