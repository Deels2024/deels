<?php

declare(strict_types=1);

namespace RB\Core;

use RB\Exception\RubiconException;

trait TraitCommonStatic {

	public static function __callStatic(string $method_name, array $args) {
		throw new RubiconException(sprintf('%s(): The method \'%s::%s\' is not found', __METHOD__, static::class, $method_name));
	} // end of the '__callStatic()' method

	final public static function class_dump(bool $display = NULL) {
		$display = $display ?? TRUE;
		$ref_class = new \ReflectionClass(static::class);
		$class_dump = (string) $ref_class;
		if (!RB_IS_CLI) {
			$class_dump = sprintf('<pre>%s</pre>', htmlspecialchars($class_dump, ENT_QUOTES, 'ISO-8859-1'));
		} // end if
		if ($display) {
			echo $class_dump;
		} else {
			return $class_dump;
		} // end if
	} // end of the 'class_dump()' method

} // end of the 'TraitCommonStatic' trait
