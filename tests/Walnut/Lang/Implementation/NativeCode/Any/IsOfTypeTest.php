<?php

namespace Walnut\Lang\Implementation\NativeCode\Any;

use Walnut\Lang\Blueprint\Common\Identifier\EnumValueIdentifier;
use Walnut\Lang\Blueprint\Common\Identifier\TypeNameIdentifier;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Value\Value;
use Walnut\Lang\Test\Implementation\BaseProgramTestHelper;

final class IsOfTypeTest extends BaseProgramTestHelper {

	private function callIsOfType(Value $value, Type $type, bool $expected): void {
		$this->testMethodCall(
			$this->expressionRegistry->constant($value),
			'isOfType',
			$this->expressionRegistry->constant(
				$this->valueRegistry->type($type)
			),
			$this->valueRegistry->boolean($expected)
		);
	}

	public function testIsSubtypeOf(): void {
		$a = $this->typeRegistry->any;

		$v1 = $this->valueRegistry->real(123.5);
		$v2 = $this->valueRegistry->real(456.5);
		$t1 = $this->typeRegistry->realSubset([$v1, $v2]);
		$t2 = $this->typeRegistry->realSubset([$v2]);
		$t3 = $this->typeRegistry->real(100.5, 200.5);
		$t4 = $this->typeRegistry->real();
		$z = $this->typeRegistry->string();

		$this->callIsOfType($v1, $t1, true);
		$this->callIsOfType($v2, $t1, true);
		$this->callIsOfType($v1, $t2, false);
		$this->callIsOfType($v2, $t2, true);
		$this->callIsOfType($v1, $t3, true);
		$this->callIsOfType($v2, $t3, false);
		$this->callIsOfType($v1, $t4, true);
		$this->callIsOfType($v2, $t4, true);
		$this->callIsOfType($v1, $z, false);
		$this->callIsOfType($v1, $this->typeRegistry->integer(), false);
		$this->callIsOfType($v1, $a, true);

		$v1 = $this->valueRegistry->integer(123);
		$v2 = $this->valueRegistry->integer(456);
		$t1 = $this->typeRegistry->integerSubset([$v1, $v2]);
		$t2 = $this->typeRegistry->integerSubset([$v2]);
		$t3 = $this->typeRegistry->integer(100, 200);
		$t4 = $this->typeRegistry->integer();
		$z = $this->typeRegistry->string();

		$this->callIsOfType($v1, $t1, true);
		$this->callIsOfType($v2, $t1, true);
		$this->callIsOfType($v1, $t2, false);
		$this->callIsOfType($v2, $t2, true);
		$this->callIsOfType($v1, $t3, true);
		$this->callIsOfType($v2, $t3, false);
		$this->callIsOfType($v1, $t4, true);
		$this->callIsOfType($v2, $t4, true);
		$this->callIsOfType($v1, $z, false);
		$this->callIsOfType($v1, $a, true);

		//check integer against real types
		//$this->callIsOfType($v1, $r1, true);
		//$this->callIsOfType($v2, $r1, true);
		//$this->callIsOfType($v1, $r2, false);
		//$this->callIsOfType($v2, $r2, true);
		//$this->callIsOfType($v1, $r3, true);
		//$this->callIsOfType($v2, $r3, false);
		//$this->callIsOfType($v1, $r4, true);
		//$this->callIsOfType($v2, $r4, true);

		$v1 = $this->valueRegistry->string("Hi");
		$v2 = $this->valueRegistry->string("Hello");
		$t1 = $this->typeRegistry->stringSubset([$v1, $v2]);
		$t2 = $this->typeRegistry->stringSubset([$v2]);
		$t3 = $this->typeRegistry->string(1, 4);
		$t4 = $this->typeRegistry->string();
		$z = $this->typeRegistry->integer();

		$this->callIsOfType($v1, $t1, true);
		$this->callIsOfType($v2, $t1, true);
		$this->callIsOfType($v1, $t2, false);
		$this->callIsOfType($v2, $t2, true);
		$this->callIsOfType($v1, $t3, true);
		$this->callIsOfType($v2, $t3, false);
		$this->callIsOfType($v1, $t4, true);
		$this->callIsOfType($v2, $t4, true);
		$this->callIsOfType($v1, $z, false);
		$this->callIsOfType($v1, $a, true);

		$v1 = $this->valueRegistry->tuple([$this->valueRegistry->string("Hi"), $this->valueRegistry->integer(5)]);
		$v2 = $this->valueRegistry->tuple([$this->valueRegistry->string("Hello"), $this->valueRegistry->string("World")]);
		$t1 = $this->typeRegistry->tuple([$this->typeRegistry->string(), $this->typeRegistry->string()]);
		$t2 = $this->typeRegistry->tuple([$this->typeRegistry->string()]);
		$t3 = $this->typeRegistry->array($this->typeRegistry->string());
		$t4 = $this->typeRegistry->array($this->typeRegistry->string(), 0, 10);
		$t5 = $this->typeRegistry->array($this->typeRegistry->string(), 3, 10);
		$t6 = $this->typeRegistry->array($this->typeRegistry->union([
			$this->typeRegistry->string(), $this->typeRegistry->integer()
		]));
		$t7 = $this->typeRegistry->tuple([$this->typeRegistry->string(), $this->typeRegistry->string()], $this->typeRegistry->any);
		$t8 = $this->typeRegistry->tuple([$this->typeRegistry->string()], $this->typeRegistry->any);
		$t9 = $this->typeRegistry->tuple([$this->typeRegistry->string(), $this->typeRegistry->string()], $this->typeRegistry->string());
		$t10 = $this->typeRegistry->tuple([$this->typeRegistry->string()], $this->typeRegistry->string());
		$z = $this->typeRegistry->integer();

		$this->callIsOfType($v1, $t1, false);
		$this->callIsOfType($v2, $t1, true);
		$this->callIsOfType($v1, $t2, false);
		$this->callIsOfType($v2, $t2, false);
		$this->callIsOfType($v1, $t3, false);
		$this->callIsOfType($v2, $t3, true);
		$this->callIsOfType($v1, $t4, false);
		$this->callIsOfType($v2, $t4, true);
		$this->callIsOfType($v1, $t5, false);
		$this->callIsOfType($v2, $t5, false);
		$this->callIsOfType($v1, $t6, true);
		$this->callIsOfType($v2, $t6, true);
		$this->callIsOfType($v1, $t7, false);
		$this->callIsOfType($v2, $t7, true);
		$this->callIsOfType($v1, $t8, true);
		$this->callIsOfType($v2, $t8, true);
		$this->callIsOfType($v1, $t9, false);
		$this->callIsOfType($v2, $t9, true);
		$this->callIsOfType($v1, $t10, false);
		$this->callIsOfType($v2, $t10, true);
		$this->callIsOfType($v1, $z, false);
		$this->callIsOfType($v1, $a, true);

		$v1 = $this->valueRegistry->record(['a' => $this->valueRegistry->string("Hi"), 'b' => $this->valueRegistry->integer(5)]);
		$v2 = $this->valueRegistry->record(['a' => $this->valueRegistry->string("Hello"), 'c' => $this->valueRegistry->string("World")]);
		$t1 = $this->typeRegistry->record(['a' => $this->typeRegistry->string(), 'c' => $this->typeRegistry->string()]);
		$t2 = $this->typeRegistry->record(['a' => $this->typeRegistry->string()]);
		$t3 = $this->typeRegistry->map($this->typeRegistry->string());
		$t4 = $this->typeRegistry->map($this->typeRegistry->string(), 0, 10);
		$t5 = $this->typeRegistry->map($this->typeRegistry->string(), 3, 10);
		$t6 = $this->typeRegistry->map($this->typeRegistry->union([
			$this->typeRegistry->string(), $this->typeRegistry->integer()
		]));
		$t7 = $this->typeRegistry->record(['a' => $this->typeRegistry->string(), 'b' => $this->typeRegistry->integer(), 'c' => $this->typeRegistry->string()]);
		$t8 = $this->typeRegistry->record(['a' => $this->typeRegistry->string(), 'c' => $this->typeRegistry->string()], $this->typeRegistry->any);
		$t9 = $this->typeRegistry->record(['a' => $this->typeRegistry->string()], $this->typeRegistry->any);
		$t10 = $this->typeRegistry->record(['a' => $this->typeRegistry->string(), 'c' => $this->typeRegistry->string()], $this->typeRegistry->string());
		$t11 = $this->typeRegistry->record(['a' => $this->typeRegistry->string()], $this->typeRegistry->string());
		$z = $this->typeRegistry->integer();

		$this->callIsOfType($v1, $t1, false);
		$this->callIsOfType($v2, $t1, true);
		$this->callIsOfType($v1, $t2, false);
		$this->callIsOfType($v2, $t2, false);
		$this->callIsOfType($v1, $t3, false);
		$this->callIsOfType($v2, $t3, true);
		$this->callIsOfType($v1, $t4, false);
		$this->callIsOfType($v2, $t4, true);
		$this->callIsOfType($v1, $t5, false);
		$this->callIsOfType($v2, $t5, false);
		$this->callIsOfType($v1, $t6, true);
		$this->callIsOfType($v2, $t6, true);
		$this->callIsOfType($v1, $t7, false);
		$this->callIsOfType($v2, $t7, false);
		$this->callIsOfType($v1, $t8, false);
		$this->callIsOfType($v2, $t8, true);
		$this->callIsOfType($v1, $t9, true);
		$this->callIsOfType($v2, $t9, true);
		$this->callIsOfType($v1, $t10, false);
		$this->callIsOfType($v2, $t10, true);
		$this->callIsOfType($v1, $t11, false);
		$this->callIsOfType($v2, $t11, true);
		$this->callIsOfType($v1, $z, false);
		$this->callIsOfType($v1, $a, true);

		$v1 = $this->valueRegistry->true;
		$v2 = $this->valueRegistry->false;
		$t1 = $this->typeRegistry->true;
		$t2 = $this->typeRegistry->false;
		$t3 = $this->typeRegistry->boolean;
		$z = $this->typeRegistry->integer();

		$this->callIsOfType($v1, $t1, true);
		$this->callIsOfType($v2, $t1, false);
		$this->callIsOfType($v1, $t2, false);
		$this->callIsOfType($v2, $t2, true);
		$this->callIsOfType($v1, $t3, true);
		$this->callIsOfType($v2, $t3, true);
		$this->callIsOfType($v1, $z, false);
		$this->callIsOfType($v1, $a, true);

		$v1 = $this->valueRegistry->null;
		$t1 = $this->typeRegistry->null;
		$z = $this->typeRegistry->integer();

		$this->callIsOfType($v1, $t1, true);
		$this->callIsOfType($v1, $z, false);
		$this->callIsOfType($v1, $a, true);

		$this->programBuilder->addSubtype(new TypeNameIdentifier('Percent'), $this->typeRegistry->integer(0, 100),
			$this->expressionRegistry->constant($this->valueRegistry->null), null
		);
		$this->programBuilder->addSubtype(new TypeNameIdentifier('Digit'), $this->typeRegistry->integer(0, 9),
				$this->expressionRegistry->constant($this->valueRegistry->null), null
		);
		$v1 = $this->valueRegistry->subtypeValue(
			$tPer = new TypeNameIdentifier('Percent'),
			$this->valueRegistry->integer(50)
		);
		$v2 = $this->valueRegistry->subtypeValue(
			$tPer,
			$this->valueRegistry->integer(5)
		);
		$v3 = $this->valueRegistry->subtypeValue(
			$tDig = new TypeNameIdentifier('Digit'),
			$this->valueRegistry->integer(5)
		);
		$v4 = $this->valueRegistry->integer(5);
		$t1 = $this->typeRegistry->subtype($tPer);
		$t2 = $this->typeRegistry->subtype($tDig);
		$t3 = $this->typeRegistry->integer(-10, 30);
		$t4 = $this->typeRegistry->integer(-200, 200);
		$z = $this->typeRegistry->string();

		$this->callIsOfType($v1, $t1, true);
		$this->callIsOfType($v2, $t1, true);
		$this->callIsOfType($v3, $t1, false);
		$this->callIsOfType($v4, $t1, false);
		$this->callIsOfType($v1, $t2, false);
		$this->callIsOfType($v2, $t2, false);
		$this->callIsOfType($v3, $t2, true);
		$this->callIsOfType($v4, $t2, false);
		$this->callIsOfType($v1, $t3, false);
		$this->callIsOfType($v2, $t3, true); //used to be false
		$this->callIsOfType($v3, $t3, true);
		$this->callIsOfType($v4, $t3, true);
		$this->callIsOfType($v1, $t4, true);
		$this->callIsOfType($v2, $t4, true);
		$this->callIsOfType($v3, $t4, true);
		$this->callIsOfType($v4, $t4, true);
		$this->callIsOfType($v1, $z, false);
		$this->callIsOfType($v1, $a, true);

		$this->programBuilder->addSealed(
			new TypeNameIdentifier('PercentState'),
			$this->typeRegistry->record(['x' => $this->typeRegistry->integer(0, 100)]),
			$this->expressionRegistry->constant($this->valueRegistry->null),
			null
		);
		$this->programBuilder->addSealed(
			new TypeNameIdentifier('DigitState'),
			$this->typeRegistry->record(['x' => $this->typeRegistry->integer(0, 9)]),
			$this->expressionRegistry->constant($this->valueRegistry->null),
			null
		);
		$v1 = $this->valueRegistry->sealedValue(
			$tPer = new TypeNameIdentifier('PercentState'),
			$this->valueRegistry->record(['x' => $this->valueRegistry->integer(50)])
		);
		$v2 = $this->valueRegistry->sealedValue(
			$tPer,
			$this->valueRegistry->record(['x' => $this->valueRegistry->integer(5)])
		);
		$v3 = $this->valueRegistry->sealedValue(
			$tDig = new TypeNameIdentifier('DigitState'),
			$this->valueRegistry->record(['x' => $this->valueRegistry->integer(5)])
		);
		$v4 = $this->valueRegistry->integer(5);
		$t1 = $this->typeRegistry->sealed($tPer);
		$t2 = $this->typeRegistry->sealed($tDig);
		$t3 = $this->typeRegistry->integer(-10, 30);
		$t4 = $this->typeRegistry->integer(-200, 200);
		$z = $this->typeRegistry->integer();

		$this->callIsOfType($v1, $t1, true);
		$this->callIsOfType($v2, $t1, true);
		$this->callIsOfType($v3, $t1, false);
		$this->callIsOfType($v4, $t1, false);
		$this->callIsOfType($v1, $t2, false);
		$this->callIsOfType($v2, $t2, false);
		$this->callIsOfType($v3, $t2, true);
		$this->callIsOfType($v4, $t2, false);
		$this->callIsOfType($v1, $t3, false);
		$this->callIsOfType($v2, $t3, false);
		$this->callIsOfType($v3, $t3, false);
		$this->callIsOfType($v4, $t3, true);
		$this->callIsOfType($v1, $t4, false);
		$this->callIsOfType($v2, $t4, false);
		$this->callIsOfType($v3, $t4, false);
		$this->callIsOfType($v4, $t4, true);
		$this->callIsOfType($v1, $z, false);
		$this->callIsOfType($v1, $a, true);

		$this->programBuilder->addEnumeration(new TypeNameIdentifier('SE'), [
			new EnumValueIdentifier('A'),
			new EnumValueIdentifier('B'),
			new EnumValueIdentifier('C'),
		]);
		$this->programBuilder->addEnumeration(new TypeNameIdentifier('SG'), [
			new EnumValueIdentifier('A'),
			new EnumValueIdentifier('X'),
			new EnumValueIdentifier('Y'),
		]);

		$v1 = $this->valueRegistry->enumerationValue($se = new TypeNameIdentifier('SE'), new EnumValueIdentifier('A'));
		$v2 = $this->valueRegistry->enumerationValue(new TypeNameIdentifier('SG'), new EnumValueIdentifier('A'));
		$t1 = $this->typeRegistry->enumeration($se)->subsetType([new EnumValueIdentifier('A'), new EnumValueIdentifier('B')]);
		$t2 = $this->typeRegistry->enumeration($se)->subsetType([new EnumValueIdentifier('B'), new EnumValueIdentifier('C')]);
		$t3 = $this->typeRegistry->enumeration($se)->subsetType([new EnumValueIdentifier('A')]);
		$t4 = $this->typeRegistry->enumeration($se);
		$z = $this->typeRegistry->integer();

		$this->callIsOfType($v1, $t1, true);
		$this->callIsOfType($v2, $t1, false);
		$this->callIsOfType($v1, $t2, false);
		$this->callIsOfType($v2, $t2, false);
		$this->callIsOfType($v1, $t3, true);
		$this->callIsOfType($v2, $t3, false);
		$this->callIsOfType($v1, $t4, true);
		$this->callIsOfType($v2, $t4, false);
		$this->callIsOfType($v1, $z, false);
		$this->callIsOfType($v1, $a, true);

		$this->programBuilder->addAtom(new TypeNameIdentifier('SH'));
		$this->programBuilder->addAtom(new TypeNameIdentifier('SJ'));

		$v1 = $this->valueRegistry->atom($sh = new TypeNameIdentifier('SH'));
		$v2 = $this->valueRegistry->atom(new TypeNameIdentifier('SJ'));
		$t1 = $this->typeRegistry->atom($sh);
		$z = $this->typeRegistry->integer();

		$this->callIsOfType($v1, $t1, true);
		$this->callIsOfType($v2, $t1, false);
		$this->callIsOfType($v1, $z, false);
		$this->callIsOfType($v1, $a, true);

		$v1 = $this->valueRegistry->function(
			$this->typeRegistry->integer(-50, 50),
			$this->typeRegistry->nothing,
			$this->typeRegistry->real(0, 3.14),
			$this->expressionRegistry->functionBody(
				$this->expressionRegistry->constant($this->valueRegistry->real(1))
			)
		);
		$v2 = $this->valueRegistry->function(
			$this->typeRegistry->any,
			$this->typeRegistry->nothing,
			$this->typeRegistry->real(0, 3),
			$this->expressionRegistry->functionBody(
				$this->expressionRegistry->constant($this->valueRegistry->real(2))
			)
		);
		$t1 = $this->typeRegistry->function(
			$this->typeRegistry->nothing,
			$this->typeRegistry->any
		);
		$t2 = $this->typeRegistry->function(
			$this->typeRegistry->integer(-30, 30),
			$this->typeRegistry->real(-3.14, 3.14),
		);
		$t3 = $this->typeRegistry->function(
			$this->typeRegistry->integer(-100, 100),
			$this->typeRegistry->real(0, 3.14)
		);
		$t4 = $this->typeRegistry->function(
			$this->typeRegistry->integer(-50, 50),
			$this->typeRegistry->real(0, 2.71)
		);
		$z = $this->typeRegistry->integer();

		$this->callIsOfType($v1, $t1, true);
		$this->callIsOfType($v2, $t1, true);
		$this->callIsOfType($v1, $t2, true);
		$this->callIsOfType($v2, $t2, true);
		$this->callIsOfType($v1, $t3, false);
		$this->callIsOfType($v2, $t3, true);
		$this->callIsOfType($v1, $t4, false);
		$this->callIsOfType($v2, $t4, false);
		$this->callIsOfType($v1, $z, false);
		$this->callIsOfType($v1, $a, true);

		$v1 = $this->valueRegistry->error($this->valueRegistry->boolean(true));
		$v2 = $this->valueRegistry->error($this->valueRegistry->integer(42));
		$v3 = $this->valueRegistry->integer(42);
		$t1 = $this->typeRegistry->result($this->typeRegistry->boolean, $this->typeRegistry->integer());
		$t2 = $this->typeRegistry->result($this->typeRegistry->integer(), $this->typeRegistry->boolean);
		$z = $this->typeRegistry->integer();

		$this->callIsOfType($v1, $t1, false);
		$this->callIsOfType($v2, $t1, true);
		$this->callIsOfType($v3, $t1, false);
		$this->callIsOfType($v1, $t2, true);
		$this->callIsOfType($v2, $t2, false);
		$this->callIsOfType($v3, $t2, true);
		$this->callIsOfType($v1, $z, false);
		$this->callIsOfType($v1, $a, true);

		$v1 = $this->valueRegistry->mutable($this->typeRegistry->integer(10, 30), $this->valueRegistry->integer(20));
		$v2 = $this->valueRegistry->mutable($this->typeRegistry->integer(0, 40), $this->valueRegistry->integer(15));
		$v3 = $this->valueRegistry->integer(25);
		$t1 = $this->typeRegistry->mutable($this->typeRegistry->integer(10, 30));
		$t2 = $this->typeRegistry->mutable($this->typeRegistry->integer(0, 40));
		$z = $this->typeRegistry->string();

		$this->callIsOfType($v1, $t1, true);
		$this->callIsOfType($v2, $t1, false);
		$this->callIsOfType($v3, $t1, false);
		$this->callIsOfType($v1, $t2, false);
		$this->callIsOfType($v2, $t2, true);
		$this->callIsOfType($v3, $t2, false);
		$this->callIsOfType($v1, $z, false);
		$this->callIsOfType($v1, $a, true);

		$v1 = $this->valueRegistry->type($this->typeRegistry->any);
		$v2 = $this->valueRegistry->type($this->typeRegistry->nothing);
		$v3 = $this->valueRegistry->type($this->typeRegistry->integer());
		$t1 = $this->typeRegistry->any;
		$t2 = $this->typeRegistry->nothing;
		$t3 = $this->typeRegistry->type($this->typeRegistry->integer());
		$z = $this->typeRegistry->string();

		$this->callIsOfType($v1, $t1, true);
		$this->callIsOfType($v2, $t1, true);
		$this->callIsOfType($v3, $t1, true);
		$this->callIsOfType($v1, $t2, false);
		$this->callIsOfType($v2, $t2, false);
		$this->callIsOfType($v3, $t2, false);
		$this->callIsOfType($v1, $t3, false);
		$this->callIsOfType($v2, $t3, true);
		$this->callIsOfType($v3, $t3, true);
		$this->callIsOfType($v1, $z, false);
		$this->callIsOfType($v1, $a, true);
	}
}
