<?php

namespace Walnut\Lang\Test\Implementation\Common\Range;

use BcMath\Number;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Walnut\Lang\Blueprint\Common\Range\InvalidNumberInterval;
use Walnut\Lang\Blueprint\Common\Range\MinusInfinity;
use Walnut\Lang\Blueprint\Common\Range\PlusInfinity;
use Walnut\Lang\Implementation\Common\Range\NumberInterval;
use Walnut\Lang\Implementation\Common\Range\NumberIntervalEndpoint;

final class NumberIntervalTest extends TestCase {

	protected NumberInterval $all;
	protected NumberInterval $toOpen;
	protected NumberInterval $toClosed;
	protected NumberInterval $fromOpen;
	protected NumberInterval $fromClosed;
	protected NumberInterval $openOpen;
	protected NumberInterval $openClosed;
	protected NumberInterval $closedOpen;
	protected NumberInterval $closedClosed;

	protected Number $min;
	protected Number $max;

	protected function setUp(): void {
		parent::setUp();
		$this->min = new Number('-3.14');
		$this->max = new Number('3.14');
		$minOpen = new NumberIntervalEndpoint($this->min, false);
		$minClosed = new NumberIntervalEndpoint($this->min, true);
		$maxOpen = new NumberIntervalEndpoint($this->max, false);
		$maxClosed = new NumberIntervalEndpoint($this->max, true);

		$this->all = new NumberInterval(MinusInfinity::value, PlusInfinity::value);
		$this->toOpen = new NumberInterval(MinusInfinity::value, $maxOpen);
		$this->toClosed = new NumberInterval(MinusInfinity::value, $maxClosed);
		$this->fromOpen = new NumberInterval($minOpen, PlusInfinity::value);
		$this->fromClosed = new NumberInterval($minClosed, PlusInfinity::value);
		$this->openOpen = new NumberInterval($minOpen, $maxOpen);
		$this->openClosed = new NumberInterval($minOpen, $maxClosed);
		$this->closedOpen = new NumberInterval($minClosed, $maxOpen);
		$this->closedClosed = new NumberInterval($minClosed, $maxClosed);
	}

	#[DataProvider('invalidRangesProvider')]
	public function testInvalidRanges(NumberIntervalEndpoint $start, NumberIntervalEndpoint $end): void {
		$this->expectException(InvalidNumberInterval::class);
		new NumberInterval($start, $end);
	}

	public static function invalidRangesProvider(): array {
		return [
			[
				new NumberIntervalEndpoint(new Number('5'), true),
				new NumberIntervalEndpoint(new Number('3'), true)
			],
			[
				new NumberIntervalEndpoint(new Number('5'), false),
				new NumberIntervalEndpoint(new Number('5'), true)
			],
			[
				new NumberIntervalEndpoint(new Number('5'), true),
				new NumberIntervalEndpoint(new Number('5'), false)
			],
			[
				new NumberIntervalEndpoint(new Number('5'), false),
				new NumberIntervalEndpoint(new Number('5'), false)
			],
		];
	}

