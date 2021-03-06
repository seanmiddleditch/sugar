Version History
===============

Version 0.84
------------

+ Simplification of compiler parsing rules (no significant breaks).
+ Modifiers can now be used in more places.
+ Functions can only be called in top-level expressions or inside
  parenthesized expressions.

Version 0.83
------------

+ Include PEAR package.xml file.
+ Added uncache() and clearCache() methods.
+ Renamed documentation tool to sugardoc.
+ Renamed most classes to follow the Sugar_Foo naming convention.
+ Added experimental Memcached-based cache driver.
+ Changed the SugarUtil methods to functions.
+ Renamed the method_acl member to methodAcl for consistency.
+ Added getOption() and setOption() methods.
+ Removed layouts in favor of inherited templates.
+ pluginDir and templateDir may be arrays of paths.
+ section, insert, inherit all look like built-in functions.

Version 0.82
------------

+ Registered functions can have escaping disabled by default.
+ Sections.
+ Layouts.

Version 0.81
------------

+ Improved documentation.
+ Added modifiers.
+ Converted several stdlib functions to modifiers.
+ Renamed to Sugar for PHP License compatibility.
+ Support Smarty-style block terminators (e.g. /if).
+ Method access control via $smarty->method_acl property.

Version 0.80
------------

+ Added the ability to set functions as non-cachable back.
+ Added ability to change code delimiters.
+ Changed default delimiters to {% and %}.
+ Function calls always used named parameters (method calls never do).
+ Function calls do not use commas between parameters (method calls do).
+ Function calls do not use parenthesis around parameters (method calls do).
+ Added auto-lookup of functions.
+ Added plugin support for functions.
+ Cleaned up the stdlib naming slightly.

Version 0.74
------------

+ Fix fetch*() family of functions.
+ More E_STRICT|E_ALL errors fixed.
+ Added isset, printf, join, split, pspli, and merge template functions.
+ Made the -> operator an alias of the . operator.
+ Added inline documentation for stdlib template functions.
+ Removed function flags.
+ Added gen-doc.php documentation generator.

Version 0.73
------------

+ Fix isCached() in debug mode.
+ E_STRICT|E_ALL error fixed.
+ Fixed templates.

Version 0.72
------------

+ Caching bug fixes.
+ New functions.
+ Include file timestamp checking for HTML caches.

Version 0.71
------------

+ Removed the automatic conversion of names to strings
+ Massive cleanups to compiler
+ Added a character set option for escaping
+ Fixed bugs in jsValue encoding with strings
+ Decode \n sequences in input strings
+ Changed the array constructor to a function call to array()
+ Added a [] array subscript operator
+ Changed license to the equivalent MIT license
+ Complete API documentation in phpDocumentor format
+ Methods for retrieving template output

Version 0.70
------------

+ Modified loop syntax
+ Added a while loop
+ Added nocache block directive
+ Revamped internal API
+ Added many stdlib functions
+ Added array constructor
+ Added PHP-style commenting syntax
