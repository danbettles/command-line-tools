<?php

declare(strict_types=1);

namespace DanBettles\CommandLineTools;

use BadMethodCallException;
use InvalidArgumentException;

use function array_filter;
use function array_key_exists;
use function array_replace;
use function count;
use function implode;
use function is_string;
use function lcfirst;
use function preg_replace;
use function reset;
use function strpos;
use function strtolower;
use function str_replace;
use function ucwords;

use const false;
use const null;

/**
 * Fluent interface for creating formatted messages.  Uses CSS names for ease of use.
 *
 * @method self visibility(?string $value)
 * @method self fontWeight(?string $value)
 * @method self fontStyle(?string $value)
 * @method self textDecoration(?string $value)
 * @method self color(?string $value)
 * @method self backgroundColor(?string $value)
 */
class MessageFormatter
{
    /**
     * Control Sequence Introducer.  Think "prefix".
     *
     * @var string
     */
    private const CSI = "\033[";

    /**
     * Select Graphic Rendition terminating character.  Think "suffix".
     *
     * @var string
     */
    private const SGR_TERMINATOR = 'm';

    /**
     * Select Graphic Rendition reset sequence.
     *
     * @var string
     */
    private const SEQ_SGR_RESET = self::CSI . '0' . self::SGR_TERMINATOR;

    /**
     * { 0: <foreground-colour>, 1: <background-colour>, 2: <contrasting-colour> }
     *
     * Names of colours from https://en.wikipedia.org/wiki/ANSI_escape_code#Colors
     *
     * @var array<string, array{0: string, 1: string, 2: string}>
     */
    private const ANSI_COLOURS = [
        'black' => ['30', '40', 'brightwhite'],
        'blue' => ['34', '44', 'brightwhite'],
        'brightblack' => ['90', '100', 'brightwhite'],
        'brightblue' => ['94', '104', 'brightwhite'],
        'brightcyan' => ['96', '106', 'black'],
        'brightgreen' => ['92', '102', 'black'],
        'brightmagenta' => ['95', '105', 'brightwhite'],
        'brightred' => ['91', '101', 'brightwhite'],
        'brightwhite' => ['97', '107', 'black'],
        'brightyellow' => ['93', '103', 'black'],
        'cyan' => ['36', '46', 'black'],
        'default' => ['39', '49', 'default'],
        'green' => ['32', '42', 'black'],
        'magenta' => ['35', '45', 'brightwhite'],
        'red' => ['31', '41', 'brightwhite'],
        'white' => ['37', '47', 'black'],
        'yellow' => ['33', '43', 'black'],
    ];

    /**
     * Not widely supported.
     *
     * @var array<string, string>
     */
    public const VISIBILITY_MAP = [
        'hidden' => '8',
        'visible' => '28',
    ];

    /**
     * See https://developer.mozilla.org/en-US/docs/Web/CSS/font-weight#common_weight_name_mapping
     *
     * @var array<string, string>
     */
    public const FONT_WEIGHT_MAP = [
        'bold' => '1',
        // The concept of a single, font-weight-resetting 'normal' doesn't exist.  We need to ensure the other
        // font-weight attributes are switched-off: we need to "reset bold and reset thin".  Not `0` because that resets
        // *all* attributes.
        'normal' => '21;22',
        'thin' => '2',
    ];

    /**
     * @var array<string, string>
     */
    public const FONT_STYLE_MAP = [
        'normal' => '23',
        'italic' => '3',
    ];

    /**
     * @var array<string, string>
     */
    public const TEXT_DECORATION_MAP = [
        'none' => '24',
        'underline' => '4',
    ];

    /**
     * Maps CSS Level-1 named-colours to the ANSI colours.
     *
     * See https://developer.mozilla.org/en-US/docs/Web/CSS/named-color#value
     *
     * @var array<string, string>
     */
    public const COLOUR_MAP = [
        'initial' => 'default',
        'teal' => 'cyan',
        'aqua' => 'brightcyan',
        'black' => 'black',
        'gray' => 'brightblack',
        'navy' => 'blue',
        'blue' => 'brightblue',
        'purple' => 'magenta',
        'fuchsia' => 'brightmagenta',
        'green' => 'green',
        'lime' => 'brightgreen',
        'maroon' => 'red',
        'red' => 'brightred',
        'olive' => 'yellow',
        'yellow' => 'brightyellow',
        'silver' => 'white',
        'white' => 'brightwhite',
    ];

