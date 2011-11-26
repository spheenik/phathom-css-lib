<absolute-size>
xx-small | x-small | small | medium | large | x-large | xx-large

no
visual, lithron





<angle>
prim-angle

no
aural
deg, rad, grad




<border-style>
none | hidden | dotted | dashed | solid | double | groove | ridge | inset | outset

no
visual, lithron





<border-width>
thin | medium | thick | <length>

no
visual, lithron





<col-param>
<integer> | <percentage>

no
visual, lithron





<color>
prim-color | cmyk(<col-param>, <col-param>, <col-param>, <col-param>) | rgb(<col-param>, <col-param>, <col-param>) | maroon | red | orange | yellow | olive | purple | fuchsia | white | lime | green | navy | blue | aqua | teal | black | silver | gray

no
visual, lithron





<counter>
counter(<identifier>) | counter(<identifier>, 'list-style-type') | counters(<identifier>, <string>) | counters(<identifier>, <string>, 'list-style-type')

no
visual, lithron





<family-name>
prim-string | prim-any-ident

no
visual, lithron





<frequency>
prim-zero | prim-frequency

no
aural
hz, khz




<generic-family>
times | helvetica | courier | zapf | serif | sans-serif | monospace

no
visual, lithron





<generic-voice>
male | female | child

no
aural





<identifier>
prim-any-ident

no
visual, lithron





<integer>
prim-zero | prim-integer

no
visual, lithron





<length>
prim-zero | prim-length

no
visual, lithron
em, ex, px, pt, cm, mm, in, pc




<margin-width>
<length> | <percentage> | auto

no
visual, lithron





<number>
prim-zero | prim-integer | prim-number

no
visual, lithron





<padding-width>
<length> | <percentage>

no
visual, lithron





<percentage>
prim-percentage

no
visual, lithron
percent




<relative-size>
larger | smaller

no
visual, lithron





<rect-param>
<length> | auto

no
visual, lithron





<shape>
rect(<rect-param>, <rect-param>, <rect-param>, <rect-param>)

no
visual, lithron





<specific-voice>
prim-ident

no
aural





<string>
prim-string

no
visual, lithron





<time>
prim-zero | prim-time

no
aural
ms, s




<uri>
prim-uri

no
visual, lithron





<border-color-type>
<color> | transparent

no
visual, lithron





'azimuth'
<angle> | [[ left-side | far-left | left | center-left | center | center-right | right | far-right | right-side ] || behind ] | leftwards | rightwards | inherit
center
yes
aural





'background-attachment'
scroll | fixed | inherit
scroll
no
visual, lithron





'background-color'
<color> | transparent | inherit 
transparent
no
visual, lithron





'background-image'
<uri> | none | inherit
none
no
visual, lithron





'background-position'
[ [ <percentage> | <length> | left | center | right ] [ <percentage> | <length> | top | center | bottom ]? ] | [ [ left | center | right ] || [ top | center | bottom ] ] | inherit
0% 0%
no
visual, lithron





'background-repeat'
repeat | repeat-x | repeat-y | no-repeat | inherit
repeat
no
visual, lithron





'background'
['background-color'@shorthand() || 'background-image'@shorthand() || 'background-repeat'@shorthand() || 'background-attachment'@shorthand() || 'background-position'@shorthand()] | inherit
//see individual properties
no
visual, lithron





'border-collapse'
collapse | separate | inherit
separate
yes
visual





'border-color'
[ <color> | transparent ]{1,4}@inflate("border-%-color") | inherit
// see individual properties
no
visual, lithron





'border-spacing'
[<length> <length>?] | inherit
0
yes
visual





'border-style'
<border-style>{1,4}@inflate("border-%-style") | inherit
// see individual properties
no
visual, lithron





'border-top' 'border-right' 'border-bottom' 'border-left'
[ <border-width>@assign("-width") || <border-style>@assign("-style") || <border-color-type>@assign("-color") ] | inherit
// see individual properties
no
visual, lithron





'border-top-color' 'border-right-color' 'border-bottom-color' 'border-left-color'
<border-color-type> | inherit
'color' // the value of the 'color' property
no
visual, lithron





'border-top-style' 'border-right-style' 'border-bottom-style' 'border-left-style'
<border-style> | inherit
none
no
visual, lithron





'border-top-width' 'border-right-width' 'border-bottom-width' 'border-left-width'
<border-width> | inherit
medium
no
visual, lithron





'border-width'
<border-width>{1,4}@inflate("border-%-width") | inherit

no
visual, lithron





