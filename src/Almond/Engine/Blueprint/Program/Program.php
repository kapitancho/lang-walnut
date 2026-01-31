<?php

namespace Walnut\Lang\Almond\Engine\Blueprint\Program;

use Walnut\Lang\Almond\Engine\Blueprint\Common\Identifier\TypeName;

interface Program {

	/** @throws InvalidEntryPointDependency */
	public function getEntryPoint(TypeName $typeName): ProgramEntryPoint;

}