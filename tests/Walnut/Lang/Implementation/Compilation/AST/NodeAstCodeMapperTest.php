<?php

namespace Walnut\Lang\Implementation\Compilation\AST;

use PHPUnit\Framework\TestCase;
use Walnut\Lang\Blueprint\Program\Registry\TypeRegistry;
use Walnut\Lang\Implementation\AST\Node\SourceLocation;
use Walnut\Lang\Implementation\AST\Node\Value\NullValueNode;
use Walnut\Lang\Implementation\Value\NullValue;
use Walnut\Lib\Walex\SourcePosition;

final class NodeAstCodeMapperTest extends TestCase {

	private NodeAstCodeMapper $nodeAstCodeMapper;

	protected function setUp(): void {
		parent::setUp();

		$this->nodeAstCodeMapper = new NodeAstCodeMapper;
	}

	public function testOk(): void {
		$node = new NullValueNode(
			new SourceLocation(
				'source',
				new SourcePosition(1, 2, 3),
				new SourcePosition(4, 5, 6),
			)
		);
		$element = new NullValue(
			$this->createMock(TypeRegistry::class),
		);
		$this->assertNull($this->nodeAstCodeMapper->getSourceLocation($element));
		$this->nodeAstCodeMapper->mapNode($node, $element);
		$this->assertNotNull($this->nodeAstCodeMapper->getSourceLocation($element));
		$this->nodeAstCodeMapper->reset();
		$this->assertNull($this->nodeAstCodeMapper->getSourceLocation($element));
	}
}