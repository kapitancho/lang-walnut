<?php /** @noinspection PhpUnusedLocalVariableInspection */

namespace Walnut\Lang\Test\Implementation\NativeCode\Type;

use BcMath\Number;
use Walnut\Lang\Blueprint\Common\Identifier\EnumValueIdentifier;
use Walnut\Lang\Blueprint\Common\Identifier\TypeNameIdentifier;
use Walnut\Lang\Blueprint\Common\Range\MinusInfinity;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Test\Implementation\BaseProgramTestHelper;

final class IsSubtypeOfTest extends BaseProgramTestHelper {

	private function callIsOfType(Type $type1, Type $type2, bool $expected): void {
		$this->testMethodCall(
			$this->expressionRegistry->constant(
				$this->valueRegistry->type($type1)
			),
			'isSubtypeOf',
			$this->expressionRegistry->constant(
				$this->valueRegistry->type($type2)
			),
			$this->valueRegistry->boolean($expected),
			sprintf("Checking if %s is subtype of %s (should be %s)",
				$type1,
				$type2,
				$expected ? 'true' : 'false'
			)
		);
	}

	public function testIsSubtypeOfInteger(): void {
		$n = $this->typeRegistry->nothing;
		$a = $this->typeRegistry->any;

		$t1 = $this->typeRegistry->integerSubset([
			new Number(1),
			new Number(10),
			new Number(42)
		]);
		$t2 = $this->typeRegistry->integerSubset([
			new Number(1),
			new Number(10),
			new Number(42),
			new Number(100)
		]);
		$t3 = $this->typeRegistry->integer(-50, 50);
		$t4 = $this->typeRegistry->integer(-200, 200);
		$t5 = $this->typeRegistry->integer(30, 130);
		$t6 = $this->typeRegistry->integer(MinusInfinity::value, 100);
		$t7 = $this->typeRegistry->integer(-100);
		$t8 = $this->typeRegistry->integer();
		$t9 = $this->typeRegistry->real(MinusInfinity::value, 100);
		$t10 = $this->typeRegistry->realSubset([
			new Number(1),
			new Number(10),
			new Number(42)
		]);

		$matrix = [
			1 => [1 =>  true,  true,  true,  true, false,  true,  true, true,  true,  true],
				 [1 => false,  true, false,  true, false,  true,  true, true,  true, false],
				 [1 => false, false,  true,  true, false,  true,  true, true,  true, false],
				 [1 => false, false, false,  true, false, false, false, true, false, false],
				 [1 => false, false, false,  true,  true, false,  true, true, false, false],
				 [1 => false, false, false, false, false,  true, false, true,  true, false],
				 [1 => false, false, false, false, false, false,  true, true, false, false],
				 [1 => false, false, false, false, false, false, false, true, false, false],
		];
		$this->callIsOfType(
			$t1,
			$t10,
			true,
		);
		foreach($matrix as $f => $values) {
			foreach($values as $o => $expected) {
				$this->callIsOfType(
					${'t'.$f},
					${'t'.$o},
					$expected,
				);
			}
		}
		$this->callIsOfType($t1, $a, true);
		$this->callIsOfType($n, $t1, true);
	}

	public function testIsSubtypeOfReal(): void {
		$n = $this->typeRegistry->nothing;
		$a = $this->typeRegistry->any;

		$t1 = $this->typeRegistry->realSubset([
			new Number(1),
			new Number('3.14'),
			new Number(42)
		]);
		$t2 = $this->typeRegistry->realSubset([
			new Number(1),
			new Number('3.14'),
			new Number(42),
			new Number(100)
		]);
		$t3 = $this->typeRegistry->real(-50, 50);
		$t4 = $this->typeRegistry->real(-200, 200);
		$t5 = $this->typeRegistry->real(30.3, 130.3);
		$t6 = $this->typeRegistry->real(MinusInfinity::value, 100);
		$t7 = $this->typeRegistry->real(-100);
		$t8 = $this->typeRegistry->real();

		$matrix = [
			1 => [1 =>  true,  true,  true,  true, false,  true,  true, true],
				 [1 => false,  true, false,  true, false,  true,  true, true],
				 [1 => false, false,  true,  true, false,  true,  true, true],
				 [1 => false, false, false,  true, false, false, false, true],
				 [1 => false, false, false,  true,  true, false,  true, true],
				 [1 => false, false, false, false, false,  true, false, true],
				 [1 => false, false, false, false, false, false,  true, true],
				 [1 => false, false, false, false, false, false, false, true],
		];
		foreach($matrix as $f => $values) {
			foreach($values as $o => $expected) {
				$this->callIsOfType(${'t'.$f}, ${'t'.$o}, $expected);
			}
		}
		$this->callIsOfType($t1, $a, true);
		$this->callIsOfType($n, $t1, true);
	}

