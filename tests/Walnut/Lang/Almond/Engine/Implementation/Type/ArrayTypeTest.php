<?php

namespace Walnut\Lang\Test\Almond\Engine\Implementation\Type;

use Walnut\Lang\Test\Almond\AlmondBaseTestHelper;

final class ArrayTypeTest extends AlmondBaseTestHelper {


	public function testArrayType(): void {
		$arrayTypes = [
			0 => $this->typeRegistry->array($this->typeRegistry->nothing, 0, 0),
			1 => $this->typeRegistry->array($this->typeRegistry->integer(), 3, 5),
			2 => $this->typeRegistry->array($this->typeRegistry->integer(), 3, 3),
			3 => $this->typeRegistry->array($this->typeRegistry->integer(2), 3, 5),
			4 => $this->typeRegistry->array($this->typeRegistry->integer(2), 3, 3),
			5 => $this->typeRegistry->array($this->typeRegistry->integer(0), 3, 5),
			6 => $this->typeRegistry->array($this->typeRegistry->integer(0), 3, 3),
			7 => $this->typeRegistry->array($this->typeRegistry->integer(2), 2, 5),
			8 => $this->typeRegistry->array($this->typeRegistry->integer(2), 2, 3),
		];

		$tupleTypes = [
			0 => $this->typeRegistry->tuple([], null),
			1 => $this->typeRegistry->tuple([], $this->typeRegistry->integer(2)),
			2 => $this->typeRegistry->tuple([$this->typeRegistry->real(), $this->typeRegistry->integer(), $this->typeRegistry->real(2)], null),
			3 => $this->typeRegistry->tuple([$this->typeRegistry->real(), $this->typeRegistry->integer(), $this->typeRegistry->real(2)], $this->typeRegistry->integer(0)),
			4 => $this->typeRegistry->tuple([$this->typeRegistry->real(), $this->typeRegistry->integer(), $this->typeRegistry->real(2)], $this->typeRegistry->string()),
			5 => $this->typeRegistry->tuple([$this->typeRegistry->string(), $this->typeRegistry->integer(), $this->typeRegistry->real(2)], null),
			6 => $this->typeRegistry->tuple([$this->typeRegistry->string(), $this->typeRegistry->integer(), $this->typeRegistry->real(2)], $this->typeRegistry->integer(0)),
		];

		$matrix = [
			0 => [ true,  true, false, false, false, false, false], // Array<Nothing, 0> <: [], [... Integer<0..>]
			1 => [false, false, false, false, false, false, false], // no matches for Array<Integer, 3..5>
			2 => [false, false, false, false, false, false, false], // no matches for Array<Integer, 3>
			3 => [false,  true, false,  true, false, false, false], // Array<Integer<2..>, 3..5> <: [Real, Integer, Real<2..>, ... Integer<0..>]
			4 => [false,  true,  true,  true, false, false, false], // Array<Integer<2..>, 3> <: [Real, Integer, Real<2..>] and [Real, Integer, Real<2..>, ... Integer<0..>]
			5 => [false, false, false, false, false, false, false], // no matches for Array<Integer<0..>, 3..5> because of Tuple[2]
			6 => [false, false, false, false, false, false, false], // no matches for Array<Integer<0..>, 3> because of Tuple[2]
			7 => [false,  true, false, false, false, false, false], // no matches for Array<Integer<2..>, 2..5> because of length
			8 => [false,  true, false, false, false, false, false], // no matches for Array<Integer<2..>, 2..3> because of length
		];

		foreach($arrayTypes as $aIdx => $arrayType) {
			foreach($tupleTypes as $tIdx => $tupleType) {
				$this->assertEquals($matrix[$aIdx][$tIdx], $arrayType->isSubtypeOf($tupleType),
					"Failed asserting that ArrayType[#$aIdx] = $arrayType is " .
					($matrix[$aIdx][$tIdx] ? "" : "not ") .
					"a subtype of TupleType[#$tIdx] = $tupleType");
			}
		}
	}

}