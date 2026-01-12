<?php

namespace Walnut\Lang\Test\NativeCode\Open;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class WithTest extends CodeExecutionTestHelper {

	public function testWithArrayArrayOk(): void {
		$result = $this->executeCodeSnippet("{MyOpen[1,3,5]}->testWith[2,6];", <<<NUT
			MyOpen := #Array<Integer, 2..5>;
			MyOpen->testWith(^param: Array<Integer, 1..3> => MyOpen) :: $->with(param);
		NUT);
		$this->assertEquals("MyOpen[2, 6, 5]", $result);
	}

	public function testWithArrayArrayWrongItemType(): void {
		$this->executeErrorCodeSnippet(
			"due to incompatible item type",
			"{MyOpen[1,3,5]}->testWith[2,6];", <<<NUT
			MyOpen := #Array<Integer, 2..5>;
			MyOpen->testWith(^param: Array<Real, 1..3> => MyOpen) :: $->with(param);
		NUT);
	}

	public function testWithArrayArrayWrongLength(): void {
		$this->executeErrorCodeSnippet(
			"due to incompatible length",
			"{MyOpen[1,3,5]}->testWith[2,6];", <<<NUT
			MyOpen := #Array<Integer, 2..5>;
			MyOpen->testWith(^param: Array<Integer, 1..10> => MyOpen) :: $->with(param);
		NUT);
	}

	public function testWithArrayTupleOk(): void {
		$result = $this->executeCodeSnippet("{MyOpen[1,3,5]}->testWith[2,6];", <<<NUT
			MyOpen := #Array<Integer, 2..5>;
			MyOpen->testWith(^param: [Integer, Integer[1, 6]] => MyOpen) :: $->with(param);
		NUT);
		$this->assertEquals("MyOpen[2, 6, 5]", $result);
	}

	public function testWithArrayTupleWrongItemType(): void {
		$this->executeErrorCodeSnippet(
			"due to incompatible item type",
			"{MyOpen[1,3,5]}->testWith[2,6];", <<<NUT
			MyOpen := #Array<Integer, 2..5>;
			MyOpen->testWith(^param: [Integer, String] => MyOpen) :: $->with(param);
		NUT);
	}

	public function testWithArrayTupleWrongLength(): void {
		$this->executeErrorCodeSnippet(
			"due to incompatible length",
			"{MyOpen[1,3,5]}->testWith[2,6];", <<<NUT
			MyOpen := #Array<Integer, 2..5>;
			MyOpen->testWith(^param: [Integer, Integer, Integer, Integer, Integer, Integer] => MyOpen) :: $->with(param);
		NUT);
	}

	public function testWithArrayTupleWrongLengthDueToRestType(): void {
		$this->executeErrorCodeSnippet(
			"due to incompatible length",
			"{MyOpen[1,3,5]}->testWith[2,6];", <<<NUT
			MyOpen := #Array<Integer, 2..5>;
			MyOpen->testWith(^param: [Integer, Integer, ...Integer] => MyOpen) :: $->with(param);
		NUT);
	}

	public function testWithTupleArrayOk(): void {
		$result = $this->executeCodeSnippet("{MyOpen[1,3.14,5,-0.7]}->testWith[2,6];", <<<NUT
			MyOpen := #[Integer, Real, Integer, Real];
			MyOpen->testWith(^param: Array<Integer, 1..3> => MyOpen) :: $->with(param);
		NUT);
		$this->assertEquals("MyOpen[2, 6, 5, -0.7]", $result);
	}

	public function testWithTupleArrayWrongLength(): void {
		$this->executeErrorCodeSnippet(
			"due to incompatible length",
			"{MyOpen[1,3.14,5,-0.7]}->testWith[2,6];", <<<NUT
			MyOpen := #[Integer, Real, Integer, Real];
			MyOpen->testWith(^param: Array<Integer, 1..5> => MyOpen) :: $->with(param);
		NUT);
	}

	public function testWithTupleArrayWrongType(): void {
		$this->executeErrorCodeSnippet(
			"due to incompatible type at index 0",
			"{MyOpen[1,3.14,5,-0.7]}->testWith[2,6];", <<<NUT
			MyOpen := #[Integer, Real, Integer, Real];
			MyOpen->testWith(^param: Array<Real, 1..3> => MyOpen) :: $->with(param);
		NUT);
	}

