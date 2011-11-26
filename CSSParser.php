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
class CSSParser extends Phathom {

    public static function SRule() {
    	return self::sequence(
    		self::optionalSpace(),
    		self::zeroOrMore(self::stmt()), 
    		self::EOI
    	);
    }
    
    public static function stmtRule() {
    	return self::firstOf(
    		self::rule()
    	);
    }
    
    public static function ruleRule() {
    	return self::sequence(
    		self::ruleLhs(),
    		"{",
    		self::decls(),
    		self::firstOf(";", ""),
    		self::optionalSpace(),
    		"}",
    		self::optionalSpace(),
    		function (StringContext $c) {
    			$declarations = $c->pop();	
    			$selectors = $c->pop(); 
    			$rule = new CSSRuleSet($selectors, $declarations);
    			//var_dump($rule);
    			$rule->dump();  
    		}
    	);
    }
    
    public static function ruleLhsRule() { 
		return self::sequence(
			function(StringContext $c) { $c->push(new CSSArray()); },
			self::selector(), function (StringContext $c) { $c->peek(1)->values[] = $c->pop(); },
			self::zeroOrMore(
				",", 
				self::optionalSpace(), 
				self::selector(), function (StringContext $c) { $c->peek(1)->values[] = $c->pop(); }
			),
			function(StringContext $c) { /* var_dump($c->peek()); */ }
		);
    }
    
    public static function selectorRule() {
    	return self::sequence(
    		self::simpleSelector(),
    		self::firstOf(
    			self::sequence(
    				self::combinator(), 
    				self::selector(),
    				function (StringContext $c) { $c->push(new CSSCombinator($c->pop(1), $c->pop(1), $c->pop())); }
    			),
    			self::optionalSpace()
    		)
    	);
    }

    public static function simpleSelectorRule() {
    	 return self::firstOf(
    	 	self::sequence(
    	 		self::firstOf(
    	 			self::sequence(self::ident(), function (StringContext $c) { $c->push(new CSSSimpleSelector($c->pop()->value, null)); }), 
    	 			self::sequence("*", function (StringContext $c) { $c->push(new CSSSimpleSelector("*", null)); })
    	 		),
    	 		self::zeroOrMore(
    	 			self::simpleSelectorEtc(), function (StringContext $c) { $c->peek(1)->predicates[] = $c->pop(); }
    	 		)
    	 	),
    	 	self::sequence(
	    	 	function (StringContext $c) { $c->push(new CSSSimpleSelector("*", null)); },
    	 		self::oneOrMore(
    	 			self::simpleSelectorEtc(), function (StringContext $c) { $c->peek(1)->predicates[] = $c->pop(); }
    	 		)
    	 	)
    	 );
    }
    
    public static function simpleSelectorEtcRule() {
    	return self::firstOf(
    		self::sequence("#", self::ident(), function(StringContext $c) { $c->push(new CSSPredicate(CSSPredicate::ATTRIBUTE_EQUALS, array("id", $c->pop()->value))); }),
    		self::sequence(".", self::ident(), function(StringContext $c) { $c->push(new CSSPredicate(CSSPredicate::ATTRIBUTE_CONTAINS_SPACE_SEPARATED, array("class", $c->pop()->value))); }),
    		self::sequence(":", self::ident(), function(StringContext $c) { /* TODO */ $c->push(new CSSPredicate(CSSPredicate::ATTRIBUTE_CONTAINS_SPACE_SEPARATED, array("pseudo", $c->pop()->value))); }),
    		self::sequence(
    			self::regex("/\[\h*/"),
    			self::ident(), 
    			function(StringContext $c) { $c->push(new CSSPredicate(CSSPredicate::HAS_ATTRIBUTE, array($c->pop()->value))); },
    			self::optional(
    				self::firstOf(
    					self::sequence("=", function(StringContext $c) { $c->peek()->type = CSSPredicate::ATTRIBUTE_EQUALS; }), 
    					self::sequence("~=", function(StringContext $c) { $c->peek()->type = CSSPredicate::ATTRIBUTE_CONTAINS_SPACE_SEPARATED; }), 
    					self::sequence("|=", function(StringContext $c) { $c->peek()->type = CSSPredicate::ATTRIBUTE_CONTAINS_HYPHEN_SEPARATED; })
    				),
    				self::firstOf(self::ident(), self::string()),
    				function(StringContext $c) { $c->peek(1)->parameters[] = $c->pop()->value; }
    			),
    			self::regex("/\h*\]/")
			)
    	);
    }
    
    public static function combinatorRule() {
    	return self::firstOf(
    		self::sequence(self::regex("/\h*\+\h*/"), function (StringContext $c) { $c->push(CSSCombinator::FOLLOWED); }),
    		self::sequence(self::regex("/\h*\>\h*/"), function (StringContext $c) { $c->push(CSSCombinator::CHILD); }),
    		self::sequence(self::regex("/\h+/"), function (StringContext $c) { $c->push(CSSCombinator::DESCENDANT); })
    	);
    }
    