	public function testContains(): void {
		$belowMin = new Number('-4.2');
		$aboveMax = new Number('4.2');
		$inside = new Number('0.5');

		$this->assertTrue($this->all->contains($belowMin));
		$this->assertTrue($this->all->contains($this->min));
		$this->assertTrue($this->all->contains($inside));
		$this->assertTrue($this->all->contains($this->max));
		$this->assertTrue($this->all->contains($aboveMax));

		$this->assertTrue($this->toOpen->contains($belowMin));
		$this->assertTrue($this->toOpen->contains($this->min));
		$this->assertTrue($this->toOpen->contains($inside));
		$this->assertFalse($this->toOpen->contains($this->max));
		$this->assertFalse($this->toOpen->contains($aboveMax));

		$this->assertTrue($this->toClosed->contains($belowMin));
		$this->assertTrue($this->toClosed->contains($this->min));
		$this->assertTrue($this->toClosed->contains($inside));
		$this->assertTrue($this->toClosed->contains($this->max));
		$this->assertFalse($this->toClosed->contains($aboveMax));

		$this->assertFalse($this->fromOpen->contains($belowMin));
		$this->assertFalse($this->fromOpen->contains($this->min));
		$this->assertTrue($this->fromOpen->contains($inside));
		$this->assertTrue($this->fromOpen->contains($this->max));
		$this->assertTrue($this->fromOpen->contains($aboveMax));

		$this->assertFalse($this->fromClosed->contains($belowMin));
		$this->assertTrue($this->fromClosed->contains($this->min));
		$this->assertTrue($this->fromClosed->contains($inside));
		$this->assertTrue($this->fromClosed->contains($this->max));
		$this->assertTrue($this->fromClosed->contains($aboveMax));

		$this->assertFalse($this->openOpen->contains($belowMin));
		$this->assertFalse($this->openOpen->contains($this->min));
		$this->assertTrue($this->openOpen->contains($inside));
		$this->assertFalse($this->openOpen->contains($this->max));
		$this->assertFalse($this->openOpen->contains($aboveMax));

		$this->assertFalse($this->openClosed->contains($belowMin));
		$this->assertFalse($this->openClosed->contains($this->min));
		$this->assertTrue($this->openClosed->contains($inside));
		$this->assertTrue($this->openClosed->contains($this->max));
		$this->assertFalse($this->openClosed->contains($aboveMax));

		$this->assertFalse($this->closedOpen->contains($belowMin));
		$this->assertTrue($this->closedOpen->contains($this->min));
		$this->assertTrue($this->closedOpen->contains($inside));
		$this->assertFalse($this->closedOpen->contains($this->max));
		$this->assertFalse($this->closedOpen->contains($aboveMax));

		$this->assertFalse($this->closedClosed->contains($belowMin));
		$this->assertTrue($this->closedClosed->contains($this->min));
		$this->assertTrue($this->closedClosed->contains($inside));
		$this->assertTrue($this->closedClosed->contains($this->max));
		$this->assertFalse($this->closedClosed->contains($aboveMax));
	}

