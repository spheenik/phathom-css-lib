<?php
/*
 * This file is part of Phathom.
*
* Copyright (c) 2011 Martin Schrodt
*
* Permission is hereby granted, free of charge, to any person obtaining a copy
* of this software and associated documentation files (the "Software"), to deal
* in the Software without restriction, including without limitation the rights
* to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
* copies of the Software, and to permit persons to whom the Software is furnished
* to do so, subject to the following conditions:
*
* The above copyright notice and this permission notice shall be included in all
* copies or substantial portions of the Software.
*
* THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
* IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
* FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
* AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
* LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
* OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
* THE SOFTWARE.
*/
class CVBase {
	public $modifier = null;
	
	protected function applyModifiers($src) {
		if (is_array($this->modifier)) {
			$src = "self::nToM(\n\t".implode(", ", $this->modifier).",\n\t".str_replace("\n", "\n\t", $src)."\n)";
		} else switch ($this->modifier) {
			case "+":
				$src = "self::oneOrMore(\n\t".str_replace("\n", "\n\t", $src)."\n)";
				break;
			case "*":
				$src = "self::zeroOrMore(\n\t".str_replace("\n", "\n\t", $src)."\n)";
				break;
			case "?":
				$src = "self::optional(\n\t".str_replace("\n", "\n\t", $src)."\n)";
				break;
			default:
				// do nothing
		}
		
		if (isset($this->shorthand)) {
			$func = $this->shorthand->name."(".implode(", ", $this->shorthand->destAttributes).")";
			$src = "self::sequence(\n\t".str_replace("\n", "\n\t", $src).",\n\tfunction(\$c) { \$c->".$func."; }\n)";
		}
		
		return $src;
	}
	
}


class CVIdent extends CVBase {
	public $name;
	
	public function __construct($name) {
		$this->name = $name;
	}
	
	public function initialConstructor() {
		return "new CSSIdent(".ValidationGenerator::toInternal($this->name).")";	
	}
	
	public function __toString() {
		return $this->applyModifiers("self::matchIdent(".ValidationGenerator::toInternal($this->name).")");
	}
}


class CVType extends CVBase {
	public $name;
	
	public function __construct($name) {
		$this->name = $name;
	}

	public function __toString() {
		return $this->applyModifiers("self::".ValidationGenerator::toCamelCase($this->name)."Type()");
	}
}

class CVString extends CVBase {
	public $value;

	public function __construct($value) {
		$this->value = $value;
	}
}



class CVAttribute extends CVBase {
	public $name;
	
	public function __construct($name) {
		$this->name = $name;
	}
	
	public function initialConstructor() {
		return "new CSSReference(".ValidationGenerator::toInternal($this->name).")";
	}
	
	public function __toString() {
		return $this->applyModifiers("self::".ValidationGenerator::toCamelCase($this->name)."Attr()");
	}
}


class CVFunction extends CVIdent {
	public $params = array();
	
	public function __toString() {
		return $this->applyModifiers("self::matchFunction(\n\t'".$this->name."',\n"."\t".str_replace("\n", "\n\t", $this->params).")");
	}	
}

class CVGroup extends CVBase {
	public $members = array();
	public $mode = null;
	
	public function setMode($mode) {
		if ($this->mode != null && $this->mode !== $mode) {
			throw new Exception("illegal mode override, was ".$this->mode.", set to ".$mode);
		}
		$this->mode = $mode;
	}
	
	public function __toString() {
		$result = array();
		foreach($this->members as $member) {
			$result[] = "\t".str_replace("\n", "\n\t", $member);
		}
		if ($this->mode && count($result) > 1) {
			return $this->applyModifiers("self::".$this->mode."(\n".implode(",\n", $result)."\n)");
		} else {
			return $this->applyModifiers(substr($result[0], 1));
		}
	}
}

class CVOperator extends CVBase {
	public $type;

	public function __construct($type) {
		$this->type = $type;
	}
	
	public function __toString() {
		return "self::matchOperator('".$this->type."')";
	}
}

class CVNumber {

	public $value;
	public $unit;
	
	public function __construct($value, $unit) {
		$this->value = $value;
		$this->unit = $unit;
	}
	
	public function initialConstructor() {
		return "new CSSNumber((double)".$this->value.", \"".$this->unit."\")";
	}	
}

class CVRule {
	public $left;
	public $right;
	public $default;
	public $inherited;
	public $media;
	public $validUnits;
	
	public function initFunction() {
		$props = array();
		if (!isset($this->shorthandFunctions)) {
			// is no shorthand
			$props[] = ValidationGenerator::toInternal($this->left->name);
		} else {
			// is shorthand
			foreach($this->shorthandFunctions as $sh) {
				$props = array_merge($props, $sh->shorthand->destAttributes);
			}
		}
		return "\t\tfunction (\$c) { \$c->setAffectedProperties(".implode(", ", $props)."); },\n";
	}
}

?>