	public function testWithTupleTupleOk(): void {
		$result = $this->executeCodeSnippet("{MyOpen[1,3.14,5,-0.7]}->testWith[2,6];", <<<NUT
			MyOpen := #[Integer, Real, Integer, Real];
			MyOpen->testWith(^param: [Integer, Integer] => MyOpen) :: $->with(param);
		NUT);
		$this->assertEquals("MyOpen[2, 6, 5, -0.7]", $result);
	}

	public function testWithTupleTupleWrongLength(): void {
		$this->executeErrorCodeSnippet(
			"due to incompatible length",
			"{MyOpen[1,3.14,5,-0.7]}->testWith[2,6];", <<<NUT
			MyOpen := #[Integer, Real, Integer, Real];
			MyOpen->testWith(^param: [Integer, Integer, Integer, Integer, Integer] => MyOpen) :: $->with(param);
		NUT);
	}

	public function testWithTupleTupleWrongLengthDueToRestType(): void {
		$this->executeErrorCodeSnippet(
			"with a rest type",
			"{MyOpen[1,3.14,5,-0.7]}->testWith[2,6];", <<<NUT
			MyOpen := #[Integer, Real, Integer, Real];
			MyOpen->testWith(^param: [Integer, Integer, ... Integer] => MyOpen) :: $->with(param);
		NUT);
	}

	public function testWithTupleTupleWrongType(): void {
		$this->executeErrorCodeSnippet(
			"due to incompatible type at index 0",
			"{MyOpen[1,3.14,5,-0.7]}->testWith[2,6];", <<<NUT
			MyOpen := #[Integer, Real, Integer, Real];
			MyOpen->testWith(^param: [Real, Real] => MyOpen) :: $->with(param);
		NUT);
	}


	public function testWithMapMapOk(): void {
		$result = $this->executeCodeSnippet("{MyOpen[a:1,b:3,c:5]}->testWith[c:2,d:6];", <<<NUT
			MyOpen := #Map<Integer>;
			MyOpen->testWith(^param: Map<Integer, 1..3> => MyOpen) :: $->with(param);
		NUT);
		$this->assertEquals("MyOpen[a: 1, b: 3, c: 2, d: 6]", $result);
	}

	public function testWithMapKeyType(): void {
		$result = $this->executeCodeSnippet(
			"{MyOpen[a:1,b:3,c:5]}->testWith[c:2,d:6];", <<<NUT
			MyOpen := #Map<String<1>:Integer>;
			MyOpen->testWith(^param: Map<String<1>:Integer, 1..3> => MyOpen) :: $->with(param);
		NUT);
		$this->assertEquals("MyOpen[a: 1, b: 3, c: 2, d: 6]", $result);
	}

	public function testWithMapMapWrongItemType(): void {
		$this->executeErrorCodeSnippet(
			"due to incompatible item type",
			"{MyOpen[a:1,b:3,c:5]}->testWith[c:2,d:6.28];", <<<NUT
			MyOpen := #Map<Integer>;
			MyOpen->testWith(^param: Map<Real, 1..3> => MyOpen) :: $->with(param);
		NUT);
	}

	public function testWithMapMapWrongKeyType(): void {
		$this->executeErrorCodeSnippet(
			"due to incompatible key type",
			"{MyOpen[a:1,b:3,c:5]}->testWith[c:2,de:6];", <<<NUT
			MyOpen := #Map<String<1>:Integer>;
			MyOpen->testWith(^param: Map<String<1..2>:Integer, 1..3> => MyOpen) :: $->with(param);
		NUT);
	}

	public function testWithMapMapWrongLength(): void {
		$this->executeErrorCodeSnippet(
			"with a limited length",
			"{MyOpen[a:1,b:3,c:5]}->testWith[c:2,d:6];", <<<NUT
			MyOpen := #Map<Integer, ..5>;
			MyOpen->testWith(^param: Map<Integer, 1..3> => MyOpen) :: $->with(param);
		NUT);
	}

	public function testWithMapRecordOk(): void {
		$result = $this->executeCodeSnippet("{MyOpen[a:1,b:3,c:5]}->testWith[c:2,d:6];", <<<NUT
			MyOpen := #Map<Integer>;
			MyOpen->testWith(^param: [c: Integer, d: Integer] => MyOpen) :: $->with(param);
		NUT);
		$this->assertEquals("MyOpen[a: 1, b: 3, c: 2, d: 6]", $result);
	}

	public function testWithMapRecordWrongItemType(): void {
		$this->executeErrorCodeSnippet(
			"due to incompatible item type",
			"{MyOpen[a:1,b:3,c:5]}->testWith[c:2,d:6.28];", <<<NUT
			MyOpen := #Map<Integer>;
			MyOpen->testWith(^param: [c: Real, d: Integer] => MyOpen) :: $->with(param);
		NUT);
	}

