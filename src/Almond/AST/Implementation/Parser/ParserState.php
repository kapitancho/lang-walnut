<?php

namespace Walnut\Lang\Almond\AST\Implementation\Parser;

use Walnut\Lang\Almond\AST\Blueprint\Parser\ParserState as ParserStateInterface;

final class ParserState implements ParserStateInterface {
	public int $i = 0;
	public int $state = 0;
	private array $resultStack = [];
	public array $result = [];
	private array $callStack = [];
	public mixed $generated = null;

	public function push(int $callReturnPoint): void {
		$this->callStack[] = $callReturnPoint;
		$this->resultStack[] = $this->result;
		$this->result = [];
	}

	public function pop(): array {
		$return = [$this->result, $this->callStack];
		if (count($this->resultStack) > 0) {
			$this->result = array_pop($this->resultStack);
			$this->state = array_pop($this->callStack);
		}
		return $return;
	}

	public function moveAndPop(): void {
		$this->i++;
		$this->pop();
	}

	public function move(int $state): void {
		$this->state = $state;
		$this->i++;
	}
	public function stay(int $state): void {
		$this->state = $state;
	}

	public function depth(): int {
		return count($this->callStack);
	}

	public function back(int $state): void {
		$this->state = $state;
		$this->i--;
	}
}