	public function testIsSubtypeOfString(): void {
		$n = $this->typeRegistry->nothing;
		$a = $this->typeRegistry->any;

		$t1 = $this->typeRegistry->stringSubset([
			"Hello",
			"World"
		]);
		$t2 = $this->typeRegistry->stringSubset([
			"Hi",
			"Hello",
			"World",
			"Hello World!"
		]);
		$t3 = $this->typeRegistry->string(1, 5);
		$t4 = $this->typeRegistry->string(3, 5);
		$t5 = $this->typeRegistry->string(2, 15);
		$t6 = $this->typeRegistry->string(5);
		$t7 = $this->typeRegistry->string();

		$matrix = [
			1 => [1 =>  true,  true,  true,  true,  true,  true, true],
				 [1 => false,  true, false, false,  true, false, true],
				 [1 => false, false,  true, false, false, false, true],
				 [1 => false, false,  true,  true,  true, false, true],
				 [1 => false, false, false, false,  true, false, true],
				 [1 => false, false, false, false, false,  true, true],
				 [1 => false, false, false, false, false, false, true],
		];
		foreach($matrix as $f => $values) {
			foreach($values as $o => $expected) {
				$this->callIsOfType(${'t'.$f}, ${'t'.$o}, $expected);
			}
		}
		$this->callIsOfType($t1, $a, true);
		$this->callIsOfType($n, $t1, true);
	}

	public function testIsSubtypeOfBoolean(): void {
		$n = $this->typeRegistry->nothing;
		$a = $this->typeRegistry->any;

		$t1 = $this->typeRegistry->true;
		$t2 = $this->typeRegistry->false;
		$t3 = $this->typeRegistry->boolean;

		$matrix = [
			1 => [1 =>  true, false, true],
				 [1 => false,  true, true],
				 [1 => false, false, true],
		];
		foreach($matrix as $f => $values) {
			foreach($values as $o => $expected) {
				$this->callIsOfType(${'t'.$f}, ${'t'.$o}, $expected);
			}
		}
		$this->callIsOfType($t1, $a, true);
		$this->callIsOfType($n, $t1, true);
	}

	public function testIsSubtypeOfNull(): void {
		$n = $this->typeRegistry->nothing;
		$a = $this->typeRegistry->any;

		$t1 = $this->typeRegistry->null;

		$this->callIsOfType($t1, $t1, true);
		$this->callIsOfType($t1, $a, true);
		$this->callIsOfType($n, $t1, true);
	}

