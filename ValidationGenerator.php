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
class ValidationGenerator {
	
	public static $attributes = array();
	public static $inheriteds = array();
	public static $initials = array();
	public static $idents = array();
	public static $numberUnits = array();
	public static $usedRules = array();
	
	private static function processRecursive($rule, $entity) {
		if (isset($entity->shorthand)) {
			$rule->shorthandFunctions[] = $entity;
			if (!isset($entity->shorthand->destAttributes)) {
				$entity->shorthand->destAttributes = array();
				@$parms =  $entity->shorthand->params->members;
				switch($entity->shorthand->name) {
					case "shorthand":
						$entity->shorthand->destAttributes = array(self::toInternal($entity->name));
						break;
					case "inflate":
						$str = $parms[0]->value;
						foreach (array("top", "right", "bottom", "left") as $subst)
						$entity->shorthand->destAttributes[] = self::toInternal(str_replace("%", $subst, $str));
						break;
					case "assign":
						$entity->shorthand->destAttributes[] = self::toInternal($rule->left->name.$parms[0]->value);
						break;
				}
				//var_dump($entity->shorthand->destAttributes);
			}
		}
	
		if ($entity instanceof CVGroup) {
			foreach ($entity->members as $sub) {
				self::processRecursive($rule, $sub);
			}
		} else if ($entity instanceof CVFunction) {
			foreach ($entity->params->members as $sub) {
				self::processRecursive($rule, $sub);
			}
		} else if ($entity instanceof CVIdent || $entity instanceof CVAttribute) {
			if (!in_array($entity->name, self::$idents)) {
				self::$idents[] = $entity->name;
			}
		}
	}
	
	public static function toInternal($s, $prefix = "CSS::_") {
		return $prefix.str_replace("-", "_", strtoupper($s));
	}
	
	public static function toCamelCase($s) {
		$parts = explode("-", $s);
		foreach($parts as &$part)
		$part = ucfirst($part);
		return implode("", $parts);
	}
	
	public static function generate($wanted_subsets, $dirOut) {
		$c = new StringContext(file_get_contents(dirname(__FILE__)."/css_2.1.spec"));
		$c->setTracingEnabled(false);
		$result = CSSSpecParser::run("S", $c);
		$c->dumpLog();
		if  ($result === false) die("failed to parse spec.");
		
		$rules = $c->pop();
		foreach ($rules as $rule) {
			/* @var $rule CVRule */
			if (count(array_intersect($rule->media, $wanted_subsets)) == 0) {
				continue;
			}
			self::$usedRules[] = $rule;
		}
		
		foreach (self::$usedRules as $rule) {
			/* @var $rule CVRule */
			self::processRecursive($rule, $rule->right);
			$lastElem = $rule->right->members[count($rule->right->members)-1];
			if ($rule->validUnits != null) {
				self::$numberUnits[$lastElem->name] = $rule->validUnits;
			}
			if ($rule->left instanceof CVAttribute) {
				self::processRecursive($rule, $rule->left);
		
				$rule->allowsInherited = ($lastElem instanceof CVIdent && $lastElem->name == "inherit");
				if ($rule->allowsInherited) {
					array_pop($rule->right->members);
				}
		
				if ($rule->default != null) {
					// no shorthand
					self::$initials[$rule->left->name] = $rule->default;
					self::$attributes[] = $rule->left;
					if ($rule->inherited) {
						self::$inheriteds[] = $rule->left;
					}
				}
			}
		}
		sort(self::$idents);
		
		//var_dump(self::$numberUnits);
		//var_dump(self::$usedRules);
		//var_dump(self::$attributes);
		//var_dump(self::$inheriteds);
		//var_dump(self::$idents);

		$dirIn = dirname(__FILE__)."/templates";
		$templates = scandir($dirIn);
		foreach($templates as $t) {
			//var_dump($t);
			if ($t[0] == ".") continue;
			ob_start();
			require($dirIn."/".$t);
			$output = ob_get_contents();
			ob_end_clean();
			
			$nameOut = $dirOut."/".str_replace(".template", "", $t);
			file_put_contents($nameOut, "<?php\n".$output."\n?>\n");
			
		}
		
		$ip = get_include_path();
		set_include_path($ip.PATH_SEPARATOR.$dirOut);
		
		set_include_path($ip);
		
		
	}
	
}

?>