	public function testContainsInterval(): void {
		$this->assertTrue($this->all->containsInterval($this->all));
		$this->assertTrue($this->all->containsInterval($this->toOpen));
		$this->assertTrue($this->all->containsInterval($this->toClosed));
		$this->assertTrue($this->all->containsInterval($this->fromOpen));
		$this->assertTrue($this->all->containsInterval($this->fromClosed));
		$this->assertTrue($this->all->containsInterval($this->openOpen));
		$this->assertTrue($this->all->containsInterval($this->openClosed));
		$this->assertTrue($this->all->containsInterval($this->closedOpen));
		$this->assertTrue($this->all->containsInterval($this->closedClosed));

		$this->assertFalse($this->toOpen->containsInterval($this->all));
		$this->assertTrue($this->toOpen->containsInterval($this->toOpen));
		$this->assertFalse($this->toOpen->containsInterval($this->toClosed));
		$this->assertFalse($this->toOpen->containsInterval($this->fromOpen));
		$this->assertFalse($this->toOpen->containsInterval($this->fromClosed));
		$this->assertTrue($this->toOpen->containsInterval($this->openOpen));
		$this->assertFalse($this->toOpen->containsInterval($this->openClosed));
		$this->assertTrue($this->toOpen->containsInterval($this->closedOpen));
		$this->assertFalse($this->toOpen->containsInterval($this->closedClosed));

		$this->assertFalse($this->toClosed->containsInterval($this->all));
		$this->assertTrue($this->toClosed->containsInterval($this->toOpen));
		$this->assertTrue($this->toClosed->containsInterval($this->toClosed));
		$this->assertFalse($this->toClosed->containsInterval($this->fromOpen));
		$this->assertFalse($this->toClosed->containsInterval($this->fromClosed));
		$this->assertTrue($this->toClosed->containsInterval($this->openOpen));
		$this->assertTrue($this->toClosed->containsInterval($this->openClosed));
		$this->assertTrue($this->toClosed->containsInterval($this->closedOpen));
		$this->assertTrue($this->toClosed->containsInterval($this->closedClosed));

		$this->assertFalse($this->fromOpen->containsInterval($this->all));
		$this->assertFalse($this->fromOpen->containsInterval($this->toOpen));
		$this->assertFalse($this->fromOpen->containsInterval($this->toClosed));
		$this->assertTrue($this->fromOpen->containsInterval($this->fromOpen));
		$this->assertFalse($this->fromOpen->containsInterval($this->fromClosed));
		$this->assertTrue($this->fromOpen->containsInterval($this->openOpen));
		$this->assertTrue($this->fromOpen->containsInterval($this->openClosed));
		$this->assertFalse($this->fromOpen->containsInterval($this->closedOpen));
		$this->assertFalse($this->fromOpen->containsInterval($this->closedClosed));

		$this->assertFalse($this->fromClosed->containsInterval($this->all));
		$this->assertFalse($this->fromClosed->containsInterval($this->toOpen));
		$this->assertFalse($this->fromClosed->containsInterval($this->toClosed));
		$this->assertTrue($this->fromClosed->containsInterval($this->fromOpen));
		$this->assertTrue($this->fromClosed->containsInterval($this->fromClosed));
		$this->assertTrue($this->fromClosed->containsInterval($this->openOpen));
		$this->assertTrue($this->fromClosed->containsInterval($this->openClosed));
		$this->assertTrue($this->fromClosed->containsInterval($this->closedOpen));
		$this->assertTrue($this->fromClosed->containsInterval($this->closedClosed));

		$this->assertFalse($this->openOpen->containsInterval($this->all));
		$this->assertFalse($this->openOpen->containsInterval($this->toOpen));
		$this->assertFalse($this->openOpen->containsInterval($this->toClosed));
		$this->assertFalse($this->openOpen->containsInterval($this->fromOpen));
		$this->assertFalse($this->openOpen->containsInterval($this->fromClosed));
		$this->assertTrue($this->openOpen->containsInterval($this->openOpen));
		$this->assertFalse($this->openOpen->containsInterval($this->openClosed));
		$this->assertFalse($this->openOpen->containsInterval($this->closedOpen));
		$this->assertFalse($this->openOpen->containsInterval($this->closedClosed));

		$this->assertFalse($this->openClosed->containsInterval($this->all));
		$this->assertFalse($this->openClosed->containsInterval($this->toOpen));
		$this->assertFalse($this->openClosed->containsInterval($this->toClosed));
		$this->assertFalse($this->openClosed->containsInterval($this->fromOpen));
		$this->assertFalse($this->openClosed->containsInterval($this->fromClosed));
		$this->assertTrue($this->openClosed->containsInterval($this->openOpen));
		$this->assertTrue($this->openClosed->containsInterval($this->openClosed));
		$this->assertFalse($this->openClosed->containsInterval($this->closedOpen));
		$this->assertFalse($this->openClosed->containsInterval($this->closedClosed));

		$this->assertFalse($this->closedOpen->containsInterval($this->all));
		$this->assertFalse($this->closedOpen->containsInterval($this->toOpen));
		$this->assertFalse($this->closedOpen->containsInterval($this->toClosed));
		$this->assertFalse($this->closedOpen->containsInterval($this->fromOpen));
		$this->assertFalse($this->closedOpen->containsInterval($this->fromClosed));
		$this->assertTrue($this->closedOpen->containsInterval($this->openOpen));
		$this->assertFalse($this->closedOpen->containsInterval($this->openClosed));
		$this->assertTrue($this->closedOpen->containsInterval($this->closedOpen));
		$this->assertFalse($this->closedOpen->containsInterval($this->closedClosed));

		$this->assertFalse($this->closedClosed->containsInterval($this->all));
		$this->assertFalse($this->closedClosed->containsInterval($this->toOpen));
		$this->assertFalse($this->closedClosed->containsInterval($this->toClosed));
		$this->assertFalse($this->closedClosed->containsInterval($this->fromOpen));
		$this->assertFalse($this->closedClosed->containsInterval($this->fromClosed));
		$this->assertTrue($this->closedClosed->containsInterval($this->openOpen));
		$this->assertTrue($this->closedClosed->containsInterval($this->openClosed));
		$this->assertTrue($this->closedClosed->containsInterval($this->closedOpen));
		$this->assertTrue($this->closedClosed->containsInterval($this->closedClosed));
	}

