<?php

namespace Walnut\Lang\Test\Implementation\Registry;

use Walnut\Lang\Blueprint\Common\Identifier\EnumValueIdentifier;
use Walnut\Lang\Blueprint\Common\Identifier\TypeNameIdentifier;
use Walnut\Lang\Blueprint\Common\Range\PlusInfinity;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Test\Implementation\BaseProgramTestHelper;

final class IntersectionTypeNormalizerTest extends BaseProgramTestHelper {

    private function intersection(Type ... $types): Type {
        return $this->typeRegistry->intersection($types);
    }

    public function testRanges(): void {
        $this->assertEquals('Integer<6..7>',
            (string)$this->intersection(
                $this->typeRegistry->integer(3, 7),
                $this->typeRegistry->integer(6, 10)
            ),
        );
    }

    public function testSubsetTypes(): void {
        $this->assertEquals(
            'Integer[3]', (string)$this->intersection(
                $this->typeRegistry->union([
                    $this->valueRegistry->integer(3)->type,
                    $this->valueRegistry->integer(5)->type
                ]),
                $this->typeRegistry->union([
                    $this->valueRegistry->integer(3)->type,
                    $this->valueRegistry->integer(7)->type
                ]),
            )
        );
        $this->assertEquals(
            'Real[3.14]', (string)$this->intersection(
                $this->typeRegistry->union([
                    $this->valueRegistry->real(3.14)->type,
                    $this->valueRegistry->real(5)->type
                ]),
                $this->typeRegistry->union([
                    $this->valueRegistry->real(3.14)->type,
                    $this->valueRegistry->real(7)->type
                ]),
            )
        );
        $this->assertEquals(
            "String['a']", (string)$this->intersection(
                $this->typeRegistry->union([
                    $this->valueRegistry->string('a')->type,
                    $this->valueRegistry->string('b')->type
                ]),
                $this->typeRegistry->union([
                    $this->valueRegistry->string('a')->type,
                    $this->valueRegistry->string('c')->type
                ]),
            )
        );
    }

    public function testEnumSubsetTypes(): void {
		$this->typeRegistryBuilder->addEnumeration($e = new TypeNameIdentifier('E'), [
			$a = new EnumValueIdentifier('A'),
			$b = new EnumValueIdentifier('B'),
			$c = new EnumValueIdentifier('C'),
			new EnumValueIdentifier('D'),
		]);
	    $this->assertEquals(
         "E[A]", (string)$this->intersection(
             $this->typeRegistry->union([
                 $this->valueRegistry->enumerationValue($e, $a)->type,
	             $this->valueRegistry->enumerationValue($e, $b)->type
             ]),
             $this->typeRegistry->union([
	             $this->valueRegistry->enumerationValue($e, $a)->type,
				 $this->valueRegistry->enumerationValue($e, $c)->type
             ]),
         )
     );
    }

    public function testEmptyIntersection(): void {
        self::assertEquals("Any", (string)$this->intersection());
    }

    public function testSingleType(): void {
        self::assertEquals("Boolean", (string)$this->intersection(
            $this->typeRegistry->boolean
        ));
    }

    public function testSimpleUnionType(): void {
        self::assertEquals("Integer", (string)$this->intersection(
            $this->typeRegistry->real(),
            $this->typeRegistry->integer()
        ));
    }

    public function testWithAnyType(): void {
        self::assertEquals("(Boolean&Integer)", (string)$this->intersection(
            $this->typeRegistry->boolean,
            $this->typeRegistry->integer(),
            $this->typeRegistry->any
        ));
    }

    public function testWithNothingType(): void {
        self::assertEquals("Nothing", (string)$this->intersection(
            $this->typeRegistry->boolean,
            $this->typeRegistry->integer(),
            $this->typeRegistry->nothing
        ));
    }

    public function testWithNestedType(): void {
        self::assertEquals("(Boolean&Integer&String)", (string)$this->intersection(
            $this->typeRegistry->boolean,
            $this->intersection(
                $this->typeRegistry->integer(),
                $this->typeRegistry->string(),
            )
        ));
    }

    public function testSubtypes(): void {
        self::assertEquals("(Integer&False)", (string)$this->intersection(
            $this->typeRegistry->boolean,
            $this->typeRegistry->integer(),
            $this->typeRegistry->false,
            $this->typeRegistry->real()
        ));
    }

	public function testShapeTypes(): void {
     self::assertEquals("Shape<(Integer&String)>", (string)$this->intersection(
         $this->typeRegistry->shape($this->typeRegistry->integer()),
         $this->typeRegistry->shape($this->typeRegistry->string()),
     ));
 }