	public function testIsSubtypeOfTuple(): void {
		$n = $this->typeRegistry->nothing;
		$a = $this->typeRegistry->any;

		$t1 = $this->typeRegistry->tuple([]);
		$t2 = $this->typeRegistry->tuple([$this->typeRegistry->integer(-10, 10)]);
		$t3 = $this->typeRegistry->tuple([$this->typeRegistry->integer()]);
		$t4 = $this->typeRegistry->tuple([$this->typeRegistry->string()]);
		$t5 = $this->typeRegistry->tuple([
			$this->typeRegistry->integer(),
			$this->typeRegistry->string()
		]);
		$t6 = $this->typeRegistry->array($this->typeRegistry->integer());
		$t7 = $this->typeRegistry->array(
			$this->typeRegistry->union([
				$this->typeRegistry->integer(),
				$this->typeRegistry->string(),
			])
		);
		$t8 = $this->typeRegistry->array($this->typeRegistry->any, 2);
		$t9 = $this->typeRegistry->array($this->typeRegistry->any, 0, 1);
		$t10 = $this->typeRegistry->array();
		$t11 = $this->typeRegistry->tuple([], $this->typeRegistry->any);
		$t12 = $this->typeRegistry->tuple([$this->typeRegistry->integer(-10, 10)], $this->typeRegistry->any);
		$t13 = $this->typeRegistry->tuple([$this->typeRegistry->integer()], $this->typeRegistry->any);
		$t14 = $this->typeRegistry->tuple([$this->typeRegistry->string()], $this->typeRegistry->any);
		$t15 = $this->typeRegistry->tuple([
			$this->typeRegistry->integer(),
			$this->typeRegistry->string()
		], $this->typeRegistry->any);

		$matrix = [
			1 => [1 =>  true, false, false, false, false,  true,  true, false,  true, true,  true, false, false, false, false],
				 [1 => false,  true,  true, false, false,  true,  true, false,  true, true,  true,  true,  true, false, false],
				 [1 => false, false,  true, false, false,  true,  true, false,  true, true,  true, false,  true, false, false],
				 [1 => false, false, false,  true, false, false,  true, false,  true, true,  true, false, false,  true, false],
				 [1 => false, false, false, false,  true, false,  true,  true, false, true,  true, false,  true, false,  true],
				 [1 => false, false, false, false, false],
				 [1 => false, false, false, false, false],
				 [1 => false, false, false, false, false],
				 [1 => false, false, false, false, false],
				 [1 => false, false, false, false, false],
				 [1 => false, false, false, false, false, false, false, false, false, true,  true, false, false, false, false],
				 [1 => false, false, false, false, false, false, false, false, false, true,  true,  true,  true, false, false],
				 [1 => false, false, false, false, false, false, false, false, false, true,  true, false,  true, false, false],
				 [1 => false, false, false, false, false, false, false, false, false, true,  true, false, false,  true, false],
				 [1 => false, false, false, false, false, false, false,  true, false, true,  true, false,  true, false,  true],
		];
		foreach($matrix as $f => $values) {
			foreach($values as $o => $expected) {
				//echo "[$f:$o] ";
				$this->callIsOfType(${'t'.$f}, ${'t'.$o}, $expected);
			}
			//echo PHP_EOL;
		}
		$this->callIsOfType($t1, $a, true);
		$this->callIsOfType($n, $t1, true);
	}

	public function testIsSubtypeOfArray(): void {
		$n = $this->typeRegistry->nothing;
		$a = $this->typeRegistry->any;

		$t1 = $this->typeRegistry->array($this->typeRegistry->integer());
		$t2 = $this->typeRegistry->array($this->typeRegistry->integer(10, 20));
		$t3 = $this->typeRegistry->array($this->typeRegistry->integer(), 5, 15);
		$t4 = $this->typeRegistry->array($this->typeRegistry->integer(10, 20), 5, 15);
		$t5 = $this->typeRegistry->array(
			$this->typeRegistry->union([
				$this->typeRegistry->integer(),
				$this->typeRegistry->string(),
			])
		);
		$t6 = $this->typeRegistry->array($this->typeRegistry->nothing);
		$t7 = $this->typeRegistry->array($this->typeRegistry->any, 5, 15);
		$t8 = $this->typeRegistry->array($this->typeRegistry->any, 10, 20);
		$t9 = $this->typeRegistry->array();

		$matrix = [
			1 => [1 =>  true, false, false, false,  true, false, false, false,  true],
			     [1 =>  true,  true, false, false,  true, false, false, false,  true],
			     [1 =>  true, false,  true, false,  true, false,  true, false,  true],
			     [1 =>  true,  true,  true,  true,  true, false,  true, false,  true],
			     [1 => false, false, false, false,  true, false, false, false,  true],
			     [1 =>  true,  true, false, false,  true,  true, false, false,  true],
			     [1 => false, false, false, false, false, false,  true, false,  true],
			     [1 => false, false, false, false, false, false, false,  true,  true],
			     [1 => false, false, false, false, false, false, false, false,  true],
		];
		foreach($matrix as $f => $values) {
			foreach($values as $o => $expected) {
				$this->callIsOfType(${'t'.$f}, ${'t'.$o}, $expected);
			}
		}
		$this->callIsOfType($t1, $a, true);
		$this->callIsOfType($n, $t1, true);
	}