	public function testIntersectsWith(): void {
		$i1 = new NumberInterval(
			MinusInfinity::value,
			new NumberIntervalEndpoint(new Number('-4'), true)
		);
		$i2 = new NumberInterval(
			MinusInfinity::value,
			new NumberIntervalEndpoint($this->min, false)
		);
		$i3 = new NumberInterval(
			MinusInfinity::value,
			new NumberIntervalEndpoint($this->min, true)
		);
		$i4 = new NumberInterval(
			new NumberIntervalEndpoint(new Number('-15'), true),
			new NumberIntervalEndpoint(new Number('-4'), true)
		);
		$i5 = new NumberInterval(
			new NumberIntervalEndpoint(new Number('-15'), true),
			new NumberIntervalEndpoint($this->min, false)
		);
		$i6 = new NumberInterval(
			new NumberIntervalEndpoint(new Number('-15'), true),
			new NumberIntervalEndpoint($this->min, true)
		);

		$this->assertTrue($i1->intersectsWith($i1));
		$this->assertTrue($i1->intersectsWith($i2));
		$this->assertTrue($i1->intersectsWith($i3));
		$this->assertTrue($i1->intersectsWith($i4));
		$this->assertTrue($i1->intersectsWith($i5));
		$this->assertTrue($i1->intersectsWith($i6));
		$this->assertTrue($i1->intersectsWith($this->all));
		$this->assertTrue($i1->intersectsWith($this->toOpen));
		$this->assertTrue($i1->intersectsWith($this->toClosed));
		$this->assertFalse($i1->intersectsWith($this->fromOpen));
		$this->assertFalse($i1->intersectsWith($this->fromClosed));
		$this->assertFalse($i1->intersectsWith($this->openOpen));
		$this->assertFalse($i1->intersectsWith($this->openClosed));
		$this->assertFalse($i1->intersectsWith($this->closedOpen));
		$this->assertFalse($i1->intersectsWith($this->closedClosed));

		$this->assertTrue($i2->intersectsWith($i1));
		$this->assertTrue($i2->intersectsWith($i2));
		$this->assertTrue($i2->intersectsWith($i3));
		$this->assertTrue($i2->intersectsWith($i4));
		$this->assertTrue($i2->intersectsWith($i5));
		$this->assertTrue($i2->intersectsWith($i6));
		$this->assertTrue($i2->intersectsWith($this->all));
		$this->assertTrue($i2->intersectsWith($this->toOpen));
		$this->assertTrue($i2->intersectsWith($this->toClosed));
		$this->assertFalse($i2->intersectsWith($this->fromOpen));
		$this->assertFalse($i2->intersectsWith($this->fromClosed));
		$this->assertFalse($i2->intersectsWith($this->openOpen));
		$this->assertFalse($i2->intersectsWith($this->openClosed));
		$this->assertFalse($i2->intersectsWith($this->closedOpen));
		$this->assertFalse($i2->intersectsWith($this->closedClosed));

		$this->assertTrue($i3->intersectsWith($i1));
		$this->assertTrue($i3->intersectsWith($i2));
		$this->assertTrue($i3->intersectsWith($i3));
		$this->assertTrue($i3->intersectsWith($i4));
		$this->assertTrue($i3->intersectsWith($i5));
		$this->assertTrue($i3->intersectsWith($i6));
		$this->assertTrue($i3->intersectsWith($this->all));
		$this->assertTrue($i3->intersectsWith($this->toOpen));
		$this->assertTrue($i3->intersectsWith($this->toClosed));
		$this->assertFalse($i3->intersectsWith($this->fromOpen));
		$this->assertTrue($i3->intersectsWith($this->fromClosed));
		$this->assertFalse($i3->intersectsWith($this->openOpen));
		$this->assertFalse($i3->intersectsWith($this->openClosed));
		$this->assertTrue($i3->intersectsWith($this->closedOpen));
		$this->assertTrue($i3->intersectsWith($this->closedClosed));

		$this->assertTrue($i4->intersectsWith($i1));
		$this->assertTrue($i4->intersectsWith($i2));
		$this->assertTrue($i4->intersectsWith($i3));
		$this->assertTrue($i4->intersectsWith($i4));
		$this->assertTrue($i4->intersectsWith($i5));
		$this->assertTrue($i4->intersectsWith($i6));
		$this->assertTrue($i4->intersectsWith($this->all));
		$this->assertTrue($i4->intersectsWith($this->toOpen));
		$this->assertTrue($i4->intersectsWith($this->toClosed));
		$this->assertFalse($i4->intersectsWith($this->fromOpen));
		$this->assertFalse($i4->intersectsWith($this->fromClosed));
		$this->assertFalse($i4->intersectsWith($this->openOpen));
		$this->assertFalse($i4->intersectsWith($this->openClosed));
		$this->assertFalse($i4->intersectsWith($this->closedOpen));
		$this->assertFalse($i4->intersectsWith($this->closedClosed));

		$this->assertTrue($i5->intersectsWith($i1));
		$this->assertTrue($i5->intersectsWith($i2));
		$this->assertTrue($i5->intersectsWith($i3));
		$this->assertTrue($i5->intersectsWith($i4));
		$this->assertTrue($i5->intersectsWith($i5));
		$this->assertTrue($i5->intersectsWith($i6));
		$this->assertTrue($i5->intersectsWith($this->all));
		$this->assertTrue($i5->intersectsWith($this->toOpen));
		$this->assertTrue($i5->intersectsWith($this->toClosed));
		$this->assertFalse($i5->intersectsWith($this->fromOpen));
		$this->assertFalse($i5->intersectsWith($this->fromClosed));
		$this->assertFalse($i5->intersectsWith($this->openOpen));
		$this->assertFalse($i5->intersectsWith($this->openClosed));
		$this->assertFalse($i5->intersectsWith($this->closedOpen));
		$this->assertFalse($i5->intersectsWith($this->closedClosed));

		$this->assertTrue($i6->intersectsWith($i1));
		$this->assertTrue($i6->intersectsWith($i2));
		$this->assertTrue($i6->intersectsWith($i3));
		$this->assertTrue($i6->intersectsWith($i4));
		$this->assertTrue($i6->intersectsWith($i5));
		$this->assertTrue($i6->intersectsWith($i6));
		$this->assertTrue($i6->intersectsWith($this->all));
		$this->assertTrue($i6->intersectsWith($this->toOpen));
		$this->assertTrue($i6->intersectsWith($this->toClosed));
		$this->assertFalse($i6->intersectsWith($this->fromOpen));
		$this->assertTrue($i6->intersectsWith($this->fromClosed));
		$this->assertFalse($i6->intersectsWith($this->openOpen));
		$this->assertFalse($i6->intersectsWith($this->openClosed));
		$this->assertTrue($i6->intersectsWith($this->closedOpen));
		$this->assertTrue($i6->intersectsWith($this->closedClosed));
	}