    public function testAliasTypes(): void {
	    $this->typeRegistryBuilder->addAlias(new TypeNameIdentifier('M'), $this->typeRegistry->boolean);
        self::assertEquals("(Integer&False)", (string)$this->intersection(
            $this->typeRegistry->alias(new TypeNameIdentifier('M')),
            $this->typeRegistry->integer(),
            $this->typeRegistry->false,
            $this->typeRegistry->real()
        ));
    }


	public function testAliasing(): void {
		$this->typeRegistryBuilder->addAlias(new TypeNameIdentifier('M'), $this->typeRegistry->boolean);
		$this->typeRegistryBuilder->addAlias(new TypeNameIdentifier('N'), $this->typeRegistry->integer());
		self::assertEquals("(M&N)", (string)$this->intersection(
			$this->typeRegistry->alias(new TypeNameIdentifier('M')),
			$this->typeRegistry->alias(new TypeNameIdentifier('N')),
		));
	}

	public function testDisjointRanges(): void {
        self::assertEquals("(Integer<1..10>&Integer<15..25>)", (string)$this->intersection(
            $this->typeRegistry->integer(1, 10),
            $this->typeRegistry->integer(15, 25),
        ));
    }

    public function testJointRanges(): void {
        self::assertEquals("Integer<10..15>", (string)$this->intersection(
            $this->typeRegistry->integer(1, 15),
            $this->typeRegistry->integer(10, 25),
        ));
        self::assertEquals("Integer<10..15>", (string)$this->intersection(
            $this->typeRegistry->integer(1, 15),
            $this->typeRegistry->real(10, 25),
        ));
        self::assertEquals("Integer<10..15>", (string)$this->intersection(
            $this->typeRegistry->real(1, 15),
            $this->typeRegistry->integer(10, 25),
        ));
        self::assertEquals("Real<10..15>", (string)$this->intersection(
            $this->typeRegistry->real(1, 15),
            $this->typeRegistry->real(10, 25),
        ));
    }

    public function testJointRangesInfinity(): void {
        self::assertEquals("Integer<10..15>", (string)$this->intersection(
            $this->typeRegistry->integer(max: 15),
            $this->typeRegistry->integer(10),
        ));
    }

	public function testResultType(): void {
		self::assertEquals("(Integer<1..15>&String)", (string)$this->intersection(
			$this->typeRegistry->integer(1, 15),
			$this->typeRegistry->result(
                $this->typeRegistry->string(),
				$this->typeRegistry->integer(5, 10)
			),
		));
		self::assertEquals("(String&Integer<1..15>)", (string)$this->intersection(
			$this->typeRegistry->result(
                $this->typeRegistry->string(),
				$this->typeRegistry->integer(5, 10)
			),
			$this->typeRegistry->integer(1, 15),
		));
		self::assertEquals("Nothing", (string)$this->intersection(
			$this->typeRegistry->result(
                $this->typeRegistry->nothing,
				$this->typeRegistry->integer(5, 10)
			),
			$this->typeRegistry->integer(1, 15),
		));
		self::assertEquals("Result<(String&Integer), (Boolean&Array)>", (string)$this->intersection(
			$this->typeRegistry->result(
                $this->typeRegistry->string(),
				$this->typeRegistry->boolean
			),
			$this->typeRegistry->result(
                $this->typeRegistry->integer(),
				$this->typeRegistry->array()
			),
		));
	}

	public function testLengthRangeMinMax(): void {
		$this->assertEquals('Map<Integer, 6..7>',
			(string)$this->intersection(
				$this->typeRegistry->map($this->typeRegistry->real(), 3, 7),
				$this->typeRegistry->map($this->typeRegistry->integer(), 6, 10),
			),
		);
    }

	public function testLengthRangeMap(): void {
		$this->assertEquals('Map<Integer, 6..10>',
			(string)$this->intersection(
				$this->typeRegistry->map($this->typeRegistry->real(), 2),
				$this->typeRegistry->map($this->typeRegistry->integer(), 6, 10),
			),
		);
    }

	public function testLengthRangeMapNoIntersection(): void {
		$this->assertEquals('(Map<Real, 6..>&Map<Integer, 2..5>)',
			(string)$this->intersection(
				$this->typeRegistry->map($this->typeRegistry->real(), 6),
				$this->typeRegistry->map($this->typeRegistry->integer(), 2, 5),
			),
		);
    }

	public function testLengthRangeArray(): void {
		$this->assertEquals('Array<Integer, 6..8>',
			(string)$this->intersection(
				$this->typeRegistry->array($this->typeRegistry->real(), 2, 8),
				$this->typeRegistry->array($this->typeRegistry->integer(), 6),
			),
		);
    }

	public function testLengthRangeArrayNoIntersection(): void {
		$this->assertEquals('(Array<Real, 2..5>&Array<Integer, 6..>)',
			(string)$this->intersection(
				$this->typeRegistry->array($this->typeRegistry->real(), 2, 5),
				$this->typeRegistry->array($this->typeRegistry->integer(), 6),
			),
		);
    }

}