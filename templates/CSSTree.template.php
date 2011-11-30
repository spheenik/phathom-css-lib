class CSSTree {
}

class CSSNode {
	public function matchIdent($ident) {
		return false;
	}
	
    public function toString() {
        ob_start();
        $args = func_get_args();
        call_user_func_array(array($this, "dump"), $args);
        $ret = ob_get_contents();
        ob_end_clean();
        return strip_tags($ret);
    }

};

class CSSArray extends CSSNode {
	public $values;
	public function __construct() {
		$this->values = func_get_args();
	}

	public function dump($separator = "") {
		$first = true;
		foreach($this->values as $value) {
			if ($first)
			$first = false;
			else
			echo $separator;

			if (is_object($value))
			$value->dump();
			else
			echo $value;
		}
	}
}

class CSSNumber extends CSSNode {

	const NONE       = 0x000;
	const UNKNOWN    = 0x001;
<?php 
	$over = 0; 
	foreach (ValidationGenerator::$numberUnits as $prim => $units) {
		$over++; echo "\n";
		foreach($units as $under => $unit) {
			echo "\tconst ".strtoupper($unit->name).str_repeat(" ", 10 - strlen($unit->name))." = 0x".dechex($over*256 + $under).";\n";
		}
	} 
?>

	public $value;
	public $unitString;
	public $unit;
	
	public function __construct($value, $unitString) {
		$this->value = $value;
		$this->unitString = $unitString;
		
		switch($unitString) {
			case "": $this->unit = self::NONE; break;
<?php 
	foreach (ValidationGenerator::$numberUnits as $prim => $units) {
		foreach($units as $under => $unit) {
			echo "\t\t\tcase \"".$unit->name."\": \$this->unit = self::".strtoupper($unit->name)."; break;\n";
		}
	} 
?>
			default: $this->unit = self::UNKNOWN; break;
		}
	}
	
	public function dump() {
		echo $this->value;
		echo $this->unit == self::PERCENT ? "%" : $this->unitString;
	}
	
	public function matchIdent($ident) {
		switch($ident) {
			case CSS::_PRIM_ZERO:
				return $this->value === 0.0 && $this->unit === self::NONE;
			case CSS::_PRIM_INTEGER:
				return $this->value == (int)$this->value && $this->unit === self::NONE;
<?php 
	foreach (ValidationGenerator::$numberUnits as $prim => $units) {
		$names = array();
		foreach($units as $under => $unit) {
			$names[] = '"'.$unit->name.'"';
		}
		echo "\t\t\tcase ".ValidationGenerator::toInternal($prim).":\n\t\t\t\treturn in_array(\$this->unitString, array(".implode(", ", $names)."));\n";
	} 
?>
		}
		return false;
	}
	

}

class CSSString extends CSSNode  {
	public $value;
	public $delimiter;
	public function __construct($value, $delimiter = "\"") {
		$this->value = $value;
		$this->delimiter = $delimiter;
	}
	public function dump() {
		echo $this->delimiter.$this->value.$this->delimiter;
	}

	public function matchIdent($ident) {
		return $ident == CSS::_PRIM_STRING;
	}
	
}

class CSSUri extends CSSString {
	public function dump() {
		echo "url(".$this->delimiter.$this->value.$this->delimiter.")";
	}
	
	public function matchIdent($ident) {
		return $ident == CSS::_PRIM_URI;
	}
	
}

class CSSColor extends CSSNode {
	public $value;
	public function __construct($value) {
		$this->value = $value;
	}

	public function dump() {
		echo '#'.$this->value.'';
	}
	
	public function matchIdent($ident) {
		$l = strlen($this->value);
		return $ident == CSS::_PRIM_COLOR && ($l == 3 || $l == 6);
	}
	
}

class CSSIdent extends CSSNode {
	public $value;
	public $tokenType;

	public function __construct($init) {
		if (is_integer($init)) {
			$this->tokenType = $init;
			$this->value = CSS::toString($init);
		} else {
			$this->tokenType = isset(CSS::$idents[$init]) ? CSS::$idents[$init] : CSS::_PRIM_ANY_IDENT;
			$this->value = $init;
		}
	}

	public function dump() {
		echo '<b>'.$this->value.'</b>';
	}
	
	public function matchIdent($ident) {
		return $ident === $this->tokenType || $ident == CSS::_PRIM_ANY_IDENT;
	}
	
}

class CSSOperator extends CSSNode  {
	public $value;

	public function __construct($value) {
		$this->value = $value;
	}

	public function dump() {
		echo $this->value;
	}	
}

class CSSReference extends CSSIdent {
	public function dump() {
		echo '<b>&amp;'.$this->value.'</b>';
	}
}

class CSSFunction extends CSSNode  {
	public $name;
	public $params;
	public function __construct($name, $params) {
		$this->name = $name;
		$this->params = $params;
	}
	public function dump() {
		echo $this->name;
		echo "(";
		echo $this->params->dump(" ");
		echo ")";
	}
}

class CSSDeclaration extends CSSNode {
	const NORMAL    = 0;
	const IMPORTANT = 1;

	public $property;
	public $expression;
	public $priority;
	public $resolved;

	public $isValid;