	public function testUnitesWith(): void {
		$i1 = new NumberInterval(
			MinusInfinity::value,
			new NumberIntervalEndpoint(new Number('-4'), true)
		);
		$i2 = new NumberInterval(
			MinusInfinity::value,
			new NumberIntervalEndpoint($this->min, false)
		);
		$i3 = new NumberInterval(
			MinusInfinity::value,
			new NumberIntervalEndpoint($this->min, true)
		);
		$i4 = new NumberInterval(
			new NumberIntervalEndpoint(new Number('-15'), true),
			new NumberIntervalEndpoint(new Number('-4'), true)
		);
		$i5 = new NumberInterval(
			new NumberIntervalEndpoint(new Number('-15'), true),
			new NumberIntervalEndpoint($this->min, false)
		);
		$i6 = new NumberInterval(
			new NumberIntervalEndpoint(new Number('-15'), true),
			new NumberIntervalEndpoint($this->min, true)
		);
		$i7 = new NumberInterval(
			new NumberIntervalEndpoint(new Number('-15'), true),
			new NumberIntervalEndpoint(new Number('-5'), true)
		);

		$this->assertFalse($i1->unitesWith($this->fromOpen, true));
		$this->assertTrue($i1->unitesWith($this->fromClosed, true));
		$this->assertFalse($i7->unitesWith($this->fromOpen, true));
		$this->assertFalse($i7->unitesWith($this->fromClosed, true));


		$this->assertTrue($i1->unitesWith($i1));
		$this->assertTrue($i1->unitesWith($i2));
		$this->assertTrue($i1->unitesWith($i3));
		$this->assertTrue($i1->unitesWith($i4));
		$this->assertTrue($i1->unitesWith($i5));
		$this->assertTrue($i1->unitesWith($i6));
		$this->assertTrue($i1->unitesWith($this->all));
		$this->assertTrue($i1->unitesWith($this->toOpen));
		$this->assertTrue($i1->unitesWith($this->toClosed));
		$this->assertFalse($i1->unitesWith($this->fromOpen));
		$this->assertFalse($i1->unitesWith($this->fromClosed));
		$this->assertFalse($i1->unitesWith($this->openOpen));
		$this->assertFalse($i1->unitesWith($this->openClosed));
		$this->assertFalse($i1->unitesWith($this->closedOpen));
		$this->assertFalse($i1->unitesWith($this->closedClosed));

		$this->assertTrue($i2->unitesWith($i1));
		$this->assertTrue($i2->unitesWith($i2));
		$this->assertTrue($i2->unitesWith($i3));
		$this->assertTrue($i2->unitesWith($i4));
		$this->assertTrue($i2->unitesWith($i5));
		$this->assertTrue($i2->unitesWith($i6));
		$this->assertTrue($i2->unitesWith($this->all));
		$this->assertTrue($i2->unitesWith($this->toOpen));
		$this->assertTrue($i2->unitesWith($this->toClosed));
		$this->assertFalse($i2->unitesWith($this->fromOpen));
		$this->assertTrue($i2->unitesWith($this->fromClosed));
		$this->assertFalse($i2->unitesWith($this->openOpen));
		$this->assertFalse($i2->unitesWith($this->openClosed));
		$this->assertTrue($i2->unitesWith($this->closedOpen));
		$this->assertTrue($i2->unitesWith($this->closedClosed));

		$this->assertTrue($i3->unitesWith($i1));
		$this->assertTrue($i3->unitesWith($i2));
		$this->assertTrue($i3->unitesWith($i3));
		$this->assertTrue($i3->unitesWith($i4));
		$this->assertTrue($i3->unitesWith($i5));
		$this->assertTrue($i3->unitesWith($i6));
		$this->assertTrue($i3->unitesWith($this->all));
		$this->assertTrue($i3->unitesWith($this->toOpen));
		$this->assertTrue($i3->unitesWith($this->toClosed));
		$this->assertTrue($i3->unitesWith($this->fromOpen));
		$this->assertTrue($i3->unitesWith($this->fromClosed));
		$this->assertTrue($i3->unitesWith($this->openOpen));
		$this->assertTrue($i3->unitesWith($this->openClosed));
		$this->assertTrue($i3->unitesWith($this->closedOpen));
		$this->assertTrue($i3->unitesWith($this->closedClosed));

		$this->assertTrue($i4->unitesWith($i1));
		$this->assertTrue($i4->unitesWith($i2));
		$this->assertTrue($i4->unitesWith($i3));
		$this->assertTrue($i4->unitesWith($i4));
		$this->assertTrue($i4->unitesWith($i5));
		$this->assertTrue($i4->unitesWith($i6));
		$this->assertTrue($i4->unitesWith($this->all));
		$this->assertTrue($i4->unitesWith($this->toOpen));
		$this->assertTrue($i4->unitesWith($this->toClosed));
		$this->assertFalse($i4->unitesWith($this->fromOpen));
		$this->assertFalse($i4->unitesWith($this->fromClosed));
		$this->assertFalse($i4->unitesWith($this->openOpen));
		$this->assertFalse($i4->unitesWith($this->openClosed));
		$this->assertFalse($i4->unitesWith($this->closedOpen));
		$this->assertFalse($i4->unitesWith($this->closedClosed));

		$this->assertTrue($i5->unitesWith($i1));
		$this->assertTrue($i5->unitesWith($i2));
		$this->assertTrue($i5->unitesWith($i3));
		$this->assertTrue($i5->unitesWith($i4));
		$this->assertTrue($i5->unitesWith($i5));
		$this->assertTrue($i5->unitesWith($i6));
		$this->assertTrue($i5->unitesWith($this->all));
		$this->assertTrue($i5->unitesWith($this->toOpen));
		$this->assertTrue($i5->unitesWith($this->toClosed));
		$this->assertFalse($i5->unitesWith($this->fromOpen));
		$this->assertTrue($i5->unitesWith($this->fromClosed));
		$this->assertFalse($i5->unitesWith($this->openOpen));
		$this->assertFalse($i5->unitesWith($this->openClosed));
		$this->assertTrue($i5->unitesWith($this->closedOpen));
		$this->assertTrue($i5->unitesWith($this->closedClosed));

		$this->assertTrue($i6->unitesWith($i1));
		$this->assertTrue($i6->unitesWith($i2));
		$this->assertTrue($i6->unitesWith($i3));
		$this->assertTrue($i6->unitesWith($i4));
		$this->assertTrue($i6->unitesWith($i5));
		$this->assertTrue($i6->unitesWith($i6));
		$this->assertTrue($i6->unitesWith($this->all));
		$this->assertTrue($i6->unitesWith($this->toOpen));
		$this->assertTrue($i6->unitesWith($this->toClosed));
		$this->assertTrue($i6->unitesWith($this->fromOpen));
		$this->assertTrue($i6->unitesWith($this->fromClosed));
		$this->assertTrue($i6->unitesWith($this->openOpen));
		$this->assertTrue($i6->unitesWith($this->openClosed));
		$this->assertTrue($i6->unitesWith($this->closedOpen));
		$this->assertTrue($i6->unitesWith($this->closedClosed));
	}