    public static function declsRule() {
    	return self::sequence(
    		self::optionalSpace(),
    		function (StringContext $c) { $c->push(new CSSArray()); },
    		self::firstOf(
    			self::sequence(
		    		self::declaration(), function(StringContext $c) { $c->peek(1)->values[] = $c->pop(); },
		    		self::zeroOrMore(
	    				";",
	    				self::optionalSpace(),
	    				self::declaration(), function(StringContext $c) { $c->peek(1)->values[] = $c->pop(); }
	    			)
    			),
    			""
    		)
    	);
    }
    
    public static function declarationRule() {
    	return self::sequence(
    		self::ident(),
    		self::optionalSpace(),
    		":",
    		self::optionalSpace(),
    		self::expr(),
    		self::firstOf(
    			self::sequence("!important", function (StringContext $c) { $c->push(CSSDeclaration::IMPORTANT); } ), 
    			self::sequence("", function (StringContext $c) { $c->push(CSSDeclaration::NORMAL); } ) 
    		),
    	    function(StringContext $c) { 
    			$prio = $c->pop();
    			$expr = $c->pop();
    			$prop = $c->pop();
    			$decl = new CSSDeclaration($prop, $expr, $prio);
    			$c->push($decl);
    			//var_dump($decl);
    			$val = new CSSDeclarationContext($decl);
    			//$val->setTracingEnabled(true);
    			$decl->isValid = $val->run();
    			//$val->dumpLog();
    		}
    	);
    }
    
    public static function exprRule() {
    	return self::sequence(
    		function (StringContext $c) { $c->push(new CSSArray()); },
    		self::term(), function(StringContext $c) { $c->peek(1)->values[] = $c->pop(); },
    		self::zeroOrMore(
    			self::optOperator(), function(StringContext $c) { $op = $c->pop(); if ($op != null) $c->peek()->values[] = $op; },
    			self::term(), function(StringContext $c) { $c->peek(1)->values[] = $c->pop(); }
    		)
    	);
    }
    
    public static function termRule() {
    	return self::sequence(
    		self::firstOf(
	    		self::uri(),
    			self::func(),
    			self::ident(),
    			self::sequence(
	    			self::firstOf("+", "-"),
	    			function (StringContext $c) { $c->push($c->currentMatch() == "+" ? 1 : -1); }, 
	    			self::measure(),
	    			function (StringContext $c) { $f = $c->pop(1); $c->peek()->value *= $f; } 
	    		),
	    		self::measure(),
	    		self::string(),
	    		self::hexcolor()
    		),
	    	self::optionalSpace()
    	);
    }
    
    public static function measureRule() {
    	return self::sequence(
    		self::regex("/(\d*\.\d+|\d+)([a-z%]*)/"),
    		function (StringContext $c) { $unit = $c->subgroup(2); if ($unit == "%") $unit = "percent"; $c->push(new CSSNumber((double)$c->subgroup(1), $unit)); }
    	);
    }
    
    public static function optOperatorRule() {
    	return self::firstOf(
			self::sequence("/", self::optionalSpace(), function(StringContext $c) { $c->push(new CSSOperator("/")); } ), 
			self::sequence(",", self::optionalSpace(), function(StringContext $c) { $c->push(new CSSOperator(",")); } ),
			self::sequence("", function(StringContext $c) { $c->push(null); } )
    	);
    }
    
    public static function funcRule() {
    	return self::sequence(
    		self::ident(),
    		function (StringContext $c) { $c->push(new CSSFunction($c->pop()->value, new CSSArray())); },
    		"(",
    		self::optional(self::expr(), function (StringContext $c) { $exp = $c->pop(); $c->peek()->params = $exp; }),
			")"
    	);
    }
    
    public static function identRule() {
    	return self::sequence(
    		self::regex("/-?[A-za-z_][A-Za-z0-9_-]*/"),
    		function (StringContext $c) { $c->push(new CSSIdent($c->currentMatch())); }
    	);
    }
    
    public static function stringRule() {
    	return self::firstOf(
    		self::sequence(
    			self::regex('/"(([^\n\r\f\\\\"]|\\\\(\r\n|\r|\n|\f|.))*)"/'), 
    			function (StringContext $c) { $c->push(new CSSString($c->subgroup(1), '"')); }
    		),
    		self::sequence(
    			self::regex("/'(([^\n\r\f\\\\']|\\\\(\r\n|\r|\n|\f|.))*)'/"), 
    			function (StringContext $c) { $c->push(new CSSString($c->subgroup(1), "'")); }
    		)
    	);
    }
    
    public static function uriRule() {
    	return self::sequence(
    		"url(",
    		self::firstOf(
    			self::sequence(
    				self::string(), 
    				function (StringContext $c) { $str = $c->pop(); $c->push(new CSSUri($str->value, $str->delimiter)); }
    			),
    			self::sequence(
    				self::regex("/\h*(([!#$%&*-~]|[\200-\377]|\\\\[^\v])*)\h*/"),
    				function (StringContext $c) {$c->push(new CSSUri($c->subgroup(1), "")); }
    			)
    		),
    		")"
    	);
    }
    
    public static function hexcolorRule() {
    	return self::sequence(
    		self::regex("/#([A-Za-z0-9]+)/"),
    		function (StringContext $c) { $c->push(new CSSColor($c->subgroup(1))); }
    	);
    }
    
    public static function optionalSpaceRule() {
    	return self::regex("@(\s|/\*(\s|.)*?\*/)*@");
    }
	
}

?>