class CSSValidator extends Phathom {

public static function matchIdent($ident) {
	return function($c) use ($ident) { return $c->matchIdent($ident); };
}

public static function matchFunction($name, $parmMatcher) {
	return function($c) use ($name, $parmMatcher) { return $c->matchFunction($name, $parmMatcher); };
}

public static function matchOperator($which) {
	return function($c) use ($which) { return $c->matchOperator($which); };
}


<?php 

foreach (ValidationGenerator::$usedRules as $rule) { 
/* @var $rule CVRule */ 
if ($rule->left instanceof CVType) : ?>
public static function <?php echo ValidationGenerator::toCamelCase($rule->left->name); ?>TypeRule() {
	return <?php echo str_replace("\n", "\n\t", $rule->right); ?>;
}
<?php else : ?>
public static function <?php echo ValidationGenerator::toCamelCase($rule->left->name); ?>AttrRule() {
	return <?php echo str_replace("\n", "\n\t", $rule->right); ?>;
}

public static function <?php echo ValidationGenerator::toCamelCase($rule->left->name); ?>Rule() {
	return self::sequence(
<?php echo $rule->initFunction(); ?><?php if ($rule->allowsInherited) : ?>
		self::firstOf(
			self::<?php echo ValidationGenerator::toCamelCase($rule->left->name); ?>Attr(), 
			<?php echo new CVIdent('inherit'); ?>
			
		),
<?php else : ?>
		self::<?php echo ValidationGenerator::toCamelCase($rule->left->name); ?>Attr(),
<?php endif; ?>
		self::EOI,
		function ($c) { $c->finalize(); }		
	);
}	
<?php endif; ?>

<?php } ?>

}