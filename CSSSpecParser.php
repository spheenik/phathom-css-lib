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
class CSSSpecParser extends Phathom {

    public static function SRule() {
    	return self::sequence(
    		function (StringContext $c) { $c->push(array()); },
    		self::zeroOrMore(self::singleEntry()), 
    		self::EOI
    	);
    }
    
    public static function singleEntryRule() {
    	return self::sequence(
    		self::typeRow(),
    		self::definitionRow(),
    		self::defaultRow(),
    		self::inheritRowRule(),
    		self::mediumRow(),
    		self::validUnitsRow(),
    		self::ignoreRow(),
    		self::ignoreRow(),
    		self::ignoreRow(),
    		self::ignoreRow(),
	    	function (StringContext $c) {
    			$validUnits = $c->pop();
    			$media = $c->pop();
    			$inherited = $c->pop();
    			$default = $c->pop();
    			$definition = $c->pop();
    			$entities = $c->pop();
    			foreach($entities as $entity) {
    				$rule = new CVRule();
    				$rule->left = $entity;
    				$rule->right = unserialize(serialize($definition));
    				$rule->default = $default;
    				$rule->inherited = $inherited;
    				$rule->media = $media;
    				if (count($validUnits)) {
    					$rule->validUnits = $validUnits;
    				}
    				$c->append($rule);
    			}
	    	}
    	);
    }
    
    public static function definitionRowRule() {
    	return self::sequence(
			self::entityList(),    		
			self::regex("/\h*\n/")
    	);
    }
    
    public static function entityRule() {
    	return self::sequence(
    		self::firstOf(
    			self::func(),
    			self::ident(),
    			self::type(),
	    		self::attribute(),
	    		self::sequence(
	    			self::regex('/\[\h?/'),
	    			self::entityList(),
	    			self::regex('/\h?\]/')
	    		),
	    		self::operator(),
	    		self::string() 
	    	),
	    	self::firstOf(
	    		self::sequence("*", function (StringContext $c) { $c->push("*"); }),
	    		self::sequence("+", function (StringContext $c) { $c->push("+"); }),
	    		self::sequence("?", function (StringContext $c) { $c->push("?"); }),
	    		self::sequence(
	    			"{", 
	    			self::regex("/\d+/"), function (StringContext $c) { $c->push((int)$c->currentMatch()); },
	    			self::spacedComma(), 
	    			self::regex("/\d+/"), function (StringContext $c) { $c->push((int)$c->currentMatch()); }, 
	    			"}", 
	    			function (StringContext $c) { $c->push(array($c->pop(1), $c->pop())); }
	    		),
	    		self::sequence("", function (StringContext $c) { $c->push(null); })
	    	),
	    	function (StringContext $c) { $c->peek(1)->modifier = $c->pop(); },
	    	self::firstOf(
	    		self::sequence("@", self::func()),
	    		self::sequence("", function (StringContext $c) { $c->push(null); })
	    	),
			function (StringContext $c) { $c->peek(1)->shorthand = $c->pop(); }
    	);
    }
    
    public static function entityListRule() {
    	return self::sequence(
	    	self::entity(),
	    	function(StringContext $c) { $g = new CVGroup(); $g->members[] = $c->pop(); $c->push($g); },
	    	self::zeroOrMore(
	    		self::separator(),
	    		self::entity(),
	    		function(StringContext $c) { $g = $c->peek(2); $g->members[] = $c->pop(); $g->setMode($c->pop()); }
	    	)
    	);
    }
    
    public static function separatorRule() {
    	return self::firstOf(
    		self::sequence(self::regex('/\h*\|\|\h*/'), function(StringContext $c) { $c->push("permutation"); }),
    		self::sequence(self::regex('/\h*\|\h*/'), function(StringContext $c) { $c->push("firstOf"); }),
    		self::sequence(self::regex('/\h*/'), function(StringContext $c) { $c->push("sequence"); })
    	);
    }
    
    public static function defaultEntryRule() {
    	return self::firstOf(
    		self::ident(),
	    	self::attribute(),
	    	self::percentage(),
	    	self::number()
    	);
    }    
    