	public function testIsSubtypeOfMap(): void {
		$n = $this->typeRegistry->nothing;
		$a = $this->typeRegistry->any;

		$t1 = $this->typeRegistry->map($this->typeRegistry->integer());
		$t2 = $this->typeRegistry->map($this->typeRegistry->integer(10, 20));
		$t3 = $this->typeRegistry->map($this->typeRegistry->integer(), 5, 15);
		$t4 = $this->typeRegistry->map($this->typeRegistry->integer(10, 20), 5, 15);
		$t5 = $this->typeRegistry->map(
			$this->typeRegistry->union([
				$this->typeRegistry->integer(),
				$this->typeRegistry->string(),
			])
		);
		$t6 = $this->typeRegistry->map($this->typeRegistry->nothing);
		$t7 = $this->typeRegistry->map($this->typeRegistry->any, 5, 15);
		$t8 = $this->typeRegistry->map($this->typeRegistry->any, 10, 20);
		$t9 = $this->typeRegistry->map();

		$matrix = [
			1 => [1 =>  true, false, false, false,  true, false, false, false,  true],
			     [1 =>  true,  true, false, false,  true, false, false, false,  true],
			     [1 =>  true, false,  true, false,  true, false,  true, false,  true],
			     [1 =>  true,  true,  true,  true,  true, false,  true, false,  true],
			     [1 => false, false, false, false,  true, false, false, false,  true],
			     [1 =>  true,  true, false, false,  true,  true, false, false,  true],
			     [1 => false, false, false, false, false, false,  true, false,  true],
			     [1 => false, false, false, false, false, false, false,  true,  true],
			     [1 => false, false, false, false, false, false, false, false,  true],
		];
		foreach($matrix as $f => $values) {
			foreach($values as $o => $expected) {
				$this->callIsOfType(${'t'.$f}, ${'t'.$o}, $expected);
			}
		}
		$this->callIsOfType($t1, $a, true);
		$this->callIsOfType($n, $t1, true);
	}

	public function testIsSubtypeOfRecord(): void {
		$n = $this->typeRegistry->nothing;
		$a = $this->typeRegistry->any;

		$t1 = $this->typeRegistry->record([]);
		$t2 = $this->typeRegistry->record(['a' => $this->typeRegistry->integer(-10, 10)]);
		$t3 = $this->typeRegistry->record(['a' => $this->typeRegistry->integer()]);
		$t4 = $this->typeRegistry->record(['a' => $this->typeRegistry->string()]);
		$t5 = $this->typeRegistry->record([
			'a' => $this->typeRegistry->integer(),
			'b' => $this->typeRegistry->string()
		]);
		$t6 = $this->typeRegistry->map($this->typeRegistry->integer());
		$t7 = $this->typeRegistry->map(
			$this->typeRegistry->union([
				$this->typeRegistry->integer(),
				$this->typeRegistry->string(),
			])
		);
		$t8 = $this->typeRegistry->map($this->typeRegistry->any, 2);
		$t9 = $this->typeRegistry->map($this->typeRegistry->any, 0, 1);
		$t10 = $this->typeRegistry->map();
		$t11 = $this->typeRegistry->record([], $this->typeRegistry->any);
		$t12 = $this->typeRegistry->record(['a' => $this->typeRegistry->integer(-10, 10)], $this->typeRegistry->any);
		$t13 = $this->typeRegistry->record(['a' => $this->typeRegistry->integer()], $this->typeRegistry->any);
		$t14 = $this->typeRegistry->record(['a' => $this->typeRegistry->string()], $this->typeRegistry->any);
		$t15 = $this->typeRegistry->record([
			'a' => $this->typeRegistry->integer(),
			'b' => $this->typeRegistry->string()
		], $this->typeRegistry->any);

		$matrix = [
			1 => [1 =>  true, false, false, false, false,  true,  true, false,  true, true,  true, false, false, false, false],
				 [1 => false,  true,  true, false, false,  true,  true, false,  true, true,  true,  true,  true, false, false],
				 [1 => false, false,  true, false, false,  true,  true, false,  true, true,  true, false,  true, false, false],
				 [1 => false, false, false,  true, false, false,  true, false,  true, true,  true, false, false,  true, false],
				 [1 => false, false, false, false,  true, false,  true,  true, false, true,  true, false,  true, false,  true],
				 [1 => false, false, false, false, false],
				 [1 => false, false, false, false, false],
				 [1 => false, false, false, false, false],
				 [1 => false, false, false, false, false],
				 [1 => false, false, false, false, false],
				 [1 => false, false, false, false, false, false, false, false, false, true,  true, false, false, false, false],
				 [1 => false, false, false, false, false, false, false, false, false, true,  true,  true,  true, false, false],
				 [1 => false, false, false, false, false, false, false, false, false, true,  true, false,  true, false, false],
				 [1 => false, false, false, false, false, false, false, false, false, true,  true, false, false,  true, false],
				 [1 => false, false, false, false, false, false, false,  true, false, true,  true, false,  true, false,  true],
		];
		foreach($matrix as $f => $values) {
			foreach($values as $o => $expected) {
				//echo "[$f:$o] ";
				$this->callIsOfType(${'t'.$f}, ${'t'.$o}, $expected);
			}
			//echo PHP_EOL;
		}
		$this->callIsOfType($t1, $a, true);
		$this->callIsOfType($n, $t1, true);
	}