'border'
[ <border-width>@inflate("border-%-width") || <border-style>@inflate("border-%-style") || <border-color-type>@inflate("border-%-color") ] | inherit
// see individual properties
no
visual, lithron





'bottom'
<length> | <percentage> | auto | inherit
auto
no
visual, lithron





'caption-side'
top | bottom | inherit
top
yes
visual





'clear'
none | left | right | both | inherit
none
no
visual, lithron





'clip'
<shape> | auto | inherit
auto
no
visual, lithron





'color'
<color> | inherit
black // depends on user agent
yes
visual, lithron





'content'
normal | none | [ <string> | <uri> | <counter> | attr(<identifier>) | open-quote | close-quote | no-open-quote | no-close-quote ]+ | inherit
normal
no
all, lithron





'counter-increment'
[ <identifier> <integer>? ]+ | none | inherit
none
no
all, lithron





'counter-reset'
[ <identifier> <integer>? ]+ | none | inherit
none
no
all, lithron





'cue-after'
<uri> | none | inherit
none
no
aural





'cue-before'
<uri> | none | inherit
none
no
aural





'cue'
[ 'cue-before' || 'cue-after' ] | inherit
// see individual properties
no
aural





'cursor'
[[<uri> ,]* [ auto | crosshair | default | pointer | move | e-resize | ne-resize | nw-resize | n-resize | se-resize | sw-resize | s-resize | w-resize | text | wait | help | progress ] ] | inherit
auto
yes
visual, interactive





'direction'
ltr | rtl | inherit
ltr
yes
visual





'display'
inline | block | list-item | run-in | inline-block | table | inline-table | table-row-group | table-header-group | table-footer-group | table-row | table-column-group | table-column | table-cell | table-caption | none | inherit
inline
no
all





'display'
root | file | page | inline | block | list-item | inline-block | none | inherit
inline
no
lithron





'elevation'
<angle> | below | level | above | higher | lower | inherit
level
yes
aural





'empty-cells'
show | hide | inherit
show
yes
visual





'float'
left | right | none | inherit
none
no
visual, lithron





'font-family'
[[ <family-name> | <generic-family> ] [, [<family-name>| <generic-family>]]* ] | inherit
serif // depends on user agent
yes
visual





'font-family'
[[ <family-name> | <generic-family> ] [, [<family-name>| <generic-family>]]* ] | inherit
times // depends on user agent
yes
lithron





'font-size'
<absolute-size> | <relative-size> | <length> | <percentage> | inherit
medium
yes
visual, lithron





'font-style'
normal | italic | oblique | inherit
normal
yes
visual, lithron





'font-variant'
normal | small-caps | inherit
normal
yes
visual, lithron





'font-weight'
<integer> | normal | bold | bolder | lighter | inherit
normal
yes
visual, lithron





'font'
[ [ 'font-style'@shorthand() || 'font-variant'@shorthand() || 'font-weight'@shorthand() ]? 'font-size'@shorthand() [ / 'line-height'@shorthand() ]? 'font-family'@shorthand() ] | caption | icon | menu | message-box | small-caption | status-bar | inherit
// see individual properties
no
visual, lithron





'height'
<length> | <percentage> | auto | inherit
auto
no
visual, lithron





'image-fitmethod'
nofit | meet | clip | slice | entire | inherit
meet
no
visual, lithron





'image-page'
<integer> | inherit
1
no
visual, lithron





'image-position'
[ [ <number> | left | center | right ] [ <number> | top | center | bottom ] ] | [ [ left | center | right ] || [ top | center | bottom ] ] | inherit
left bottom
no
visual, lithron





'image-scale'
<number> | <percentage> | inherit
1
no
visual, lithron





'left'
<length> | <percentage> | auto | inherit
auto
no
visual, lithron





'letter-spacing'
normal | <length> | inherit
normal
yes
visual, lithron





'line-height'
normal | <number> | <length> | <percentage> | inherit
normal
yes
visual, lithron





'list-style-image'
<uri> | none | inherit
none
yes
visual, lithron





'list-style-position'
inside | outside | inherit
outside
yes
visual, lithron





'list-style-type'
disc | circle | square | decimal | decimal-leading-zero | lower-roman | upper-roman | lower-greek | lower-latin | upper-latin | armenian | georgian | lower-alpha | upper-alpha | none | inherit
disc
yes
visual, lithron





'list-style'
[ 'list-style-type'@shorthand() || 'list-style-position'@shorthand() || 'list-style-image'@shorthand() ] | inherit
// see individual properties
yes
visual, lithron





'margin-right' 'margin-left'
<margin-width> | inherit
0
no
visual, lithron





'margin-top' 'margin-bottom'
<margin-width> | inherit
0
no
visual, lithron





