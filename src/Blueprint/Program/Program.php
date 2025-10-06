<?php

namespace Walnut\Lang\Blueprint\Program;

use Walnut\Lang\Blueprint\Common\Identifier\TypeNameIdentifier;

interface Program {

	/** @throws InvalidEntryPointDependency */
	public function getEntryPoint(TypeNameIdentifier $typeName): ProgramEntryPoint;

}