	public function testIsSubtypeOfAliasType(): void {
		$n = $this->typeRegistry->nothing;
		$a = $this->typeRegistry->any;

		$this->typeRegistryBuilder->addAlias(new TypeNameIdentifier('Integer1050'), $this->typeRegistry->integer(10, 50));
		$this->typeRegistryBuilder->addAlias(new TypeNameIdentifier('Integer2040'), $this->typeRegistry->integer(20, 40));

		$t1 = $this->typeRegistry->alias(new TypeNameIdentifier('Integer1050'));
		$t2 = $this->typeRegistry->alias(new TypeNameIdentifier('Integer2040'));
		$t3 = $this->typeRegistry->integer(10, 50);
		$t4 = $this->typeRegistry->integer(20, 40);

		$matrix = [
			1 => [1 =>  true, false,  true, false],
			     [1 =>  true,  true,  true,  true],
			     [1 =>  true, false,  true, false],
			     [1 =>  true,  true,  true,  true],
		];
		foreach($matrix as $f => $values) {
			foreach($values as $o => $expected) {
				$this->callIsOfType(${'t'.$f}, ${'t'.$o}, $expected);
			}
		}
		$this->callIsOfType($t1, $a, true);
		$this->callIsOfType($n, $t1, true);
	}

	public function testIsSubtypeOfFunctionType(): void {
		$n = $this->typeRegistry->nothing;
		$a = $this->typeRegistry->any;

		$t1 = $this->typeRegistry->function($this->typeRegistry->integer(),$this->typeRegistry->string());
		$t2 = $this->typeRegistry->function($this->typeRegistry->integer(),$this->typeRegistry->string(10, 20));
		$t3 = $this->typeRegistry->function($this->typeRegistry->integer(10, 20),$this->typeRegistry->string());
		$t4 = $this->typeRegistry->function($this->typeRegistry->integer(10, 20),$this->typeRegistry->string(10, 20));
		$t5 = $this->typeRegistry->function($this->typeRegistry->string(),$this->typeRegistry->integer());
		$t6 = $this->typeRegistry->function($this->typeRegistry->any,$this->typeRegistry->nothing);
		$t7 = $this->typeRegistry->function($this->typeRegistry->nothing,$this->typeRegistry->any);

		$matrix = [
			1 => [1 =>  true, false,  true, false, false, false, true],
				 [1 =>  true,  true,  true,  true, false, false, true],
				 [1 => false, false,  true, false, false, false, true],
				 [1 => false, false,  true,  true, false, false, true],
				 [1 => false, false, false, false,  true, false, true],
				 [1 =>  true,  true,  true,  true,  true,  true, true],
				 [1 => false, false, false, false, false, false, true],
		];
		foreach($matrix as $f => $values) {
			foreach($values as $o => $expected) {
				$this->callIsOfType(${'t'.$f}, ${'t'.$o}, $expected);
			}
		}
		$this->callIsOfType($t1, $a, true);
		$this->callIsOfType($n, $t1, true);
	}

	public function testIsSubtypeOfMutableValue(): void {
		$n = $this->typeRegistry->nothing;
		$a = $this->typeRegistry->any;

		$t1 = $this->typeRegistry->mutable($this->typeRegistry->integer());
		$t2 = $this->typeRegistry->mutable($this->typeRegistry->integer(10, 20));
		$t3 = $this->typeRegistry->mutable($this->typeRegistry->any);
		$t4 = $this->typeRegistry->mutable($this->typeRegistry->nothing);

		$matrix = [
			1 => [1 =>  true, false, false, false],
				 [1 => false,  true, false, false],
				 [1 => false, false,  true, false],
				 [1 => false, false, false,  true],
		];
		foreach($matrix as $f => $values) {
			foreach($values as $o => $expected) {
				$this->callIsOfType(${'t'.$f}, ${'t'.$o}, $expected);
			}
		}
		$this->callIsOfType($t1, $a, true);
		$this->callIsOfType($n, $t1, true);
	}