	public function testWithMapRecordWrongLength(): void {
		$this->executeErrorCodeSnippet(
			"with a limited length",
			"{MyOpen[a:1,b:3,c:5]}->testWith[c:2,d:6];", <<<NUT
			MyOpen := #Map<Integer, ..5>;
			MyOpen->testWith(^param: [c: Integer, d: Integer] => MyOpen) :: $->with(param);
		NUT);
	}


	public function testWithRecordMapError(): void {
		$this->executeErrorCodeSnippet(
			"Cannot call 'with' on Record type MyOpen with a parameter of Map type Map<Integer, 1..3>",
			"{MyOpen[a:1,b:3,c:5]}->testWith[c:2,d:6];", <<<NUT
			MyOpen := #[a: Integer, b: Integer, c: Integer];
			MyOpen->testWith(^param: Map<Integer, 1..3> => MyOpen) :: $->with(param);
		NUT);
	}

	public function testWithRecordRecordOk(): void {
		$result = $this->executeCodeSnippet("{MyOpen[a:1,b:3,c:5]}->testWith[b:2,c:6];", <<<NUT
			MyOpen := #[a: Integer, b: Integer, c: Integer];
			MyOpen->testWith(^param: [b: Integer, c: Integer] => MyOpen) :: $->with(param);
		NUT);
		$this->assertEquals("MyOpen[a: 1, b: 2, c: 6]", $result);
	}

	public function testWithRecordRecordWrongTypeKey(): void {
		$this->executeErrorCodeSnippet(
			"due to incompatible type at key c",
			"{MyOpen[a:1,b:3,c:5]}->testWith[b:2,c:6];", <<<NUT
			MyOpen := #[a: Integer, b: Integer, c: Integer];
			MyOpen->testWith(^param: [b: Integer, c: Real] => MyOpen) :: $->with(param);
		NUT);
	}

	public function testWithRecordRecordWrongTypeMissingKey(): void {
		$this->executeErrorCodeSnippet(
			"due to incompatible type at key d",
			"{MyOpen[a:1,b:3,c:5]}->testWith[c:2,d:6];", <<<NUT
			MyOpen := #[a: Integer, b: Integer, c: Integer];
			MyOpen->testWith(^param: [c: Integer, d: Integer] => MyOpen) :: $->with(param);
		NUT);
	}



	public function testWithValidatorOk(): void {
		$result = $this->executeCodeSnippet("{MyOpen[a:1,b:3,c:5]}->testWith[b:2,c:6];", <<<NUT
			MyOpen := #[a: Integer, b: Integer, c: Integer] :: null;
			MyOpen->testWith(^param: [b: Integer, c: Integer] => MyOpen) :: $->with(param);
		NUT);
		$this->assertEquals("MyOpen[a: 1, b: 2, c: 6]", $result);
	}

	public function testWithValidatorWithErrorTypeOk(): void {
		$result = $this->executeCodeSnippet("MyOpen[a:1,b:3,c:5]?->testWith[b:2,c:6];", <<<NUT
			MyError := ();
			MyOpen := #[a: Integer, b: Integer, c: Integer] @ MyError :: null;
			MyOpen->testWith(^param: [b: Integer, c: Integer] => Result<MyOpen, MyError>) :: $->with(param);
		NUT);
		$this->assertEquals("MyOpen[a: 1, b: 2, c: 6]", $result);
	}

	public function testWithValidatorWithErrorTypeReturnErrorValue(): void {
		$result = $this->executeCodeSnippet("MyOpen[a:1,b:3,c:5]?->testWith[b:2,c:6];", <<<NUT
			MyError := ();
			MyOpen := #[a: Integer, b: Integer, c: Integer] @ MyError :: ?when(#b == 2) { => @MyError };
			MyOpen->testWith(^param: [b: Integer, c: Integer] => Result<MyOpen, MyError>) :: $->with(param);
		NUT);
		$this->assertEquals("@MyError", $result);
	}

	public function testWithValidatorWithErrorTypeWrongReturnType(): void {
		$this->executeErrorCodeSnippet(
			"Expected a return value of type MyOpen, got Result<MyOpen, MyError>",
			"MyOpen[a:1,b:3,c:5]?->testWith[b:2,c:6];", <<<NUT
			MyError := ();
			MyOpen := #[a: Integer, b: Integer, c: Integer] @ MyError :: null;
			MyOpen->testWith(^param: [b: Integer, c: Integer] => MyOpen) :: $->with(param);
		NUT);
	}

}