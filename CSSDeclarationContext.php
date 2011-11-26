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
class CSSDeclarationContext extends Context {

	private $decl;
	private $affected;
	private $resolved = array();
	

	public function __construct(CSSDeclaration $decl) {
		$this->decl = $decl;
		parent::__construct(count($decl->expression->values));
	}
	
	public function getInitialState() {
		return array(array(0, 0, array()));
	}
	
	public function matchIdent($ident) {
    	if ($this->tracingEnabled) $this->log[] = "try ident <b>".CSS::toString($ident)."</b> at ".$this->state[POS]."<br/>";
    	$ret = isset($this->decl->expression->values[$this->state[POS]]) ? $this->decl->expression->values[$this->state[POS]]->matchIdent($ident) : false;
    	if ($this->tracingEnabled) $this->log[] = ($ret ? "" : "un")."successful ident <b>".CSS::toString($ident)."</b> at ".$this->state[POS]."<br/>";
		if ($ret) {
			$this->push($this->decl->expression->values[$this->state[POS]]);
			$this->state[POS]++;
		}
		return $ret;
	}

	public function matchOperator($which) {
		if ($this->tracingEnabled) $this->log[] = "try operator <b>".$which."</b> at ".$this->state[POS]."<br/>";
		if (isset($this->decl->expression->values[$this->state[POS]])) {
			$o = $this->decl->expression->values[$this->state[POS]];
			$ret = $o instanceof CSSOperator && $o->value === $which;
		} else {
			$ret = false;
		}
		if ($this->tracingEnabled) $this->log[] = ($ret ? "" : "un")."successful operator <b>".$which."</b> at ".$this->state[POS]."<br/>";
		if ($ret) {
			$this->state[POS]++;
		}
		return $ret;
	}
	
	public function matchFunction($which, $paramFunc) {
		if ($this->tracingEnabled) $this->log[] = "try function <b>".$which."</b> at ".$this->state[POS]."<br/>";
		if (isset($this->decl->expression->values[$this->state[POS]])) {
			$o = $this->decl->expression->values[$this->state[POS]];
			$ret = $o instanceof CSSFunction && $o->name === $which;
			if ($ret) {
				$c = new CSSDeclarationContext(new CSSDeclaration(null, $o->params, null));
				$c->setTracingEnabled($this->tracingEnabled);
				$ret = $paramFunc($c) && $c->atEnd();
				$this->log[] = "<ul><li>".implode("</li><li>", $c->log)."</li></ul>";
			}
		} else {
			$ret = false;
		}
		if ($this->tracingEnabled) $this->log[] = ($ret ? "" : "un")."successful function <b>".$which."</b> at ".$this->state[POS]."<br/>";
		if ($ret) {
			$this->push($this->decl->expression->values[$this->state[POS]]);
			$this->state[POS]++;
		}
		return $ret;
	}
	
	public function setAffectedProperties() {
		$this->affected = func_get_args();
	}

	
	public function shorthand() {
		$props = func_get_args();
		$src = $this->state[VALUES];
		$this->state[VALUES] = array();
		$this->resolved[$props[0]] = $src;
	}
	
	public function assign() {
		$props = func_get_args();
		$src = $this->state[VALUES];
		$this->state[VALUES] = array();
		$this->resolved[$props[0]] = $src;
	}
	
	public function inflate() {
		$props = func_get_args();
		$src = $this->state[VALUES];
		$this->state[VALUES] = array();
		switch(count($src)) {
			case 1:
				$t = array(array(0, 1, 2, 3));
				break;
			case 2:
				$t = array(array(0, 2), array(1, 3));
				break;
			case 3:
				$t = array(array(0), array(1, 3), array(2));
				break;
			case 4:
				$t = array(array(0), array(1), array(2), array(3));
				break;
		}
		foreach($t as $key => $dests)
			foreach($dests as $dest) {
				$this->resolved[$props[$dest]] = array($src[$key]);
			}
	}
	
	public function finalize() {
		$src = $this->state[VALUES];
		$this->state[VALUES] = array();
		if (count($this->affected) > 1) {
			// shorthand
			foreach($this->affected as $a) {
				if (!isset($this->resolved[$a])) {
					$this->resolved[$a] = count($src) == 0 ? CSS::$initials[$a] : $src;
				}
			}
		} else {
			// no shorthand
			$this->resolved[$this->affected[0]] = $src;
		}
	}
	
	public function run() {
		$parts = explode("-", $this->decl->property->value);
		foreach($parts as &$part) $part = ucfirst($part);
		$funcName = implode("", $parts);
		if (!method_exists("CSSValidator", $funcName."Rule")) {
			return false;
		}
    	if ($this->tracingEnabled) $this->log[] = "<h2>Validation of ".$this->decl->property->value."</h2>";
    	
    	$valid = CSSValidator::run($funcName, $this);
    	$this->decl->resolved = $this->resolved;
    	//var_dump($this->resolved); 
		return $valid;
	}

}
