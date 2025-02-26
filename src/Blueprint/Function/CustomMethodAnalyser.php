<?php

namespace Walnut\Lang\Blueprint\Function;

use Walnut\Lang\Blueprint\Program\Registry\CustomMethodRegistry;

interface CustomMethodAnalyser {
	/** @return string[] - the errors found during analyse */
	public function analyse(CustomMethodRegistry $registry): array;
}