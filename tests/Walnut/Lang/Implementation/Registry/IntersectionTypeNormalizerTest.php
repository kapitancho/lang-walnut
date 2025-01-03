<?php

namespace Walnut\Lang\Implementation\Registry;

use Walnut\Lang\Blueprint\Common\Identifier\TypeNameIdentifier;
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

    public function testAliasTypes(): void {
	    $this->programBuilder->addAlias(new TypeNameIdentifier('M'), $this->typeRegistry->boolean);
        self::assertEquals("(Integer&False)", (string)$this->intersection(
            $this->typeRegistry->alias(new TypeNameIdentifier('M')),
            $this->typeRegistry->integer(),
            $this->typeRegistry->false,
            $this->typeRegistry->real()
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

}