	public function testIsSubtypeOfTypeType(): void {
		$n = $this->typeRegistry->nothing;
		$a = $this->typeRegistry->any;

		$t1 = $this->typeRegistry->type($this->typeRegistry->integer());
		$t2 = $this->typeRegistry->type($this->typeRegistry->integer(10, 20));
		$t3 = $this->typeRegistry->type($this->typeRegistry->any);
		$t4 = $this->typeRegistry->type($this->typeRegistry->nothing);

		$matrix = [
			1 => [1 =>  true, false,  true, false],
				 [1 =>  true,  true,  true, false],
				 [1 => false, false,  true, false],
				 [1 =>  true,  true,  true,  true],
		];
		foreach($matrix as $f => $values) {
			foreach($values as $o => $expected) {
				$this->callIsOfType(${'t'.$f}, ${'t'.$o}, $expected);
			}
		}
		$this->callIsOfType($t1, $a, true);
		$this->callIsOfType($n, $t1, true);
	}

	public function testIsSubtypeOfAtomType(): void {
		$n = $this->typeRegistry->nothing;
		$a = $this->typeRegistry->any;

		$this->typeRegistryBuilder->addAtom(new TypeNameIdentifier('Atom1'));
		$this->typeRegistryBuilder->addAtom(new TypeNameIdentifier('Atom2'));

		$t1 = $this->typeRegistry->atom(new TypeNameIdentifier('Atom1'));
		$t2 = $this->typeRegistry->atom(new TypeNameIdentifier('Atom2'));

		$matrix = [
			1 => [1 =>  true, false],
				 [1 => false,  true],
		];
		foreach($matrix as $f => $values) {
			foreach($values as $o => $expected) {
				$this->callIsOfType(${'t'.$f}, ${'t'.$o}, $expected);
			}
		}
		$this->callIsOfType($t1, $a, true);
		$this->callIsOfType($n, $t1, true);
	}

	public function testIsSubtypeOfEnumerationType(): void {
		$n = $this->typeRegistry->nothing;
		$a = $this->typeRegistry->any;

		$this->typeRegistryBuilder->addEnumeration(new TypeNameIdentifier('Enumeration1'), [
			new EnumValueIdentifier('A'),
			new EnumValueIdentifier('B'),
			new EnumValueIdentifier('C')
		]);
		$this->typeRegistryBuilder->addEnumeration(new TypeNameIdentifier('Enumeration2'), [
			new EnumValueIdentifier('A'),
			new EnumValueIdentifier('B'),
			new EnumValueIdentifier('C')
		]);

		$t1 = $this->typeRegistry->enumeration(new TypeNameIdentifier('Enumeration1'));
		$t2 = $this->typeRegistry->enumeration(new TypeNameIdentifier('Enumeration2'));
		$t3 = $t1->subsetType([
			new EnumValueIdentifier('A'),
		]);
		$t4 = $t1->subsetType([
			new EnumValueIdentifier('A'),
			new EnumValueIdentifier('B'),
		]);
		$t5 = $t1->subsetType([
			new EnumValueIdentifier('A'),
			new EnumValueIdentifier('B'),
			new EnumValueIdentifier('C'),
		]);

		$matrix = [
			1 => [1 =>  true, false, false, false,  true],
				 [1 => false,  true, false, false, false],
				 [1 =>  true, false,  true,  true,  true],
				 [1 =>  true, false, false,  true,  true],
				 [1 =>  true, false, false, false,  true],
		];
		foreach($matrix as $f => $values) {
			foreach($values as $o => $expected) {
				$this->callIsOfType(${'t'.$f}, ${'t'.$o}, $expected);
			}
		}
		$this->callIsOfType($t1, $a, true);
		$this->callIsOfType($n, $t1, true);
	}

