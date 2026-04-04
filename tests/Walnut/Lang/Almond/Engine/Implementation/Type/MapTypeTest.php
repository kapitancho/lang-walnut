<?php

namespace Walnut\Lang\Test\Almond\Engine\Implementation\Type;

use Walnut\Lang\Test\Almond\AlmondBaseTestHelper;

final class MapTypeTest extends AlmondBaseTestHelper {


	public function testMapType(): void {
		$mapTypes = [
			0 => $this->typeRegistry->map($this->typeRegistry->nothing, 0, 0),
			1 => $this->typeRegistry->map($this->typeRegistry->integer(), 3, 5),
			2 => $this->typeRegistry->map($this->typeRegistry->integer(), 3, 3),
			3 => $this->typeRegistry->map($this->typeRegistry->integer(2), 3, 5),
			4 => $this->typeRegistry->map($this->typeRegistry->integer(2), 3, 3),
			5 => $this->typeRegistry->map($this->typeRegistry->integer(0), 3, 5),
			6 => $this->typeRegistry->map($this->typeRegistry->integer(0), 3, 3),
			7 => $this->typeRegistry->map($this->typeRegistry->integer(2), 2, 5),
			8 => $this->typeRegistry->map($this->typeRegistry->integer(2), 2, 3),
			9 => $this->typeRegistry->map($this->typeRegistry->integer(2), 3, 3, $this->typeRegistry->stringSubset(
				['a', 'b', 'c']
			)),
			10 => $this->typeRegistry->map($this->typeRegistry->integer(2), 3, 3, $this->typeRegistry->stringSubset(
				['a', 'b', 'x']
			)),
			11 => $this->typeRegistry->map($this->typeRegistry->integer(2), 3, 5, $this->typeRegistry->stringSubset(
				['a', 'b', 'c']
			)),
			12 => $this->typeRegistry->map($this->typeRegistry->integer(2), 1, 5),
			13 => $this->typeRegistry->map($this->typeRegistry->integer(2), 1, 3),
			14 => $this->typeRegistry->map($this->typeRegistry->integer(2), 2, 3, $this->typeRegistry->stringSubset(
				['a', 'b']
			)),
		];

		$recordTypes = [
			0 => $this->typeRegistry->record([], null),
			1 => $this->typeRegistry->record([], $this->typeRegistry->integer(2)),
			2 => $this->typeRegistry->record(['a' => $this->typeRegistry->real(), 'b' => $this->typeRegistry->integer(), 'c' => $this->typeRegistry->real(2)], null),
			3 => $this->typeRegistry->record(['a' => $this->typeRegistry->real(), 'b' => $this->typeRegistry->integer(), 'c' => $this->typeRegistry->real(2)], $this->typeRegistry->integer(0)),
			4 => $this->typeRegistry->record(['a' => $this->typeRegistry->real(), 'b' => $this->typeRegistry->integer(), 'c' => $this->typeRegistry->real(2)], $this->typeRegistry->string()),
			5 => $this->typeRegistry->record(['a' => $this->typeRegistry->string(), 'b' => $this->typeRegistry->integer(), 'c' => $this->typeRegistry->real(2)], null),
			6 => $this->typeRegistry->record(['a' => $this->typeRegistry->string(), 'b' => $this->typeRegistry->integer(), 'c' => $this->typeRegistry->real(2)], $this->typeRegistry->integer(0)),
			7 => $this->typeRegistry->record(['a' => $this->typeRegistry->real(), 'b' => $this->typeRegistry->integer(), 'c' => $this->typeRegistry->optional($this->typeRegistry->real(2))], null),
			8 => $this->typeRegistry->record(['a' => $this->typeRegistry->real(), 'b' => $this->typeRegistry->integer(), 'c' => $this->typeRegistry->optional($this->typeRegistry->real(2))], $this->typeRegistry->integer(0)),
		];

		$matrix = [
			 0 => [ true,  true, false, false, false, false, false, false, false], // Map<Nothing, 0> <: [], [... Integer<0..>]
			 1 => [false, false, false, false, false, false, false, false, false], // no matches for Map<Integer, 3..5>
			 2 => [false, false, false, false, false, false, false, false, false], // no matches for Map<Integer, 3>
			 3 => [false,  true, false,  true, false, false, false, false,  true], // Map<Integer<2..>, 3..5> <: [Real, Integer, Real<2..>, ... Integer<0..>]
			 4 => [false,  true, false,  true, false, false, false, false,  true], // Map<Integer<2..>, 3> <: [Real, Integer, Real<2..>, ... Integer<0..>]
			 5 => [false, false, false, false, false, false, false, false, false], // no matches for Map<Integer<0..>, 3..5> because of Record[2]
			 6 => [false, false, false, false, false, false, false, false, false], // no matches for Map<Integer<0..>, 3> because of Record[2]
			 7 => [false,  true, false, false, false, false, false, false,  true], // no matches for Map<Integer<2..>, 2..5> because of length
			 8 => [false,  true, false, false, false, false, false, false,  true], // no matches for Map<Integer<2..>, 2..3> because of length
			 9 => [false,  true,  true,  true, false, false, false,  true,  true], // Map<'a'|'b'|'c':Integer<2..>, 3> <: [Real, Integer, Real<2..>] and [Real, Integer, Real<2..>, ... Integer<0..>]
			10 => [false,  true, false,  true, false, false, false, false,  true], // Map<'a'|'b'|'x':Integer<2..>, 3> <: [Real, Integer, Real<2..>, ... Integer<0..>]
			11 => [false,  true, false,  true, false, false, false, false,  true], // Map<'a'|'b'|'c':Integer<2..>, 5> <: [Real, Integer, Real<2..>, ... Integer<0..>]
			12 => [false,  true, false, false, false, false, false, false, false], // no matches for Map<Integer<2..>, 1..5> because of length
			13 => [false,  true, false, false, false, false, false, false, false], // no matches for Map<Integer<2..>, 1..3> because of length
			14 => [false,  true, false, false, false, false, false,  true,  true], // Map<'a'|'b':Integer<2..>, 3> <: [Real, Integer, ?Real<2..>] and [Real, Integer, ?Real<2..>, ... Integer<0..>]

		];

		foreach($mapTypes as $aIdx => $mapType) {
			foreach($recordTypes as $tIdx => $recordType) {
				$this->assertEquals($matrix[$aIdx][$tIdx], $mapType->isSubtypeOf($recordType),
					"Failed asserting that MapType[#$aIdx] = $mapType is " .
					($matrix[$aIdx][$tIdx] ? "" : "not ") .
					"a subtype of RecordType[#$tIdx] = $recordType");
			}
		}
	}

}