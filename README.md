# Command-Line Tools

[Skip to installation instructions](#installation).

This library comprises just a few basic classes that make it easier to write command-line PHP scripts.  There are helpers for running external programs, and methods for sending formatted messages to the output.  Use this library when the might of something like [Symfony Console](https://symfony.com/doc/current/components/console.html) would be like taking a sledgehammer to a thumbtack.

The main component classes are [`Output`](#output), [`MessageFormatter`](#messageformatter), and [`Host`](#host).

## `Output`

Displays messages on the screen; it provides methods that output messages in different styles.

For example:

```php
(new Output())->writeLine('A plain-looking message followed by a newline.');

(new Output())->danger('A brightly-coloured message about something that just went wrong, followed by a newline.');
```

Run `php tests/show_output_formats.php` to see what `Output` can do.

## `MessageFormatter`

Provides a fluent interface for creating formatted messages.  **It uses a CSS-like approach, using CSS naming conventions whenever possible, to make the job easier, more intuitive, for web developers.**

For example, both of the following calls will create the same bold white message on a bright red background.

```php
$formattedMessage = (new MessageFormatter())
    ->fontWeight('bold')
    ->backgroundColor('maroon')
    ->format('A brightly-coloured message about something that just went wrong')
;

$formattedMessage = MessageFormatter::createFromStyleDeclarations([
    'font-weight' => 'bold',
    'background-color' => 'maroon',
])->format('A brightly-coloured message about something that just went wrong');
```

> When creating a Formatter from an array of style declarations, you can use either CSS-style kebab-case names or camel-case names.

> `MessageFormatter` will automatically use a contrasting colour for the foreground if only the background colour is set.

See the [section on the available styles](#style-reference), below, to see what you can do.

### Style Reference

| Property | Formatter Method | Valid Values |
| -------- | ---------------- | ------------ |
| `visibility`[^1]   |   `visibility`   |   `hidden\|visible`   |
| `font-weight`   |   `fontWeight`   |   `normal\|bold\|thin`[^2]   |
| `font-style`   |   `fontStyle`   |   `normal\|italic`[^1]   |
| `text-decoration`   |   `textDecoration`   |   `none\|underline`   |
| `color`[^3]   |   `color`   |   `initial\|teal\|aqua\|black\|gray\|navy\|blue\|purple\|fuchsia\|green\|lime\|maroon\|red\|olive\|yellow\|silver\|white`   |
| `background-color`[^3]   |   `backgroundColor`   |   `initial\|teal\|aqua\|black\|gray\|navy\|blue\|purple\|fuchsia\|green\|lime\|maroon\|red\|olive\|yellow\|silver\|white`   |

[^1]: Not widely supported by terminals.
[^2]: Non-standard name.
[^3]: Responds to CSS Level-1 named colours.

Run `php tests/show_styles.php` to see the effect of each of the style settings&mdash;and whether or not a style is supported by your terminal.

## `Host`

Provides a helpful `passthru()` wrapper that echoes the command it's executing in addition to its output, and, by default, throws an exception if something goes wrong.

For example:

```php
$output = new Output();
$host = new Host($output);

// Throws an exception if something goes wrong.
$host->passthru('ls -al --color=always');

// Will not throw an exception if something goes wrong: instead, will display a formatted error message and return a value indicating the problem that occurred.
$resultCode = $host->passthru('ls -al --color=always', false);
```

## Installation

No dependencies other than PHP 7+.

Install using Composer by running `composer require danbettles/command-line-tools`.

## References

- Fabien Loison (9 Aug 2018) *Bash tips: Colors and formatting (ANSI/VT100 Control sequences)* https://misc.flogisoft.com/bash/tip_colors_and_formatting  Accessed 16 Nov 2022
- Wikipedia (9 Nov 2022) *ANSI escape code* https://en.wikipedia.org/wiki/ANSI_escape_code#3-bit_and_4-bit  Accessed 16 Nov 2022
- Chris Maunder (7 Apr 2022) *How to change text color in a Linux terminal* CodeProject.  https://www.codeproject.com/Articles/5329247/How-to-change-text-color-in-a-Linux-terminal  Accessed 16 Nov 2022
- MDN Web Docs (27 Sep 2022) *font-weight* Mozilla.  https://developer.mozilla.org/en-US/docs/Web/CSS/font-weight#common_weight_name_mapping  Accessed 16 Nov 2022
- MDN Web Docs (5 Oct 2022) *\<named-color\>* Mozilla.  https://developer.mozilla.org/en-US/docs/Web/CSS/named-color#value  Accessed 16 Nov 2022