    private const STYLE_PROPERTIES = [
        'visibility' => self::VISIBILITY_MAP,
        'fontWeight' => self::FONT_WEIGHT_MAP,
        'fontStyle' => self::FONT_STYLE_MAP,
        'textDecoration' => self::TEXT_DECORATION_MAP,
        'color' => self::COLOUR_MAP,
        'backgroundColor' => self::COLOUR_MAP,
    ];

    /**
     * @var array<string, string|null>
     */
    private array $styleDeclarations = [];

    /**
     * @param mixed[] $arguments
     * @throws BadMethodCallException If the style setter-method does not exist.
     * @throws BadMethodCallException If a single property value was not specified.
     * @throws InvalidArgumentException If the type of the property value is not a string or `null`.
     * @throws InvalidArgumentException If the property value does not exist.
     */
    public function __call(string $methodName, array $arguments): self
    {
        $styleProperty = $methodName;

        if (!self::supportsStyleProperty($styleProperty)) {
            throw new BadMethodCallException("The style setter-method, `{$styleProperty}()`, does not exist.");
        }

        if (1 !== count($arguments)) {
            throw new BadMethodCallException('A single property value was not specified.');
        }

        $value = reset($arguments);
        $valueMap = self::STYLE_PROPERTIES[$styleProperty];

        $translatedValue = null;

        if (null !== $value) {
            if (!is_string($value)) {
                throw new InvalidArgumentException('The type of the property value is not a string or `null`.');
            }

            if (!array_key_exists($value, $valueMap)) {
                $stylePropertyHumanized = self::humanize($styleProperty);

                throw new InvalidArgumentException("The {$stylePropertyHumanized}, `{$value}`, does not exist.");
            }

            $translatedValue = $valueMap[$value];
        }

        $this->styleDeclarations[$styleProperty] = $translatedValue;

        return $this;
    }

    /**
     * Formats the message.
     */
    public function format(string $message): string
    {
        if ('' === $message) {
            return '';
        }

        $filteredStyleDeclarations = array_filter($this->styleDeclarations, 'is_string');

        if (empty($filteredStyleDeclarations)) {
            return $message;
        }

        $defaultStyleDeclarations = [];

        if (array_key_exists('backgroundColor', $filteredStyleDeclarations)) {
            $backgroundColor = $filteredStyleDeclarations['backgroundColor'];
            $defaultStyleDeclarations['color'] = self::ANSI_COLOURS[$backgroundColor][2];
        }

        $styleDeclarations = array_replace($defaultStyleDeclarations, $filteredStyleDeclarations);

        $attributes = [];

        foreach ($styleDeclarations as $property => $value) {
            switch ($property) {
                case 'color':
                    $attributes[] = self::ANSI_COLOURS[$value][0];
                    break;

                case 'backgroundColor':
                    $attributes[] = self::ANSI_COLOURS[$value][1];
                    break;

                default:
                    $attributes[] = $value;
            }
        }

        $startEscSeq = self::CSI . implode(';', $attributes) . self::SGR_TERMINATOR;

        return $startEscSeq . $message . self::SEQ_SGR_RESET;
    }

    public static function supportsStyleProperty(string $name): bool
    {
        return array_key_exists($name, self::STYLE_PROPERTIES);
    }

    /**
     * Converts a name in kebab case (e.g. "font-weight") into lower camel-case (e.g. "fontWeight").
     */
    private static function camelize(string $kebabized): string
    {
        $titleized = ucwords(strtolower(str_replace('-', ' ', $kebabized)));

        return lcfirst(str_replace(' ', '', $titleized));
    }

    /**
     * Converts a name in lower camel-case (e.g. "fontWeight") into 'human case' (e.g. "font weight").
     */
    private static function humanize(string $camelized): string
    {
        /** @var string */
        $spaced = preg_replace('~([A-Z])~', ' $1', $camelized);

        return strtolower($spaced);
    }

    /**
     * Creates a new instance from an array of style declarations.
     *
     * Properties can be written in lower camel-case or in lower kebab-case -- as in a CSS stylesheet.
     *
     * @param array<string, string|null> $declarations
     * @throws InvalidArgumentException If the style property is not supported.
     */
    public static function createFromStyleDeclarations(array $declarations): self
    {
        $formatter = new self();

        foreach ($declarations as $property => $value) {
            $originalProperty = $property;

            if (false !== strpos($property, '-')) {
                $property = self::camelize($property);
            }

            if (!self::supportsStyleProperty($property)) {
                throw new InvalidArgumentException("The style property, `{$originalProperty}`, is not supported.");
            }

            $formatter->{$property}($value);
        }

        return $formatter;
    }
}
