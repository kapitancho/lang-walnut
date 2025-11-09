<?php

namespace Walnut\Lang\Test\NativeCode\Open;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class ItemTest extends CodeExecutionTestHelper {

	public function testTupleItemOutOfRange(): void {
		$result = $this->executeCodeSnippet("{MyTuple[3, 5]}->item(4);", "MyTuple := #[Integer, Real];");
		$this->assertEquals("@IndexOutOfRange![index: 4]", $result);
	}

	public function testTupleItemInRange(): void {
		$result = $this->executeCodeSnippet("{MyTuple[3, 5]}->item(1);", "MyTuple := #[Integer, Real];");
		$this->assertEquals("5", $result);
	}

	public function testTupleItemInteger(): void {
		$result = $this->executeCodeSnippet(
			"get(1);",
			"MyTuple := #[Integer, Real, String, Boolean];",
			"t = MyTuple[42, 3.14, 'hello', false]; get = ^idx: Integer<1..2> => Real|String :: t->item(idx);",
		);
		$this->assertEquals("3.14", $result);
	}

	public function testTupleItemTypeWithRestSubset(): void {
		$result = $this->executeCodeSnippet("getValue(MyTuple[6, true, false]);",
			"MyTuple := #[Integer, ... Boolean];",
			"getValue = ^t: MyTuple => Integer :: t.0;"
		);
		$this->assertEquals("6", $result);
	}

	public function testTupleItemTypeWithRestRange(): void {
		$result = $this->executeCodeSnippet("getValue[t: MyTuple[6, true, false], idx: 2];",
			"MyTuple := #[Integer, ... Boolean];",
			"getValue = ^[t: MyTuple, idx: Integer<1..>] => Result<Boolean, IndexOutOfRange> :: #t->item(#idx);"
		);
		$this->assertEquals("false", $result);
	}

	public function testTupleItemTypeWithRestInteger(): void {
		$result = $this->executeCodeSnippet("getValue[t: MyTuple[6, true, false], idx: 2];",
			"MyTuple := #[Integer, ... Boolean];",
			"getValue = ^[t: MyTuple, idx: Integer<0..>] => Result<Integer|Boolean, IndexOutOfRange> :: #t->item(#idx);"
		);
		$this->assertEquals("false", $result);
	}

	public function testRecordItemOutOfRange(): void {
		$result = $this->executeCodeSnippet("{MyRecord[a: 3, b: 5]}->item('d');", "MyRecord := #[a: Integer, b: Real];");
		$this->assertEquals("@MapItemNotFound![key: 'd']", $result);
	}

	public function testRecordItemInRange(): void {
		$result = $this->executeCodeSnippet("{MyRecord[a: 3, b: 5]}->item('b');", "MyRecord := #[a: Integer, b: Real];");
		$this->assertEquals("5", $result);
	}

	public function testRecordOptionalKeyItemInRange(): void {
		$result = $this->executeCodeSnippet("{MyRecord[a: 3, b: 5]}->item('b');", "MyRecord := #[a: Integer, b: ?Real];");
		$this->assertEquals("5", $result);
	}

	public function testRecordOptionalKeyItemMissing(): void {
		$result = $this->executeCodeSnippet("{MyRecord[a: 3]}->item('b');", "MyRecord := #[a: Integer, b: ?Real];");
		$this->assertEquals("@MapItemNotFound![key: 'b']", $result);
	}

	public function testRecordItemTypeWithRestSubset(): void {
		$result = $this->executeCodeSnippet("getValue(MyRecord[a: 6, b: true, c: false]);",
			"MyRecord := #[a: Integer, ... Boolean];",
			"getValue = ^t: MyRecord => Integer :: t.a;"
		);
		$this->assertEquals("6", $result);
	}

	public function testRecordItemTypeWithRestRange(): void {
		$result = $this->executeCodeSnippet("getValue[t: MyRecord[a: 6, b: true, c: false], key: 'c'];",
			"MyRecord := #[a: Integer, ... Boolean];",
			"getValue = ^[t: MyRecord, key: String['c', 'd', 'f']] => Result<Boolean, MapItemNotFound> :: #t->item(#key);"
		);
		$this->assertEquals("false", $result);
	}

	public function testRecordItemTypeWithRestString(): void {
		$result = $this->executeCodeSnippet("getValue[t: MyRecord[a: 6, b: true, c: false], key: 'c'];",
			"MyRecord := #[a: Integer, ... Boolean];",
			"getValue = ^[t: MyRecord, key: String] => Result<Integer|Boolean, MapItemNotFound> :: #t->item(#key);"
		);
		$this->assertEquals("false", $result);
	}


	public function testOpenTupleInvalidParameterType(): void {
		$this->executeErrorCodeSnippet(
			"Invalid parameter type: Null",
			"{MyTuple[3, 5]}->item;", "MyTuple := #[Integer, Real];"
		);
	}

	public function testOpenRecordInvalidParameterType(): void {
		$this->executeErrorCodeSnippet(
			"Invalid parameter type: Null",
			"{MyRecord[a: 3, b: 5]}->item;", "MyRecord := #[a: Integer, b: Real];"
		);
	}

}