	public function __construct($property, $expression, $priority) {
		$this->property = $property;
		$this->expression = $expression;
		$this->priority = $priority;
		$this->isValid = null;
	}

	public function dump() {
		$color = $this->isValid === null ? "#444" : ($this->isValid ? "green" : "red");
		echo "<span style='color:$color;'>";
		if ($this->property) $this->property->dump();
		echo " : ";
		if ($this->expression) $this->expression->dump(" ");
		if ($this->priority == self::IMPORTANT) echo " !important";
		echo "</span>";
	}

	public function __sleep() {
		return array("property", "expression", "resolved", "priority");
	}
}

class CSSSimpleSelector extends CSSNode {
	public $initial;
	public $predicates = array();
	public function __construct($initial, $predicate) {
		$this->initial = $initial;
		if ($predicate !== null) $this->predicates[] = $predicate;
	}

	public function toXPath() {
		$result = $this->initial;
		foreach($this->predicates as $predicate)
		$result .= $predicate->toXPath();
		return $result;
	}
		
	public function specifity() {
		$s = $this->initial != "*" ? 0x01 : 0x00;
		foreach($this->predicates as $predicate)
		$s += $predicate->specifity();
		return $s;
	}
	public function dump() {
		echo $this->initial; echo " ";
		foreach($this->predicates as $p)
		if (is_object($p))
		$p->dump();
	}
}

class CSSPredicate extends CSSNode {
	const ATTRIBUTE_CONTAINS_SPACE_SEPARATED  = 0;
	const ATTRIBUTE_CONTAINS_HYPHEN_SEPARATED = 1;
	const ATTRIBUTE_EQUALS                    = 2;
	const HAS_ATTRIBUTE                       = 3;
	public $type;
	public $parameters;
	public function __construct($type, $parameters) {
		$this->type = $type;
		$this->parameters = $parameters;
	}
	public function toXPath() {
		switch ($this->type) {
			case self::ATTRIBUTE_CONTAINS_SPACE_SEPARATED:
				return '[contains(concat(" ", @'.$this->parameters[0].', " "), " '.$this->parameters[1].' ")]';
			case self::ATTRIBUTE_CONTAINS_HYPHEN_SEPARATED:
				return '[@'.$this->parameters[0].'="'.$this->parameters[1].'" or starts-with(@'.$this->parameters[0].', "'.$this->parameters[1].'-")]';
			case self::ATTRIBUTE_EQUALS:
				return '[@'.$this->parameters[0].'="'.$this->parameters[1].'"]';
			case self::HAS_ATTRIBUTE:
				return '[@'.$this->parameters[0].']';
		}
	}
	public function specifity() {
		if ($this->parameters[0] == "id")
		return 0x00010000;
		else
		return 0x00000100;
	}
	public function dump() {
		list($p1, $p2) = array_pad($this->parameters, 2, "");
		switch ($this->type) {
			case self::ATTRIBUTE_CONTAINS_SPACE_SEPARATED  : echo "[@$p1 contains(s) '$p2']"; break;
			case self::ATTRIBUTE_CONTAINS_HYPHEN_SEPARATED : echo "[@$p1 contains(h) '$p2']"; break;
			case self::ATTRIBUTE_EQUALS                    : echo "[@$p1 equals '$p2']"; break;
			case self::HAS_ATTRIBUTE                       : echo "[has @$p1]"; break;
		}
	}
}

class CSSCombinator extends CSSNode {
	const DESCENDANT  = 0;
	const CHILD       = 1;
	const FOLLOWED    = 2;
	const FIRST_CHILD = 3;
	public $combinator;
	public $left;
	public $right;
	public function __construct($combinator, $left, $right) {
		$this->combinator = $combinator;
		$this->left = $left;
		$this->right = $right;
	}
	public function toXPath() {
		$E = $this->left->toXPath();
		$F = $this->right ? $this->right->toXPath() : null;
		switch ($this->combinator) {
			case self::DESCENDANT:
				return "$E//$F";
			case self::CHILD:
				return "$E/$F";
			case self::FOLLOWED:
				return "$E/following-sibling::*[1]/self::$F";
			case self::FIRST_CHILD:
				return "*[1]/self::$E";
		}
	}
	public function specifity() {
		return $this->left->specifity() + ($this->right ? $this->right->specifity() : 0x00000100);
	}
	public function dump() {
		$this->left->dump();
		switch($this->combinator) {
			case self::DESCENDANT: echo " // "; break;
			case self::CHILD: echo " / "; break;
			case self::FOLLOWED: echo " + "; break;
			case self::FIRST_CHILD: echo " first-child "; break;
		}
		if ($this->right) $this->right->dump();
	}
}

class CSSRuleSet extends CSSNode {
	public $selectors;
	public $declarations;
	public function __construct($selectors, $declarations) {
		$this->selectors = $selectors;
		$this->declarations = $declarations;
	}
	public function dump() {
		echo "<span style='color:blue'>";
		$this->selectors->dump(" | ");
		echo "</span><br/>";
//		foreach($this->selectors->values as $key => $selector) {
//			if ($key != 0) echo " | ";
//			echo $selector->toXPath();
//		}
//		echo "<br/>";
		echo '<div class="level">';
		$this->declarations->dump("<br/>");
		echo '</div>';
		echo "<br/><br/>";
	}

}