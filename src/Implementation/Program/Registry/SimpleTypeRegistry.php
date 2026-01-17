<?php

namespace Walnut\Lang\Implementation\Program\Registry;

use BcMath\Number;
use Walnut\Lang\Blueprint\AST\Parser\EscapeCharHandler;
use Walnut\Lang\Blueprint\Common\Range\InvalidLengthRange;
use Walnut\Lang\Blueprint\Common\Range\MinusInfinity;
use Walnut\Lang\Blueprint\Common\Range\NumberInterval as NumberIntervalInterface;
use Walnut\Lang\Blueprint\Common\Range\PlusInfinity;
use Walnut\Lang\Blueprint\Type\DuplicateSubsetValue;
use Walnut\Lang\Implementation\Common\Range\LengthRange;
use Walnut\Lang\Implementation\Common\Range\NumberInterval;
use Walnut\Lang\Implementation\Common\Range\NumberIntervalEndpoint;
use Walnut\Lang\Implementation\Common\Range\NumberRange;
use Walnut\Lang\Implementation\Type\BytesType;
use Walnut\Lang\Implementation\Type\IntegerSubsetType;
use Walnut\Lang\Implementation\Type\IntegerType;
use Walnut\Lang\Implementation\Type\RealSubsetType;
use Walnut\Lang\Implementation\Type\RealType;
use Walnut\Lang\Implementation\Type\StringSubsetType;
use Walnut\Lang\Implementation\Type\StringType;

trait SimpleTypeRegistry {
	private readonly EscapeCharHandler $escapeCharHandler;

	public function integer(
		int|Number|MinusInfinity $min = MinusInfinity::value,
		int|Number|PlusInfinity $max = PlusInfinity::value
	): IntegerType {
		$rangeMin = is_int($min) ? new Number($min) : $min;
		$rangeMax = is_int($max) ? new Number($max) : $max;
		return new IntegerType(
			new NumberRange(true,
				new NumberInterval(
					$rangeMin === MinusInfinity::value ? MinusInfinity::value :
						new NumberIntervalEndpoint($rangeMin, true),
					$rangeMax === PlusInfinity::value ? PlusInfinity::value :
						new NumberIntervalEndpoint($rangeMax, true)
				)
			)
		);
	}

	/**
	 * @param list<Number> $values
	 * @throws DuplicateSubsetValue
	 */
	public function integerSubset(array $values): IntegerSubsetType {
		return new IntegerSubsetType($values);
	}

	public function integerFull(
		NumberIntervalInterface ... $intervals
	): IntegerType {
		$numberRange = new NumberRange(true, ...
			count($intervals) === 0 ?
				[new NumberInterval(MinusInfinity::value, PlusInfinity::value)] :
				$intervals
		);
		return new IntegerType(
			$numberRange
		);
	}

	public function nonZeroInteger(): IntegerType {
		return $this->integerFull(
			new NumberInterval(MinusInfinity::value, new NumberIntervalEndpoint(new Number(0), false)),
			new NumberInterval(new NumberIntervalEndpoint(new Number(0), false), PlusInfinity::value)
		);
	}

	public function nonZeroReal(): RealType {
		return $this->realFull(
			new NumberInterval(MinusInfinity::value, new NumberIntervalEndpoint(new Number(0), false)),
			new NumberInterval(new NumberIntervalEndpoint(new Number(0), false), PlusInfinity::value)
		);
	}

	public function realFull(
		NumberIntervalInterface ... $intervals
	): RealType {
		$numberRange = new NumberRange(false, ...
			count($intervals) === 0 ?
				[new NumberInterval(MinusInfinity::value, PlusInfinity::value)] :
				$intervals
		);
		return new RealType(
			$numberRange
		);
	}

	public function real(
		float|Number|MinusInfinity $min = MinusInfinity::value,
		float|Number|PlusInfinity $max = PlusInfinity::value
	): RealType {
		$rangeMin = is_float($min) ? new Number((string)$min) : $min;
		$rangeMax = is_float($max) ? new Number((string)$max) : $max;
		return new RealType(
			new NumberRange(false,
				new NumberInterval(
					$rangeMin === MinusInfinity::value ? MinusInfinity::value :
						new NumberIntervalEndpoint($rangeMin, true),
					$rangeMax === PlusInfinity::value ? PlusInfinity::value :
						new NumberIntervalEndpoint($rangeMax, true)
				)
			)
		);
	}
	/**
	 * @param list<Number> $values
	 * @throws DuplicateSubsetValue
	 */
	public function realSubset(array $values): RealSubsetType {
		return new RealSubsetType($values);
	}


	public function string(
		int|Number $minLength = 0,
		int|Number|PlusInfinity $maxLength = PlusInfinity::value
	): StringType {
		return new StringType(new LengthRange(
			is_int($minLength) ? new Number($minLength) : $minLength,
			is_int($maxLength) ? new Number($maxLength) : $maxLength
		));
	}
	/**
	 * @param list<string> $values
	 * @throws DuplicateSubsetValue
	 */
	public function stringSubset(array $values): StringSubsetType {
		return new StringSubsetType($this->string(), $this->escapeCharHandler, $values);
	}

	/** @throws InvalidLengthRange */
	public function bytes(
		int|Number $minLength = 0,
		int|Number|PlusInfinity $maxLength = PlusInfinity::value
	): BytesType {
		return new BytesType(new LengthRange(
			is_int($minLength) ? new Number($minLength) : $minLength,
			is_int($maxLength) ? new Number($maxLength) : $maxLength
		));
	}

}