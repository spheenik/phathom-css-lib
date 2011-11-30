class CSS {

	const SPECIFIED = 0x10000000;
	const HASH = "<?php echo md5(serialize(ValidationGenerator::$usedRules)); ?>";

<?php
    foreach (ValidationGenerator::$idents as $key => $ident) {
        echo "\tconst ".ValidationGenerator::toInternal($ident, "_")." = ".$key.";\n";
    }
?>

	public static $initialized = false;
	public static $initials;
	public static $computedInitials;

	public static $idents = array(
<?php
    foreach (ValidationGenerator::$idents as $key => $ident) {
        echo "\t\t'".$ident."' => ".ValidationGenerator::toInternal($ident).",\n";
    }
?>
	);

	public static $attributes = array(
<?php
    foreach (ValidationGenerator::$attributes as $attribute) {
        echo "\t\t'".$attribute->name."' => ".ValidationGenerator::toInternal($attribute->name).",\n";
    }
?>
	);

	public static $inheriteds = array(
<?php
    foreach(ValidationGenerator::$inheriteds as $key => $inherited) {
        echo "\t\t".ValidationGenerator::toInternal($inherited->name).",\n";
    }
?>
	);

	public static $toString = array(
<?php
	foreach (ValidationGenerator::$idents as $ident) {
        echo "\t\t".ValidationGenerator::toInternal($ident)." => \"".$ident."\",\n";
    }
?>
	);

	public static function initialize() {
		if (self::$initialized) return;
		
		self::$initialized = true;
		new CSSTree(); // autoload
		self::$initials = array(
<?php
	foreach(ValidationGenerator::$initials as $ident => $ilist) {
		$subs = array();
		foreach ($ilist as $initial) {
			$subs[] = $initial->initialConstructor();
		}
		echo "\t\t\t".ValidationGenerator::toInternal($ident)." => array(".implode(", ", $subs)."),\n";
    }
?>
		);
	
	    foreach(self::$initials as $prop => $specifieds) {
			self::$computedInitials[$prop] = array();
	        foreach($specifieds as $specified) {
				self::$computedInitials[$prop][] = CSSComputer::compute(null, null, $prop, $specified);
			}
	    }
	}

	public static function toString($value) {
	    if (is_integer($value) && isset(self::$toString[$value])) {
	        return self::$toString[$value];
	    } else {
	        return "".$value;
	    }
	}



}