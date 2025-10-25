<?php

namespace Walnut\Lang\Test;

use PHPUnit\Framework\TestCase;
use Walnut\Lang\Blueprint\Common\Identifier\EnumValueIdentifier;
use Walnut\Lang\Blueprint\Common\Identifier\TypeNameIdentifier;
use Walnut\Lang\Blueprint\Program\UnknownType;
use Walnut\Lang\Blueprint\Type\UnknownEnumerationValue;

final class ExceptionTest extends TestCase {

	public function testUnknownType(): void {
		$this->expectException(UnknownType::class);
		UnknownType::withName(new TypeNameIdentifier('X'));
	}

	public function testUnknownEnumerationValue(): void {
		$this->expectException(UnknownEnumerationValue::class);
		UnknownEnumerationValue::of(
			new TypeNameIdentifier('X'),
			new EnumValueIdentifier('Y')
		);
	}

}