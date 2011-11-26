class CSS {

const SPECIFIED = 0x10000000;

const HASH = "<?php echo md5(serialize(ValidationGenerator::$usedRules)); ?>";

public static function toString($value) {
    if (is_integer($value) && isset(self::$toString[$value])) {
        return self::$toString[$value];
    } else {
        return "".$value;
    }
}

public static $initialized = false;

public static $initials;

public static $computedInitials;

<?php
    foreach (ValidationGenerator::$idents as $key => $ident) {
        echo "\tconst ".ValidationGenerator::toInternal($ident, "_")." = ".$key.";\n";
    }
?>

public static $idents = array(
<?php
    foreach (ValidationGenerator::$idents as $key => $ident) {
        echo "\t'".$ident."' => ".ValidationGenerator::toInternal($ident).",\n";
    }
?>
);



public static $attributes = array(
<?php
    foreach (ValidationGenerator::$attributes as $attribute) {
        echo "\t'".$attribute->name."' => ".ValidationGenerator::toInternal($attribute->name).",\n";
    }
?>
);

public static $inheriteds = array(
<?php
    foreach(ValidationGenerator::$inheriteds as $key => $inherited) {
        echo "\t".ValidationGenerator::toInternal($inherited->name).",\n";
    }
?>
);

public static $toString = array(
<?php
	foreach (ValidationGenerator::$idents as $ident) {
        echo "\t".ValidationGenerator::toInternal($ident)." => \"".$ident."\",\n";
    }
?>
);

}

if (CSS::$initialized == false) {
CSS::$initials = array(
<?php
	foreach(ValidationGenerator::$initials as $ident => $ilist) {
		$subs = array();
		foreach ($ilist as $initial) {
			$subs[] = $initial->initialConstructor();
		}
		echo "\t".ValidationGenerator::toInternal($ident)." => array(".implode(", ", $subs)."),\n";
    }
?>
);

}
