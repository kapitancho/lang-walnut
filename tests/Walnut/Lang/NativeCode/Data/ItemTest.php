<?php

namespace Walnut\Lang\Test\NativeCode\Data;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class ItemTest extends CodeExecutionTestHelper {

	public function testTupleItemOutOfRange(): void {
		$result = $this->executeCodeSnippet("{MyTuple![3, 5]}->item(4);", "MyTuple := [Integer, Real];");
		$this->assertEquals("@IndexOutOfRange![index: 4]", $result);
	}

	public function testTupleItemInRange(): void {
		$result = $this->executeCodeSnippet("{MyTuple![3, 5]}->item(1);", "MyTuple := [Integer, Real];");
		$this->assertEquals("5", $result);
	}

	public function testItemOptionalKey(): void {
		$result = $this->executeCodeSnippet(
			"getItem(MyTuple![a: 'hello', b: 2]);",
			"MyTuple := [a: String, b: ?Real];",
			"getItem = ^m: MyTuple => Result<Real, MapItemNotFound> :: m->item('b');"
		);
		$this->assertEquals("2", $result);
	}

	public function testItemOptionalKeyMissing(): void {
		$result = $this->executeCodeSnippet(
			"getItem(MyTuple![a: 'hello']);",
			"MyTuple := [a: String, b: ?Real];",
			valueDeclarations: "getItem = ^m: MyTuple => Result<Real, MapItemNotFound> :: m->item('b');"
		);
		$this->assertEquals("@MapItemNotFound![key: 'b']", $result);
	}

	public function testTupleItemTypeWithRestSubset(): void {
		$result = $this->executeCodeSnippet("getValue(MyTuple![6, true, false]);",
			"MyTuple := [Integer, ... Boolean];",
			"getValue = ^t: MyTuple => Integer :: t.0;"
		);
		$this->assertEquals("6", $result);
	}

	public function testTupleItemTypeParameterWithinLimit(): void {
		$result = $this->executeCodeSnippet("getValue(2);",
			"MyTuple := [Integer, Integer, Real];",
			"myTuple = MyTuple![6, -2, 3.14]; getValue = ^idx: Integer<1..2> => Real :: myTuple->item(idx);"
		);
		$this->assertEquals("3.14", $result);
	}

	public function testTupleItemTypeWithRestRange(): void {
		$result = $this->executeCodeSnippet("getValue[t: MyTuple![6, true, false], idx: 2];",
			"MyTuple := [Integer, ... Boolean];",
			"getValue = ^[t: MyTuple, idx: Integer<1..>] => Result<Boolean, IndexOutOfRange> :: #t->item(#idx);"
		);
		$this->assertEquals("false", $result);
	}

	public function testTupleItemTypeWithRestInteger(): void {
		$result = $this->executeCodeSnippet("getValue[t: MyTuple![6, true, false], idx: 2];",
			"MyTuple := [Integer, ... Boolean];",
			"getValue = ^[t: MyTuple, idx: Integer<0..>] => Result<Integer|Boolean, IndexOutOfRange> :: #t->item(#idx);"
		);
		$this->assertEquals("false", $result);
	}

	public function testRecordItemOutOfRange(): void {
		$result = $this->executeCodeSnippet("{MyRecord![a: 3, b: 5]}->item('d');", "MyRecord := [a: Integer, b: Real];");
		$this->assertEquals("@MapItemNotFound![key: 'd']", $result);
	}

	public function testRecordItemInRange(): void {
		$result = $this->executeCodeSnippet("{MyRecord![a: 3, b: 5]}->item('b');", "MyRecord := [a: Integer, b: Real];");
		$this->assertEquals("5", $result);
	}

	public function testRecordItemTypeWithRestSubset(): void {
		$result = $this->executeCodeSnippet("getValue(MyRecord![a: 6, b: true, c: false]);",
			"MyRecord := [a: Integer, ... Boolean];",
			"getValue = ^t: MyRecord => Integer :: t.a;"
		);
		$this->assertEquals("6", $result);
	}

	public function testRecordItemTypeWithRestRange(): void {
		$result = $this->executeCodeSnippet("getValue[t: MyRecord![a: 6, b: true, c: false], key: 'c'];",
			"MyRecord := [a: Integer, ... Boolean];",
			"getValue = ^[t: MyRecord, key: String['c', 'd', 'f']] => Result<Boolean, MapItemNotFound> :: #t->item(#key);"
		);
		$this->assertEquals("false", $result);
	}

	public function testRecordItemTypeWithRestString(): void {
		$result = $this->executeCodeSnippet("getValue[t: MyRecord![a: 6, b: true, c: false], key: 'c'];",
			"MyRecord := [a: Integer, ... Boolean];",
			"getValue = ^[t: MyRecord, key: String] => Result<Integer|Boolean, MapItemNotFound> :: #t->item(#key);"
		);
		$this->assertEquals("false", $result);
	}


	public function testItemArrayInvalidParameterType(): void {
		$this->executeErrorCodeSnippet(
			"Invalid parameter type: String['a']",
			"getItem(MyTuple!['hello', 2]);",
			"MyTuple := [String, Real];",
			"getItem = ^m: MyTuple => Result<Real, MapItemNotFound> :: m->item('a');"
		);
	}

	public function testItemMapInvalidParameterType(): void {
		$this->executeErrorCodeSnippet(
			"Invalid parameter type: Integer[42]",
			"getItem(MyRecord![a: 'hello', b: 2]);",
			"MyRecord := [a: String, b: ?Real];",
			"getItem = ^m: MyRecord => Result<Real, MapItemNotFound> :: m->item(42);"
		);
	}

}