	public function testToString(): void {
		$this->assertSame('(-Infinity..+Infinity)', (string) $this->all);
		$this->assertSame('(-Infinity..3.14)', (string) $this->toOpen);
		$this->assertSame('(-Infinity..3.14]', (string) $this->toClosed);
		$this->assertSame('(-3.14..+Infinity)', (string) $this->fromOpen);
		$this->assertSame('[-3.14..+Infinity)', (string) $this->fromClosed);
		$this->assertSame('(-3.14..3.14)', (string) $this->openOpen);
		$this->assertSame('(-3.14..3.14]', (string) $this->openClosed);
		$this->assertSame('[-3.14..3.14)', (string) $this->closedOpen);
		$this->assertSame('[-3.14..3.14]', (string) $this->closedClosed);

		// Test single number intervals
		$single = NumberInterval::singleNumber(new Number('42.753'));
		$this->assertSame('42.753', (string)$single);

		$i1 = new NumberInterval(
			new NumberIntervalEndpoint(new Number('-15.4'), false),
			new NumberIntervalEndpoint(new Number('3.14'), true)
		);
		$i2 = new NumberInterval(
			new NumberIntervalEndpoint(new Number('0.2'), true),
			PlusInfinity::value
		);
		$this->assertEquals('[0.2..3.14]', (string)$i1->intersectionWith($i2, false));
		$this->assertEquals('[0.2..3.14]', (string)$i2->intersectionWith($i1, false));
		$this->assertEquals('(-15.4..+Infinity)', (string)$i1->unionWith($i2, false));
	}

}