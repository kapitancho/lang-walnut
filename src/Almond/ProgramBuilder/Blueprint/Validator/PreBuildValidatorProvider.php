<?php

namespace Walnut\Lang\Almond\ProgramBuilder\Blueprint\Validator;

interface PreBuildValidatorProvider {
	/** @var list<PreBuildValidator> */
	public array $validators { get; }
}