    public static function defaultRowRule() {
    	return self::sequence(
    		function (StringContext $c) { $c->push(array()); },
    		self::zeroOrMore(
    			self::defaultEntry(), function (StringContext $c) { $c->append($c->pop()); },
    			self::regex('/\h*/')
    		),
    		self::regex('/.*?\n/')
    	);
    }
    
    public static function ignoreRowRule() {
        return self::regex('/.*?\n/');
	}
	
	public static function identRule() {
		return self::sequence(self::regex('/[a-z-]+/'), function(StringContext $c) { $c->push(new CVIdent($c->currentMatch())); });
	}
	
	public static function funcRule() {
		return self::sequence(
			self::regex('/[a-z-]+(?=\()/'), function(StringContext $c) { $c->push(new CVFunction($c->currentMatch())); },
			self::regex('/\(\h*/'),
			self::optional(self::entityList(), function (StringContext $c) { $c->peek(1)->params = $c->pop(); }),
			self::regex('/\)\h*/')
		);
	}
	
	public static function typeRule() {
		return self::sequence('<', self::ident(), '>', function(StringContext $c) { $c->push(new CVType($c->pop()->name)); });
	}
	
	public static function attributeRule() {
		return self::sequence('\'', self::ident(), '\'', function(StringContext $c) { $c->push(new CVAttribute($c->pop()->name)); }); 
	}

	public static function stringRule() {
		return self::sequence(
			self::regex('/"(([^\n\r\f\\\\"]|\\\\(\r\n|\r|\n|\f|.))*)"/'), 
			function (StringContext $c) { $c->push(new CVString($c->subgroup(1))); }
		);
	}
		
	public static function operatorRule() {
		return self::sequence(
			self::firstOf(",", "/"),
			function(StringContext $c) { $c->push(new CVOperator($c->currentMatch()));
		});
	}
	
	public static function numberRule() {
		return self::sequence(
			self::regex("/\d+/"), function(StringContext $c) { $c->push(new CVNumber((double)$c->currentMatch(), "")); }
		);
	}

	public static function percentageRule() {
		return self::sequence(
			self::regex("/\d+%/"), function(StringContext $c) { $c->push(new CVNumber((double)$c->currentMatch(), "percent")); }
		);
	}
	
	public static function attributeOrTypeRule() {
		return self::firstOf(self::type(), self::attribute());
	}
	
	public static function typeRowRule() {
		return self::sequence(
			self::attributeOrType(),
			function(StringContext $c) { $c->push(array($c->pop())); },
			self::zeroOrMore(
				self::space(),
				self::sequence(self::attributeOrType(), function(StringContext $c) { $c->append($c->pop()); })
			),
			"\n"
		);
	}
	
	public static function mediumRule() {
		return self::sequence(
			self::regex("/[a-z]+/"), 
			function (StringContext $c) { $c->push($c->currentMatch()); }
		);
	}
	
	public static function mediumRowRule() {
		return self::sequence(
			self::medium(),
			function(StringContext $c) { $c->push(array($c->pop())); },
			self::zeroOrMore(
				self::spacedComma(), 
				self::sequence(self::medium(), function(StringContext $c) { $c->append($c->pop()); })
			), 
			"\n"
		);
	}
	
	public static function inheritRowRule() {
		return self::sequence(
			self::firstOf(
				self::sequence("yes", function (StringContext $c) { $c->push(true); } ),
				self::sequence("no", function (StringContext $c) { $c->push(false); } )
			),		
			self::regex("/.*?\n/")
		);
	}
	
	public static function validUnitsRowRule() {
		return self::sequence(
			self::firstOf(
				self::sequence(
					self::ident(),
					function (StringContext $c) { $c->push(array($c->pop())); },
					self::zeroOrMore(
						self::regex('/\h*,\h*/'),
						self::ident(),
						function (StringContext $c) { $c->append($c->pop()); }	
					)
				),
				self::sequence("", function (StringContext $c) { $c->push(array()); } )
			),		
			self::regex("/.*?\n/")
		);
	}
		
	public static function optionalSpaceRule() {
		return self::regex('/\h*/');
	}

	public static function spaceRule() {
		return self::regex('/\h+/');
	}
	
	public static function spacedCommaRule() {
		return self::regex('/\h*,\h*/');
	}
}

?>