## PHP Rule Engine (Parser and Evaluator for PHP 7)

|  | Build Status | Code Quality | Coverage | Style CI |
|:----------------:|:----------------:|:----------:|:----------:|:----------:|
| [Master][Master] | [![Build status][Master image]][Master] | [![Code Quality][Master quality image]][Master quality] | [![Build status][Master coverage image]][Master coverage] | [![StyleCI](https://styleci.io/repos/39503126/shield?branch=master)](https://styleci.io/repos/39503126)

[![Latest Stable Version](https://img.shields.io/packagist/v/nicoswd/php-rule-parser.svg)](https://packagist.org/packages/nicoswd/php-rule-parser)

If you're using PHP 5, you might want to look at [version 0.4.0](https://github.com/nicoSWD/php-rule-parser/tree/0.4.0).

You're looking at a PHP library to parse and evaluate text based rules with a Javascript-like syntax. This project was born out of the necessity to evaluate hundreds of rules that were originally written and evaluated in JavaScript, and now needed to be evaluated on the server-side, using PHP.

This library has initially been used to change and configure the behavior of certain "Workflows" (without changing actual code) in an intranet application, but it may serve a purpose elsewhere.


Find me on Twitter: @[nicoSWD](https://twitter.com/nicoSWD)

[![SensioLabsInsight](https://insight.sensiolabs.com/projects/67203389-970c-419c-9430-a7f9a005bd94/big.png)](https://insight.sensiolabs.com/projects/67203389-970c-419c-9430-a7f9a005bd94)

## Install

Via Composer

```bash
$ composer require nicoswd/php-rule-parser
```

## Usage

```php
use nicoSWD\Rules\Rule;

// Composer install
require '/path/to/vendor/autoload.php';

$ruleStr = '
    2 < 3 && (
        // False
        foo in [4, 6, 7] ||
        // True
        [1, 4, 3].join("") === "143" &&
        // True
        "bar" in "foo bar".split(" ") &&
        // True
        "foo bar".substr(4) === "bar"
    ) && (
        // True
        "foo|bar|baz".split("|") === ["foo", /* what */ "bar", "baz"] &&
        // True
        bar > 6
    )';

$variables = [
    'foo' => 'abc',
    'bar' => 321,
    'baz' => 123
];

$rule = new Rule($ruleStr, $variables);

var_dump($rule->isTrue()); // bool(true)
```


## Custom Functions

```php
$ruleStr = 'double(value) === result';

$variables = [
    'value'  => 2,
    'result' => 4
];

$rule = new Rule($ruleStr, $variables);

$rule->registerFunction('double', function (BaseToken $multiplier) : BaseToken {
    if (!$multiplier instanceof TokenInteger) {
        throw new \Exception;
    }

    return new TokenInteger($multiplier->getValue() * 2);
});

var_dump($rule->isTrue()); // bool(true)
```

## Redefine Tokens
Tokens can be customized, if desired. Note that it's very easy to break stuff by doing that, if you have colliding regular expressions.
You may want to set a different `$priority` when registering a token: `::registerToken($type, $regex, $priority)`
(take a look at `Tokenizer::__construct()` for more info).

```php
$ruleStr = ':this is greater than :that';

$variables = [
    ':this' => 8,
    ':that' => 7
];

$rule = new Rule($ruleStr, $variables);

$rule->registerToken(Tokenizer::TOKEN_GREATER, '\bis\s+greater\s+than\b');
$rule->registerToken(Tokenizer::TOKEN_VARIABLE, ':[a-zA-Z_][\w-]*');

var_dump($rule->isTrue()); // bool(true)
```

Also note that the original tokens will no longer be recognized after overwriting them. Thus, if you want to implement aliases
for custom tokens, you have to group them into one regular expression: `(?:\b(?:is\s+)?greater\s+than\b|>)`

## Error Handling
Both, `$rule->isTrue()` and `$rule->isFalse()` will throw an exception if the syntax is invalid. These calls can either be placed inside a `try` / `catch` block, or it can be checked prior using `$rule->isValid()`.

```php
$ruleStr = '
    (2 == 2) && (
        1 < 3 && 3 == 2 ( // Missing and/or before parentheses
            1 == 1
        )
    )';

$rule = new Rule($ruleStr);

try {
    $rule->isTrue();
} catch (\Exception $e) {
    echo $e->getMessage();
}
```

Or alternatively:

```php
if (!$rule->isValid()) {
    echo $rule->getError();
}
```

Both will output: `Unexpected token "(" at position 25 on line 3`

## Syntax Highlighting
 
A custom syntax highlighter is also provided.

```php
use nicoSWD\Rules;

$ruleStr = '
    // This is true
    2 < 3 && (
        // This is false
        foo in [4, 6, 7] ||
        // True
        [1, 4, 3].join("") === "143"
    ) && (
        // True
        "foo|bar|baz".split("|" /* uh oh */) === ["foo", /* what */ "bar", "baz"] &&
        // True
        bar > 6
    )';

$highlighter = new Rules\Highlighter\Highlighter(new Rules\Tokenizer());

// Optional custom styles
$highlighter->setStyle(
    Rules\Constants::GROUP_VARIABLE,
    'color: #007694; font-weight: 900;'
);

echo $highlighter->highlightString($ruleStr);
```

Outputs:

![Syntax preview](https://s3.amazonaws.com/f.cl.ly/items/0y1b0s0J2v2v1u3O1F3M/Screen%20Shot%202015-08-05%20at%2012.15.21.png)

## Supported Operators
- Equal: `==`, `===` (strict)
- Not equal: `!=`, `!==` (strict)
- Greater than: `>`
- Less than: `<`
- Greater than/equal: `>=`
- Less than/equal: `<=`
- In: `in`

## Notes
- Parentheses can be nested, and will be evaluated from right to left.
- Only value/variable comparison expressions with optional logical ANDs/ORs, are supported. This is not a full JavaScript emulator.

## Security

If you discover any security related issues, please email security@nic0.me instead of using the issue tracker.

## Testing

```bash
$ composer test
```

## Contributing
Pull requests are very welcome! If they include tests, even better. This project follows PSR-2 coding standards, please make sure your pull requests do too.

## To Do
- Support for object properties (foo.length)
- Support for returning actual results, other than true or false
- Support for array / string dereferencing: "foo"[1]
- Change regex and implementation for method calls. ".split(" should not be the token
- Add / implement missing methods
- Add "typeof" construct
- Do math (?)
- Allow string concatenating with "+"
- Support for objects {} (?)
- Invalid regex modifiers should not result in an unknown token
- Duplicate regex modifiers should throw an error
- ~~Add support for function calls~~
- ~~Support for regular expressions~~
- ~~Fix build on PHP 7 / Nightly~~
- ~~Allow variables in arrays~~
- ~~Verify function and method name spelling (.tOuPpErCAse() is currently valid)~~
- ...

## License

[![License](https://img.shields.io/packagist/l/nicoSWD/php-rule-parser.svg)](https://packagist.org/packages/nicoswd/php-rules-parser)

  [Master image]: https://travis-ci.org/nicoSWD/php-rule-parser.svg?branch=master
  [Master]: https://github.com/nicoSWD/php-rule-parser/tree/master
  [Master coverage image]: https://scrutinizer-ci.com/g/nicoSWD/php-rule-parser/badges/coverage.png?b=master
  [Master coverage]: https://scrutinizer-ci.com/g/nicoSWD/php-rule-parser/?branch=master
  [Master quality image]: https://img.shields.io/scrutinizer/g/nicoswd/php-rule-parser.svg?b=master
  [Master quality]: https://scrutinizer-ci.com/g/nicoSWD/php-rule-parser/?branch=master
  [0.3 image]: https://travis-ci.org/nicoSWD/php-rule-parser.svg?branch=v0.3
  [0.3]: https://github.com/nicoSWD/php-rule-parser/tree/v0.3
  [0.3 coverage image]: https://scrutinizer-ci.com/g/nicoSWD/php-rule-parser/badges/coverage.png?b=v0.3
  [0.3 coverage]: https://scrutinizer-ci.com/g/nicoSWD/php-rule-parser/?branch=v0.3
  [0.3 quality image]: https://img.shields.io/scrutinizer/g/nicoswd/php-rule-parser.svg?b=v0.3
  [0.3 quality]: https://scrutinizer-ci.com/g/nicoSWD/php-rule-parser/?branch=v0.3
  [Develop image]: https://travis-ci.org/nicoSWD/php-rule-parser.svg?branch=develop
  [Develop]: https://github.com/nicoSWD/php-rule-parser/tree/develop
  [Develop coverage image]: https://scrutinizer-ci.com/g/nicoSWD/php-rule-parser/badges/coverage.png?b=develop
  [Develop coverage]: https://scrutinizer-ci.com/g/nicoSWD/php-rule-parser/?branch=develop
  [Develop quality image]: https://img.shields.io/scrutinizer/g/nicoswd/php-rule-parser.svg?b=develop
  [Develop quality]: https://scrutinizer-ci.com/g/nicoSWD/php-rule-parser/?branch=develop