'margin'
<margin-width>{1,4}@inflate("margin-%") | inherit
// see individual properties
no
visual, lithron





'max-height'
<length> | <percentage> | none | inherit
none
no
visual, lithron





'max-width'
<length> | <percentage> | none | inherit
none
no
visual, lithron





'min-height'
<length> | <percentage> | inherit
0
no
visual, lithron





'min-width'
<length> | <percentage> | inherit
0
no
visual, lithron





'orphans'
<integer> | inherit
2
yes
visual, paged





'outline-color'
<color> | invert | inherit
invert
no
visual, interactive





'outline-style'
<border-style> | inherit
none
no
visual, interactive





'outline-width'
<border-width> | inherit
medium
no
visual, interactive





'outline'
[ 'outline-color'@shorthand() || 'outline-style'@shorthand() || 'outline-width'@shorthand() ] | inherit
// see individual properties
no
visual, interactive





'overflow'
visible | hidden | scroll | auto | inherit
visible
no
visual





'padding-top' 'padding-right' 'padding-bottom' 'padding-left'
<padding-width> | inherit
0
no
visual, lithron





'padding'
<padding-width>{1,4}@inflate("padding-%") | inherit
// see individual properties
no
visual, lithron





'page-break-after'
auto | always | avoid | left | right | inherit
auto
no
visual, paged





'page-break-before'
auto | always | avoid | left | right | inherit
auto
no
visual, paged





'page-break-inside'
avoid | auto | inherit
auto
no
visual, paged





'pause-after'
<time> | <percentage> | inherit
0
no
aural





'pause-before'
<time> | <percentage> | inherit
0
no
aural





'pause'
[<time> | <percentage>]{1,2} | inherit
// see individual properties
no
aural





'pitch-range'
<number> | inherit
50
yes
aural





'pitch'
<frequency> | x-low | low | medium | high | x-high | inherit
medium
yes
aural





'play-during'
[ <uri> [ mix || repeat ]? ] | auto | none | inherit
auto
no
aural





'position'
static | relative | absolute | fixed | inherit
static
no
visual, lithron





'provider'
element | image | line-break
element
no
lithron





'quotes'
[<string> <string>]+ | none | inherit
// depends on user agent
yes
visual





'richness'
<number> | inherit
50
yes
aural





'right'
<length> | <percentage> | auto | inherit
auto
no
visual, lithron





'speak-header'
once | always | inherit
once
yes
aural





'speak-numeral'
digits | continuous | inherit
continuous
yes
aural





'speak-punctuation'
code | none | inherit
none
yes
aural





'speak'
normal | none | spell-out | inherit
normal
yes
aural





'speech-rate'
<number> | x-slow | slow | medium | fast | x-fast | faster | slower | inherit
medium
yes
aural





'stress'
<number> | inherit
50
yes
aural





'table-layout'
auto | fixed | inherit
auto
no
visual





'text-align'
left | right | center | justify | inherit
left // a nameless value that acts as 'left' if 'direction' is 'ltr', 'right' if 'direction' is 'rtl'
yes
visual, lithron





'text-decoration'
none | [ underline || overline || line-through ] | inherit
none
yes /* (see prose) */
visual, lithron





'text-indent'
<length> | <percentage> | inherit
0
yes
visual, lithron





'text-transform'
capitalize | uppercase | lowercase | none | inherit
none
yes
visual





'text-transform'
capitalize | uppercase | uppercase-sz | lowercase | none | inherit
none
yes
lithron





'top'
<length> | <percentage> | auto | inherit
auto
no
visual, lithron





'unicode-bidi'
normal | embed | bidi-override | inherit
normal
no
visual





'vertical-align'
baseline | sub | super | top | text-top | middle | bottom | text-bottom | <percentage> | <length> | inherit
baseline
no
visual, lithron





'visibility'
visible | hidden | collapse | inherit
visible
yes
visual, lithron





'voice-family'
[[ <specific-voice> | <generic-voice> ] [, [<specific-voice> | <generic-voice>]]* ] | inherit
// depends on user agent
yes
aural





'volume'
<number> | <percentage> | silent | x-soft | soft | medium | loud | x-loud | inherit
medium
yes
aural





'white-space'
normal | pre | nowrap | pre-wrap | pre-line | inherit
normal
yes
visual, lithron





'widows'
<integer> | inherit
2
yes
visual, paged





'width'
<length> | <percentage> | auto | inherit
auto
no
visual, lithron





'word-spacing'
normal | <length> | inherit
normal
yes
visual, lithron





'z-index'
auto | <integer> | inherit
auto
no
visual, lithron





