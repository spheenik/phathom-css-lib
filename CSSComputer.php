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
if (CSSComputer::$initialized == false) {
    CSSComputer::$initialized = true;
    CSSComputer::$defaults = array(
        CSS::_LINE_HEIGHT => new CSSNumber(110.0, CSSNumber::PERCENT),
    );
}

class CSSComputer {

    public static $initialized = false;

    public static $defaults;

    private static $colors = array(
        CSS::_AQUA => array('rgb', 0.0, 1.0, 1.0),
        CSS::_BLACK => array('rgb', 0.0, 0.0, 0.0),
        CSS::_BLUE => array('rgb', 0.0, 0.0, 1.0),
        CSS::_FUCHSIA => array('rgb', 1.0, 0.0, 1.0),
        CSS::_GRAY => array('rgb', 0.5, 0.5, 0.5),
        CSS::_GREEN => array('rgb', 0.0, 0.5, 0.0),
        CSS::_LIME => array('rgb', 0.0, 1.0, 0.0),
        CSS::_MAROON => array('rgb', 0.5, 0.0, 0.0),
        CSS::_NAVY  => array('rgb', 0.0, 0.0, 1.0),
        CSS::_OLIVE  => array('rgb', 0.5, 0.5, 0.0),
        CSS::_ORANGE => array('rgb', 1.0, 0.65, 0.0),
        CSS::_PURPLE => array('rgb', 0.5, 0.0, 0.5),
        CSS::_RED => array('rgb', 1.0, 0.0, 0.0),
        CSS::_SILVER => array('rgb', 0.75, 0.75, 0.75),
        CSS::_TEAL => array('rgb', 0.0, 0.5, 0.5),
        CSS::_WHITE => array('rgb', 1.0, 1.0, 1.0),
        CSS::_YELLOW => array('rgb', 1.0, 1.0, 0.0),
    );


    private static $referenceValues = array(
        CSS::_LINE_HEIGHT => array(0, CSS::_FONT_SIZE),
        CSS::_BACKGROUND_POSITION => 0,
    );

    private static $cssFuncs = array(
        "cmyk" => "funcCmyk",
        "rgb"  => "funcRgb",
    );

    private static $preResolveFuncs = array(
        CSS::_BACKGROUND_COLOR => "preColor",
        CSS::_COLOR => "preColor",
        CSS::_LINE_HEIGHT => "preLineHeight",
    );

    private static $postResolveFuncs = array(
    );

    public static function compute(Attributes $me = null, Attributes $parent = null, $prop, CSSNode $spec) {
        if (isset(self::$preResolveFuncs[$prop])) {
            $spec = call_user_func(array(__CLASS__, self::$preResolveFuncs[$prop]), $me, $parent, $spec);
        }
        $result = self::resolve($me, $parent, $prop, $spec);
        if ($result !== null && isset(self::$postResolveFuncs[$prop])) {
            $result = call_user_func(array(__CLASS__, self::$postResolveFuncs[$prop]), $me, $parent, $result);
        }
        return $result;
    }

    private static function funcCmyk() {
        $result = array("cmyk");
        foreach(func_get_args() as $num) {
            if ($num->unit == CSSNumber::NONE)
                $result[] = max(0.0, min(255.0, $num->value))/255.0;
            else
                $result[] = max(0.0, min(100.0, $num->value))/100.0;
        }
        //var_dump($result);
        return $result;
    }

    private static function funcRgb() {
        $result = array("rgb");
        foreach(func_get_args() as $num) {
            if ($num->unit == CSSNumber::NONE)
                $result[] = max(0.0, min(255.0, $num->value))/255.0;
            else
                $result[] = max(0.0, min(100.0, $num->value))/100.0;
        }
        $result[] = 0.0;
        //var_dump($result);
        return $result;
    }

    private static function preLineHeight($me, $parent, $spec) {
        if ($spec instanceof CSSIdent) {
            if ($spec->tokenType == CSS::_NORMAL)
                return self::$defaults[CSS::_LINE_HEIGHT];
        } else if ($spec instanceof CSSNumber) {
            if ($spec->unit == CSSNumber::NONE) {
                return new CSSNumber($spec->value*100.0, CSSNumber::PERCENT);
            }
        }
        return $spec;
    }

