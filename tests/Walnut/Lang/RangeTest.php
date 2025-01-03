<?php

namespace Walnut\Lang;

use BcMath\Number;
use PHPUnit\Framework\TestCase;
use Walnut\Lang\Blueprint\Common\Range\InvalidIntegerRange;
use Walnut\Lang\Blueprint\Common\Range\InvalidLengthRange;
use Walnut\Lang\Blueprint\Common\Range\InvalidRealRange;
use Walnut\Lang\Blueprint\Common\Range\MinusInfinity;
use Walnut\Lang\Blueprint\Common\Range\PlusInfinity;
use Walnut\Lang\Implementation\Common\Range\IntegerRange;
use Walnut\Lang\Implementation\Common\Range\LengthRange;
use Walnut\Lang\Implementation\Common\Range\RealRange;

final class RangeTest extends TestCase {

	public function testLengthRangeWithInfinity(): void {
		$mm = new Number(0);
		$m = new Number(2);
		$p = new Number(5);
		$pp = PlusInfinity::value;
		$ranges = [
			new LengthRange($mm, $pp),
			new LengthRange($mm, $m),
			new LengthRange($mm, $p),
			new LengthRange($m, $pp),
			new LengthRange($p, $pp),
			new LengthRange($m, $p)
		];
		$this->checkMatrix($ranges);
	}

	public function testLengthRangeNegative(): void {
		$this->expectException(InvalidLengthRange::class);
		new LengthRange(new Number(-10), new Number(10));
	}

	public function testLengthRangeInvalid(): void {
		$this->expectException(InvalidLengthRange::class);
		new LengthRange(new Number(10), new Number(3));
	}

	public function testIntegerRangeWithInfinity(): void {
		$m = new Number(-5);
		$p = new Number(5);
		$ranges = [
			new IntegerRange(MinusInfinity::value, PlusInfinity::value),
			new IntegerRange(MinusInfinity::value, $m),
			new IntegerRange(MinusInfinity::value, $p),
			new IntegerRange($m, PlusInfinity::value),
			new IntegerRange($p, PlusInfinity::value),
			new IntegerRange($m, $p)
		];
		$this->checkMatrix($ranges);
	}

	public function testIntegerRangeWithoutInfinity(): void {
		$mm = new Number(-1000);
		$m = new Number(-5);
		$p = new Number(5);
		$pp = new Number(1000);
		$ranges = [
			new IntegerRange($mm, $pp),
			new IntegerRange($mm, $m),
			new IntegerRange($mm, $p),
			new IntegerRange($m, $pp),
			new IntegerRange($p, $pp),
			new IntegerRange($m, $p)
		];
		$this->checkMatrix($ranges);
	}

	public function testIntegerRangeInvalid(): void {
		$this->expectException(InvalidIntegerRange::class);
		new IntegerRange(new Number(10), new Number(-10));
	}

	public function testRealRangeWithInfinity(): void {
		$m = new Number((string)-3.14);
		$p = new Number((string)3.14);
		$ranges = [
			new RealRange(MinusInfinity::value, PlusInfinity::value),
			new RealRange(MinusInfinity::value, $m),
			new RealRange(MinusInfinity::value, $p),
			new RealRange($m, PlusInfinity::value),
			new RealRange($p, PlusInfinity::value),
			new RealRange($m, $p)
		];
		$this->checkMatrix($ranges);
	}

	public function testRealRangeWithoutInfinity(): void {
		$mm = new Number(-1000);
		$m = new Number((string)-3.14);
		$p = new Number((string)3.14);
		$pp = new Number(1000);
		$ranges = [
			new RealRange($mm, $pp),
			new RealRange($mm, $m),
			new RealRange($mm, $p),
			new RealRange($m, $pp),
			new RealRange($p, $pp),
			new RealRange($m, $p)
		];
		$this->checkMatrix($ranges);
	}

	public function testRealRangeInvalid(): void {
		$this->expectException(InvalidRealRange::class);
		new RealRange(new Number(10), new Number(-10));
	}

	/** @param list<IntegerRange>|list<RealRange> $ranges */
	private function checkMatrix(array $ranges): void {
		$matrix = [
			[1, 0, 0, 0, 0, 0],
			[1, 1, 1, 0, 0, 0],
			[1, 0, 1, 0, 0, 0],
			[1, 0, 0, 1, 0, 0],
			[1, 0, 0, 1, 1, 0],
			[1, 0, 1, 1, 0, 1],
		];
		foreach($ranges as $k0 => $range) {
			foreach($ranges as $k1 => $refRange) {
				self::assertEquals((bool)$matrix[$k0][$k1], $range->isSubRangeOf($refRange),
					sprintf("Range %s is a sub range of %s",
						$range, $refRange));
			}
		}
	}

}