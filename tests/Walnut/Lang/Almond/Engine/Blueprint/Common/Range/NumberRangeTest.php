<?php

namespace Walnut\Lang\Test\Almond\Engine\Blueprint\Common\Range;

use BcMath\Number;
use PHPUnit\Framework\TestCase;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Range\InvalidNumberRange;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Range\MinusInfinity;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Range\NumberInterval;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Range\NumberIntervalEndpoint;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Range\NumberRange;

final class NumberRangeTest extends TestCase {

	private NumberRange $range1;
	private NumberRange $range2;

	protected function setUp(): void {
		parent::setUp();

		$this->range1 = new NumberRange(false,
			new NumberInterval(
				MinusInfinity::value,
				new NumberIntervalEndpoint(new Number('7'), true)
			),
			NumberInterval::singleNumber(new Number('12')),
			NumberInterval::singleNumber(new Number('15.4')),
			new NumberInterval(
				new NumberIntervalEndpoint(new Number('20.3'), true),
				new NumberIntervalEndpoint(new Number('29'), false)
			),
		);
		$this->range2 = new NumberRange(false,
			new NumberInterval(
				new NumberIntervalEndpoint(new Number('7'), true),
				new NumberIntervalEndpoint(new Number('12'), false),
			),
			NumberInterval::singleNumber(new Number('12.5')),
			NumberInterval::singleNumber(new Number('15.4')),
			NumberInterval::singleNumber(new Number('10.4')),
			new NumberInterval(
				new NumberIntervalEndpoint(new Number('-3'), true),
				new NumberIntervalEndpoint(new Number('9.2'), false),
			),
			new NumberInterval(
				new NumberIntervalEndpoint(new Number('15.4'), false),
				new NumberIntervalEndpoint(new Number('25.09'), false),
			),
		);
	}

	public function testContainsInterval(): void {
		$range = $this->range2;
		$this->assertTrue($range->containsInterval(
			NumberInterval::singleNumber(new Number('15.4')),
		));
		$this->assertFalse($range->containsInterval(
			NumberInterval::singleNumber(new Number('45.4')),
		));
		$this->assertTrue($range->containsInterval(
			new NumberInterval(
				new NumberIntervalEndpoint(new Number('-2.01'), true),
				new NumberIntervalEndpoint(new Number('10'), false),
			)
		));
		$this->assertFalse($range->containsInterval(
			new NumberInterval(
				new NumberIntervalEndpoint(new Number('20'), true),
				new NumberIntervalEndpoint(new Number('25.09'), true),
			)
		));
	}

	public function testContainsRange(): void {
		$range = $this->range2;
		$this->assertTrue($range->containsRange(
			new NumberRange(false,
				NumberInterval::singleNumber(new Number('15.4'))
			),
		));
		$this->assertFalse($range->containsRange(
			new NumberRange(false,
				NumberInterval::singleNumber(new Number('45.4')),
			),
		));
		$this->assertTrue($range->containsRange(
			new NumberRange(false,
				NumberInterval::singleNumber(new Number('15.4')),
				new NumberInterval(
					new NumberIntervalEndpoint(new Number('-2.01'), true),
					new NumberIntervalEndpoint(new Number('10'), false),
				)
			),
		));
		$this->assertFalse($range->containsRange(
			new NumberRange(false,
				new NumberInterval(
					new NumberIntervalEndpoint(new Number('-2.01'), true),
					new NumberIntervalEndpoint(new Number('10'), false),
				),
				new NumberInterval(
					new NumberIntervalEndpoint(new Number('20'), true),
					new NumberIntervalEndpoint(new Number('25.09'), true),
				)
			),
		));

		$r31 = new NumberRange(
			true,
			NumberInterval::singleNumber(new Number(1)),
			NumberInterval::singleNumber(new Number(2)),
			NumberInterval::singleNumber(new Number(3)),
		);
		$r13 = new NumberRange(
			true,
			new NumberInterval(
				new NumberIntervalEndpoint(new Number(1), true),
				new NumberIntervalEndpoint(new Number(3), true),
			)
		);
		$this->assertTrue($r31->containsRange($r13));
		$this->assertTrue($r13->containsRange($r31));
	}

	public function testContains(): void {
		$range = $this->range1;
		$this->assertTrue($range->contains(new Number('5')));
		$this->assertTrue($range->contains(new Number('7')));
		$this->assertFalse($range->contains(new Number('8')));
		$this->assertTrue($range->contains(new Number('12')));
		$this->assertTrue($range->contains(new Number('15.4')));
		$this->assertFalse($range->contains(new Number('20')));
		$this->assertTrue($range->contains(new Number('20.3')));
		$this->assertTrue($range->contains(new Number('25')));
		$this->assertFalse($range->contains(new Number('29')));
		$this->assertFalse($range->contains(new Number('1000')));
	}

	public function testInvalidRange(): void {
		$this->expectException(InvalidNumberRange::class);
		new NumberRange(false);
	}

	public function testAdjustedIntervals(): void {
		$range = $this->range2;
		$this->assertEquals('[-3..12), 12.5, [15.4..25.09)', implode(', ', $range->adjustedIntervals));
	}

}