	public function testIsSubtypeOfIntersectionType(): void {
		$n = $this->typeRegistry->nothing;
		$a = $this->typeRegistry->any;

		$t1 = $this->typeRegistry->record([
			'a' => $this->typeRegistry->integer(), 'b' => $this->typeRegistry->string(), 'c' => $this->typeRegistry->boolean
		]);
		$t2 = $this->typeRegistry->record([
			'a' => $this->typeRegistry->any, 'b' => $this->typeRegistry->string(), 'd' => $this->typeRegistry->array()
		]);
		$t3 = $this->typeRegistry->intersection([$t1, $t2]);
		$t4 = $this->typeRegistry->intersection([$t1, $this->typeRegistry->integer()]);

		$matrix = [
			1 => [1 =>  true, false, false, false],
				 [1 => false,  true, false, false],
				 [1 =>  true,  true,  true, false],
				 [1 =>  true, false, false,  true]
		];
		foreach($matrix as $f => $values) {
			foreach($values as $o => $expected) {
				$this->callIsOfType(${'t'.$f}, ${'t'.$o}, $expected);
			}
		}
		$this->callIsOfType($t1, $a, true);
		$this->callIsOfType($n, $t1, true);
	}

	public function testIsSubtypeOfUnionType(): void {
		$n = $this->typeRegistry->nothing;
		$a = $this->typeRegistry->any;

		$t1 = $this->typeRegistry->record([
			'a' => $this->typeRegistry->integer(), 'b' => $this->typeRegistry->string(), 'c' => $this->typeRegistry->boolean
		]);
		$t2 = $this->typeRegistry->record([
			'a' => $this->typeRegistry->any, 'b' => $this->typeRegistry->string(), 'd' => $this->typeRegistry->array()
		]);
		$t3 = $this->typeRegistry->union([$t1, $t2]);
		$t4 = $this->typeRegistry->union([$t1, $this->typeRegistry->integer()]);

		$matrix = [
			1 => [1 =>  true, false,  true,  true],
				 [1 => false,  true,  true, false],
				 [1 => false, false,  true, false],
				 [1 => false, false, false,  true]
		];
		foreach($matrix as $f => $values) {
			foreach($values as $o => $expected) {
				$this->callIsOfType(${'t'.$f}, ${'t'.$o}, $expected);
			}
		}
		$this->callIsOfType($t1, $a, true);
		$this->callIsOfType($n, $t1, true);
	}

	public function testIsSubtypeOfResultType(): void {
		$n = $this->typeRegistry->nothing;
		$a = $this->typeRegistry->any;

		$t1 = $this->typeRegistry->result($this->typeRegistry->integer(), $this->typeRegistry->string());
		$t2 = $this->typeRegistry->result($this->typeRegistry->integer(10, 20), $this->typeRegistry->string());
		$t3 = $this->typeRegistry->result($this->typeRegistry->any, $this->typeRegistry->string());
		$t4 = $this->typeRegistry->result($this->typeRegistry->nothing, $this->typeRegistry->string());
		$t5 = $this->typeRegistry->result($this->typeRegistry->integer(), $this->typeRegistry->string(10, 20));
		$t6 = $this->typeRegistry->result($this->typeRegistry->integer(10, 20), $this->typeRegistry->string(10, 20));
		$t7 = $this->typeRegistry->result($this->typeRegistry->any, $this->typeRegistry->string(10, 20));
		$t8 = $this->typeRegistry->result($this->typeRegistry->nothing, $this->typeRegistry->string(10, 20));
		$t9 = $this->typeRegistry->integer();
		$t10 = $this->typeRegistry->string();

		$matrix = [
			1 => [1 =>  true, false,  true, false, false, false, false, false, false, false],
			     [1 =>  true,  true,  true, false, false, false, false, false, false, false],
			     [1 => false, false,  true, false, false, false, false, false, false, false],
			     [1 =>  true,  true,  true,  true, false, false, false, false, false, false],
			     [1 =>  true, false,  true, false,  true, false,  true, false, false, false],
			     [1 =>  true,  true,  true, false,  true,  true,  true, false, false, false],
			     [1 => false, false,  true, false, false, false,  true, false, false, false],
			     [1 =>  true,  true,  true,  true,  true,  true,  true,  true, false, false],
			     [1 =>  true, false,  true, false,  true, false,  true, false,  true, false],
			     [1 => false, false,  true, false, false, false,  true, false, false,  true],
		];
		foreach($matrix as $f => $values) {
			foreach($values as $o => $expected) {
				//echo "[$f, $o]:";
				$this->callIsOfType(${'t'.$f}, ${'t'.$o}, $expected);
				//echo '---', PHP_EOL;
			}
		}
		$this->callIsOfType($t1, $a, true);
		$this->callIsOfType($n, $t1, true);
	}
}
