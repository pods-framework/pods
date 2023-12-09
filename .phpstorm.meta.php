<?php

namespace PHPSTORM_META {
	// Allow PhpStorm IDE to resolve return types when calling pods_container( Object_Type::class ) or pods_container( `Object_Type` )
	override(
		\pods_container( 0 ),
		map( [
			'' => '@',
			'' => '@Class',
		] )
	);
}
