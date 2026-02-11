<?php

namespace Walnut\Lang\Test\Almond\Engine\NativeCode\Data;

use Walnut\Lang\Test\Almond\Engine\CodeExecutionTestHelper;

final class WithTest extends CodeExecutionTestHelper {

	public function testWithArrayArrayOk(): void {
		$result = $this->executeCodeSnippet("{MyData![1,3,5]}->testWith[2,6];", <<<NUT
			MyData := Array<Integer, 2..5>;
			MyData->testWith(^param: Array<Integer, 1..3> => MyData) :: $->with(param);
		NUT);
		$this->assertEquals("MyData![2, 6, 5]", $result);
	}

	public function testWithArrayArrayWrongItemType(): void {
		$this->executeErrorCodeSnippet(
			"due to incompatible item type",
			"{MyData![1,3,5]}->testWith[2,6];", <<<NUT
			MyData := Array<Integer, 2..5>;
			MyData->testWith(^param: Array<Real, 1..3> => MyData) :: $->with(param);
		NUT);
	}

	public function testWithArrayArrayWrongLength(): void {
		$this->executeErrorCodeSnippet(
			"due to incompatible length",
			"{MyData![1,3,5]}->testWith[2,6];", <<<NUT
			MyData := Array<Integer, 2..5>;
			MyData->testWith(^param: Array<Integer, 1..10> => MyData) :: $->with(param);
		NUT);
	}

	public function testWithArrayTupleOk(): void {
		$result = $this->executeCodeSnippet("{MyData![1,3,5]}->testWith[2,6];", <<<NUT
			MyData := Array<Integer, 2..5>;
			MyData->testWith(^param: [Integer, Integer[1, 6]] => MyData) :: $->with(param);
		NUT);
		$this->assertEquals("MyData![2, 6, 5]", $result);
	}

	public function testWithArrayTupleWrongItemType(): void {
		$this->executeErrorCodeSnippet(
			"due to incompatible item type",
			"{MyData![1,3,5]}->testWith[2,6];", <<<NUT
			MyData := Array<Integer, 2..5>;
			MyData->testWith(^param: [Integer, String] => MyData) :: $->with(param);
		NUT);
	}

	public function testWithArrayTupleWrongLength(): void {
		$this->executeErrorCodeSnippet(
			"due to incompatible length",
			"{MyData![1,3,5]}->testWith[2,6];", <<<NUT
			MyData := Array<Integer, 2..5>;
			MyData->testWith(^param: [Integer, Integer, Integer, Integer, Integer, Integer] => MyData) :: $->with(param);
		NUT);
	}

	public function testWithArrayTupleWrongLengthDueToRestType(): void {
		$this->executeErrorCodeSnippet(
			"due to incompatible length",
			"{MyData![1,3,5]}->testWith[2,6];", <<<NUT
			MyData := Array<Integer, 2..5>;
			MyData->testWith(^param: [Integer, Integer, ...Integer] => MyData) :: $->with(param);
		NUT);
	}

	public function testWithTupleArrayOk(): void {
		$result = $this->executeCodeSnippet("{MyData![1,3.14,5,-0.7]}->testWith[2,6];", <<<NUT
			MyData := [Integer, Real, Integer, Real];
			MyData->testWith(^param: Array<Integer, 1..3> => MyData) :: $->with(param);
		NUT);
		$this->assertEquals("MyData![2, 6, 5, -0.7]", $result);
	}

	public function testWithTupleArrayWrongLength(): void {
		$this->executeErrorCodeSnippet(
			"due to incompatible length",
			"{MyData![1,3.14,5,-0.7]}->testWith[2,6];", <<<NUT
			MyData := [Integer, Real, Integer, Real];
			MyData->testWith(^param: Array<Integer, 1..5> => MyData) :: $->with(param);
		NUT);
	}

	public function testWithTupleArrayWrongType(): void {
		$this->executeErrorCodeSnippet(
			"due to incompatible type at index 0",
			"{MyData![1,3.14,5,-0.7]}->testWith[2,6];", <<<NUT
			MyData := [Integer, Real, Integer, Real];
			MyData->testWith(^param: Array<Real, 1..3> => MyData) :: $->with(param);
		NUT);
	}

	public function testWithTupleTupleOk(): void {
		$result = $this->executeCodeSnippet("{MyData![1,3.14,5,-0.7]}->testWith[2,6];", <<<NUT
			MyData := [Integer, Real, Integer, Real];
			MyData->testWith(^param: [Integer, Integer] => MyData) :: $->with(param);
		NUT);
		$this->assertEquals("MyData![2, 6, 5, -0.7]", $result);
	}

	public function testWithTupleTupleWrongLength(): void {
		$this->executeErrorCodeSnippet(
			"due to incompatible length",
			"{MyData![1,3.14,5,-0.7]}->testWith[2,6];", <<<NUT
			MyData := [Integer, Real, Integer, Real];
			MyData->testWith(^param: [Integer, Integer, Integer, Integer, Integer] => MyData) :: $->with(param);
		NUT);
	}

	public function testWithTupleTupleWrongLengthDueToRestType(): void {
		$this->executeErrorCodeSnippet(
			"with a rest type",
			"{MyData![1,3.14,5,-0.7]}->testWith[2,6];", <<<NUT
			MyData := [Integer, Real, Integer, Real];
			MyData->testWith(^param: [Integer, Integer, ... Integer] => MyData) :: $->with(param);
		NUT);
	}

