<?php

namespace Walnut\Lang\Almond\AST\Blueprint\Parser;

interface ParserState {
	public int $i { get; set; }
	public int $state { get; set; }
	public array $result { get; set; }
	public mixed $generated { get; set; }

	public function push(int $callReturnPoint): void;
	public function pop(): array;
	public function moveAndPop(): void;
	public function move(int $state): void;
	public function stay(int $state): void;
	public function depth(): int;
	public function back(int $state): void;
}