    private static function preColor($me, $parent, $spec) {
        if ($spec instanceof CSSIdent) {
            if ($spec->tokenType == CSS::_TRANSPARENT)
                return $spec;
            if (isset(self::$colors[$spec->tokenType]))
                return new CSSColor(self::$colors[$spec->tokenType]);
            else
                throw new LithronException("Cannot compute color");
        } else if ($spec instanceof CSSColor) {
            $hash = $spec->value;
            switch(strlen($hash)) {
                case 3: $type = "rgb"; $half = true; break;
                case 4: $type = "cmyk"; $half = true; break;
                case 6: $type = "rgb"; $half = false; break;
                case 8: $type = "cmyk"; $half = false; break;
                default: throw new LithronException("Unsupported CSSColor hash length of ".strlen($hash));
            }
            $raw = hexdec($hash);
            $cooked = array();
            $c = strlen($type);
            for ($i = 0; $i < $c; $i++) {
                $val = $raw & ($half ? 0xF : 0xFF);
                if ($half) $val = $val | $val << 4;
                $val /= 255.0;
                array_unshift($cooked, $val);
                $raw >>= $half ? 4 : 8;
            }
            array_unshift($cooked, $type);
            //var_dump($cooked);
            return new CSSColor($cooked);
        }
        return $spec;
    }

    private static function resolve(Attributes $me = null, Attributes $parent = null, $prop, CSSNode $spec) {
        if ($spec instanceof CSSReference) {

            /* REFERENCE */

            if (isset($me[$spec->tokenType])) {
                return $me[$spec->tokenType];
            } else {
                return null;
            }
            
        } else if ($spec instanceof CSSIdent) {

            /* IDENT */

            if ($spec->tokenType == CSS::_PRIM_UNKNOWN_IDENT)
                return $spec->value;
            else if ($spec->tokenType == CSS::_INHERIT)
                return isset($parent[$prop]) ? $parent[$prop] : null;
            else
                return $spec->tokenType;

        } else if ($spec instanceof CSSNumber) {

            /* NUMBER */

            switch($spec->unit) {
                case CSSNumber::NONE:
                case CSSNumber::UNKNOWN:
                case CSSNumber::PT:
                case CSSNumber::PX:
                    return $spec->value;
                case CSSNumber::EM:
                    if ($prop === CSS::_FONT_SIZE)
                        return $spec->value*$parent[CSS::_FONT_SIZE];
                    if (!isset($me[CSS::_FONT_SIZE]))
                        return null;
                    return $spec->value*$me[CSS::_FONT_SIZE];
                case CSSNumber::EX:
                    if ($prop === CSS::_FONT_SIZE)
                        return $spec->value*$parent[CSS::_FONT_SIZE]*$me->steward->getFontMetrics($me->getFontId(), "xheight");
                    if (!isset($me[CSS::_FONT_SIZE]))
                        return null;
                    return $spec->value*$me[CSS::_FONT_SIZE]*$me->steward->getFontMetrics($me->getFontId(), "xheight");
                case CSSNumber::CM:
                    return $spec->value / 2.54 * 72.0;
                case CSSNumber::MM:
                    return $spec->value / 25.4 * 72.0;
                case CSSNumber::IN:
                    return $spec->value * 72.0;
                case CSSNumber::PC:
                    return $spec->value * 12.0;
                case CSSNumber::PERCENT:
                    $passThrough = array(CSS::_WIDTH, CSS::_HEIGHT, CSS::_MARGIN_BOTTOM, CSS::_MARGIN_LEFT, CSS::_MARGIN_RIGHT, CSS::_MARGIN_TOP);
                    if (in_array($prop, $passThrough))
                        return $spec; // return as CSSNumber, and let the layout determine
                    if (!isset(self::$referenceValues[$prop]))
                        throw new LithronException("No reference value set for percentage computation of '{0}'", CSS::toString($prop));
                    $refDesc = self::$referenceValues[$prop];
                    if (!is_array($refDesc))
                        return null;
                    $ref = $refDesc[0] === 0 ? $me : $parent;
                    $refVal = $ref[$refDesc[1]];
                    if (!is_double($refVal))
                        return null;
                    return $refVal * $spec->value / 100.0;
                default:
                    throw new LithronException("Cannot compute CSSNumber with unit '{0}'", $spec->unit);

            }
        } else if ($spec instanceof CSSColor) {

            /* COLOR */

            return $spec->value;

        } else if ($spec instanceof CSSString) {

            /* STRING */

            return $spec->value;

        } else if ($spec instanceof CSSFunction) {

            /* FUNCTION */
                                                            
            if (!isset(self::$cssFuncs[$spec->name]))
                throw new LithronException("Unknown CSSFunction '{0}'", $spec->name);
            return call_user_func_array(array(__CLASS__, self::$cssFuncs[$spec->name]), $spec->params->values);

        } else {
            throw new LithronException("Cannot compute property of type '{0}'", CSS::toString($spec));
        }

    }

}
?>