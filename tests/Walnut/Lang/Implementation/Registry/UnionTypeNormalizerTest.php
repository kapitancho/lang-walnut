<?php

namespace Walnut\Lang\Test\Implementation\Registry;

use Walnut\Lang\Blueprint\Common\Identifier\TypeNameIdentifier;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Test\Implementation\BaseProgramTestHelper;

final class UnionTypeNormalizerTest extends BaseProgramTestHelper {

    private function union(Type ... $types): Type {
        return $this->typeRegistry->union($types);
    }

    public function testBasic(): void {
        $this->assertEquals(
            '(Integer|String)', (string)$this->union(
                $this->typeRegistry->integer(),
                $this->typeRegistry->string()
            )
        );
    }

    public function testRanges(): void {
        $this->assertEquals('Integer<3..10>',
            (string)$this->union(
                $this->typeRegistry->integer(3, 7),
                $this->typeRegistry->integer(6, 10)
            ),
        );
    }

    public function testSubsetTypes(): void {
        $this->assertEquals(
            'Integer[3, 5]', (string)$this->union(
            $this->valueRegistry->integer(3)->type,
            $this->valueRegistry->integer(5)->type,
            )
        );
        $this->assertEquals(
            'Real[3.14, 5]', (string)$this->union(
            $this->valueRegistry->real(3.14)->type,
            $this->valueRegistry->real(5)->type,
            )
        );
    }

    public function testEmptyUnion(): void {
        self::assertEquals("Nothing", (string)$this->union());
    }

    public function testSingleType(): void {
        self::assertEquals("Boolean", (string)$this->union(
            $this->typeRegistry->boolean
        ));
    }

    public function testSimpleUnionType(): void {
        self::assertEquals("(Boolean|Integer)", (string)$this->union(
            $this->typeRegistry->boolean,
            $this->typeRegistry->integer()
        ));
    }

    public function testWithNothingType(): void {
        self::assertEquals("(Boolean|Integer)", (string)$this->union(
            $this->typeRegistry->boolean,
            $this->typeRegistry->integer(),
            $this->typeRegistry->nothing
        ));
    }

    public function testWithAnyType(): void {
        self::assertEquals("Any", (string)$this->union(
            $this->typeRegistry->boolean,
            $this->typeRegistry->integer(),
            $this->typeRegistry->any
        ));
    }

    public function testWithNestedType(): void {
        self::assertEquals("(Boolean|Integer|String)", (string)$this->union(
            $this->typeRegistry->boolean,
            $this->union(
                $this->typeRegistry->integer(),
                $this->typeRegistry->string(),
            )
        ));
    }

    public function testSubtypes(): void {
        self::assertEquals("(Boolean|Real)", (string)$this->union(
            $this->typeRegistry->boolean,
            $this->typeRegistry->integer(),
            $this->typeRegistry->false,
            $this->typeRegistry->real()
        ));
    }

    public function testShapeTypes(): void {
        self::assertEquals("Shape<(Integer|String)>", (string)$this->union(
            $this->typeRegistry->shape($this->typeRegistry->integer()),
            $this->typeRegistry->shape($this->typeRegistry->string()),
        ));
    }

    public function testAliasTypes(): void {
	    $this->typeRegistryBuilder->addAlias(new TypeNameIdentifier('M'), $this->typeRegistry->boolean);
        self::assertEquals("(M|Real)", (string)$this->union(
	        $this->typeRegistry->alias(new TypeNameIdentifier('M')),
            $this->typeRegistry->integer(),
            $this->typeRegistry->false,
            $this->typeRegistry->real()
        ));
    }

    public function testAliasing(): void {
	    $this->typeRegistryBuilder->addAlias(new TypeNameIdentifier('M'), $this->typeRegistry->boolean);
	    $this->typeRegistryBuilder->addAlias(new TypeNameIdentifier('N'), $this->typeRegistry->integer());
        self::assertEquals("(M|N)", (string)$this->union(
	        $this->typeRegistry->alias(new TypeNameIdentifier('M')),
	        $this->typeRegistry->alias(new TypeNameIdentifier('N')),
        ));
    }

    public function testDisjointRanges(): void {
        self::assertEquals("Integer<[1..10], [15..25]>", (string)$this->union(
            $this->typeRegistry->integer(1, 10),
            $this->typeRegistry->integer(15, 25),
        ));
        self::assertEquals("Integer<[1..10], [15..25]>", (string)$this->union(
            $this->typeRegistry->integer(15, 25),
	        $this->typeRegistry->integer(1, 10),
        ));
        self::assertEquals("Real<[1..10], [15..25]>", (string)$this->union(
            $this->typeRegistry->real(15, 25),
	        $this->typeRegistry->real(1, 10),
        ));
    }

    public function testJointRanges(): void {
        self::assertEquals("Integer<1..25>", (string)$this->union(
            $this->typeRegistry->integer(1, 15),
            $this->typeRegistry->integer(10, 25),
        ));
    }

    public function testJointRangesInfinity(): void {
        self::assertEquals("Integer", (string)$this->union(
            $this->typeRegistry->integer(max: 15),
            $this->typeRegistry->integer(10),
        ));
    }

	public function testResultType(): void {
		self::assertEquals("Result<(Integer<1..15>|String), Integer<5..10>>", (string)$this->union(
			$this->typeRegistry->integer(1, 15),
			$this->typeRegistry->result(
                $this->typeRegistry->string(),
				$this->typeRegistry->integer(5, 10)
			),
		));
		self::assertEquals("Result<(String|Integer<1..15>), Integer<5..10>>", (string)$this->union(
			$this->typeRegistry->result(
                $this->typeRegistry->string(),
				$this->typeRegistry->integer(5, 10)
			),
			$this->typeRegistry->integer(1, 15),
		));
		self::assertEquals("Result<Integer<1..15>, Integer<5..10>>", (string)$this->union(
			$this->typeRegistry->result(
                $this->typeRegistry->nothing,
				$this->typeRegistry->integer(5, 10)
			),
			$this->typeRegistry->integer(1, 15),
		));
		self::assertEquals("Result<(String|Integer), (Boolean|Array)>", (string)$this->union(
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