	public function testWithTupleTupleWrongType(): void {
		$this->executeErrorCodeSnippet(
			"due to incompatible type at index 0",
			"{MyData![1,3.14,5,-0.7]}->testWith[2,6];", <<<NUT
			MyData := [Integer, Real, Integer, Real];
			MyData->testWith(^param: [Real, Real] => MyData) :: $->with(param);
		NUT);
	}


	public function testWithMapMapOk(): void {
		$result = $this->executeCodeSnippet("{MyData![a:1,b:3,c:5]}->testWith[c:2,d:6];", <<<NUT
			MyData := Map<Integer>;
			MyData->testWith(^param: Map<Integer, 1..3> => MyData) :: $->with(param);
		NUT);
		$this->assertEquals("MyData![a: 1, b: 3, c: 2, d: 6]", $result);
	}

	public function testWithMapKeyType(): void {
		$result = $this->executeCodeSnippet(
			"{MyData![a:1,b:3,c:5]}->testWith[c:2,d:6];", <<<NUT
			MyData := Map<String<1>:Integer>;
			MyData->testWith(^param: Map<String<1>:Integer, 1..3> => MyData) :: $->with(param);
		NUT);
		$this->assertEquals("MyData![a: 1, b: 3, c: 2, d: 6]", $result);
	}

	public function testWithMapMapWrongItemType(): void {
		$this->executeErrorCodeSnippet(
			"due to incompatible item type",
			"{MyData![a:1,b:3,c:5]}->testWith[c:2,d:6.28];", <<<NUT
			MyData := Map<Integer>;
			MyData->testWith(^param: Map<Real, 1..3> => MyData) :: $->with(param);
		NUT);
	}

	public function testWithMapMapWrongKeyType(): void {
		$this->executeErrorCodeSnippet(
			"due to incompatible key type",
			"{MyData![a:1,b:3,c:5]}->testWith[c:2,de:6];", <<<NUT
			MyData := Map<String<1>:Integer>;
			MyData->testWith(^param: Map<String<1..2>:Integer, 1..3> => MyData) :: $->with(param);
		NUT);
	}

	public function testWithMapMapWrongLength(): void {
		$this->executeErrorCodeSnippet(
			"with a limited length",
			"{MyData![a:1,b:3,c:5]}->testWith[c:2,d:6];", <<<NUT
			MyData := Map<Integer, ..5>;
			MyData->testWith(^param: Map<Integer, 1..3> => MyData) :: $->with(param);
		NUT);
	}

	public function testWithMapRecordOk(): void {
		$result = $this->executeCodeSnippet("{MyData![a:1,b:3,c:5]}->testWith[c:2,d:6];", <<<NUT
			MyData := Map<Integer>;
			MyData->testWith(^param: [c: Integer, d: Integer] => MyData) :: $->with(param);
		NUT);
		$this->assertEquals("MyData![a: 1, b: 3, c: 2, d: 6]", $result);
	}

	public function testWithMapRecordWrongItemType(): void {
		$this->executeErrorCodeSnippet(
			"due to incompatible item type",
			"{MyData![a:1,b:3,c:5]}->testWith[c:2,d:6.28];", <<<NUT
			MyData := Map<Integer>;
			MyData->testWith(^param: [c: Real, d: Integer] => MyData) :: $->with(param);
		NUT);
	}

	public function testWithMapRecordWrongLength(): void {
		$this->executeErrorCodeSnippet(
			"with a limited length",
			"{MyData![a:1,b:3,c:5]}->testWith[c:2,d:6];", <<<NUT
			MyData := Map<Integer, ..5>;
			MyData->testWith(^param: [c: Integer, d: Integer] => MyData) :: $->with(param);
		NUT);
	}


	public function testWithRecordMapError(): void {
		$this->executeErrorCodeSnippet(
			"Cannot call 'with' on Record type MyData with a parameter of Map type Map<Integer, 1..3>",
			"{MyData![a:1,b:3,c:5]}->testWith[c:2,d:6];", <<<NUT
			MyData := [a: Integer, b: Integer, c: Integer];
			MyData->testWith(^param: Map<Integer, 1..3> => MyData) :: $->with(param);
		NUT);
	}

	public function testWithRecordRecordOk(): void {
		$result = $this->executeCodeSnippet("{MyData![a:1,b:3,c:5]}->testWith[b:2,c:6];", <<<NUT
			MyData := [a: Integer, b: Integer, c: Integer];
			MyData->testWith(^param: [b: Integer, c: Integer] => MyData) :: $->with(param);
		NUT);
		$this->assertEquals("MyData![a: 1, b: 2, c: 6]", $result);
	}

	public function testWithRecordRecordWrongTypeKey(): void {
		$this->executeErrorCodeSnippet(
			"due to incompatible type at key c",
			"{MyData![a:1,b:3,c:5]}->testWith[b:2,c:6];", <<<NUT
			MyData := [a: Integer, b: Integer, c: Integer];
			MyData->testWith(^param: [b: Integer, c: Real] => MyData) :: $->with(param);
		NUT);
	}

	public function testWithRecordRecordWrongTypeMissingKey(): void {
		$this->executeErrorCodeSnippet(
			"due to incompatible type at key d",
			"{MyData![a:1,b:3,c:5]}->testWith[c:2,d:6];", <<<NUT
			MyData := [a: Integer, b: Integer, c: Integer];
			MyData->testWith(^param: [c: Integer, d: Integer] => MyData) :: $->with(param);
		NUT);
	}

}