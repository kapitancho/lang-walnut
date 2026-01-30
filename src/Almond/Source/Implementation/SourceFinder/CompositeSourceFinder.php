<?php

namespace Walnut\Lang\Almond\Source\Implementation\SourceFinder;

use Walnut\Lang\Almond\Source\Blueprint\SourceFinder\SourceFinder;

final readonly class CompositeSourceFinder implements SourceFinder {

	/** @var list<SourceFinder> */
	public array $sourceFinders;

	public function __construct(SourceFinder ... $sourceFinders) {
		$this->sourceFinders = array_values($sourceFinders);
	}

	public function sourceExists(string $sourceName): bool {
		return array_any(
			$this->sourceFinders,
			fn(SourceFinder $sourceFinder): bool => $sourceFinder->sourceExists($sourceName)
		);
	}
	public function readSource(string $sourceName): string|null {
		foreach($this->sourceFinders as $sourceFinder) {
			$sourceCode = $sourceFinder->readSource($sourceName);
			if (is_string($sourceCode)) {
				return $sourceCode;
			}
		}
		return null;
	}
}