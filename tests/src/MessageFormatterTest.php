<?php

declare(strict_types=1);

namespace DanBettles\CommandLineTools\Tests;

use DanBettles\CommandLineTools\MessageFormatter;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

use const false;
use const null;
use const true;

class MessageFormatterTest extends TestCase
{
    public function testFormatFormatsAMessage(): void
    {
        $formatter = new MessageFormatter();

        $this->assertSame('', $formatter->format(''));
        $this->assertSame('Hello, World!', $formatter->format('Hello, World!'));
    }

    public function testFontweightSetsTheFontWeightOfTheMessage(): void
    {
        $formatter = new MessageFormatter();

        $something = $formatter->fontWeight('normal');
        $this->assertSame("\033[21;22mFOO\033[0m", $formatter->format('FOO'));
        $this->assertSame($formatter, $something);

        // Overwrites any previous setting.
        $formatter->fontWeight('bold');
        $this->assertSame("\033[1mFOO\033[0m", $formatter->format('FOO'));

        // Clears the previous setting.
        $formatter->fontWeight(null);
        $this->assertSame('FOO', $formatter->format('FOO'));
    }

    public function testFontweightThrowsAnExceptionIfTheWeightIsInvalid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("The font weight, `bolder`, does not exist.");

        (new MessageFormatter())->fontWeight('bolder');
    }

    public function testFontstyleSetsTheFontStyleOfTheMessage(): void
    {
        $formatter = new MessageFormatter();

        $something = $formatter->fontStyle('normal');
        $this->assertSame("\033[23mFOO\033[0m", $formatter->format('FOO'));
        $this->assertSame($formatter, $something);

        // Overwrites any previous setting.
        $formatter->fontStyle('italic');
        $this->assertSame("\033[3mFOO\033[0m", $formatter->format('FOO'));

        // Clears the previous setting.
        $formatter->fontStyle(null);
        $this->assertSame('FOO', $formatter->format('FOO'));
    }

    public function testFontstyleThrowsAnExceptionIfTheStyleIsInvalid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("The font style, `oblique`, does not exist.");

        (new MessageFormatter())->fontStyle('oblique');
    }

    public function testColorSetsTheForegroundColourOfTheMessage(): void
    {
        $formatter = new MessageFormatter();

        $something = $formatter->color('yellow');
        $this->assertSame("\033[93mFOO\033[0m", $formatter->format('FOO'));
        $this->assertSame($formatter, $something);

        // Overwrites any previous setting.
        $formatter->color('white');
        $this->assertSame("\033[97mFOO\033[0m", $formatter->format('FOO'));

        // Clears the previous setting.
        $formatter->color(null);
        $this->assertSame('FOO', $formatter->format('FOO'));
    }

    public function testColorThrowsAnExceptionIfTheColourIsInvalid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("The color, `rainbow`, does not exist.");

        (new MessageFormatter())->color('rainbow');
    }

    public function testBackgroundcolorSetsTheBackgroundColourOfTheMessage(): void
    {
        $formatter = new MessageFormatter();

        $something = $formatter->backgroundColor('yellow');
        $this->assertSame("\033[30;103mFOO\033[0m", $formatter->format('FOO'));
        $this->assertSame($formatter, $something);

        // Overwrites any previous setting.
        $formatter->backgroundColor('white');
        $this->assertSame("\033[30;107mFOO\033[0m", $formatter->format('FOO'));

        // Clears the previous setting.
        $formatter->backgroundColor(null);
        $this->assertSame('FOO', $formatter->format('FOO'));
    }

    public function testBackgroundcolorThrowsAnExceptionIfTheColourIsInvalid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("The background color, `rainbow`, does not exist.");

        (new MessageFormatter())->backgroundColor('rainbow');
    }

    public function testBackgroundcolorAlsoSetsTheForegroundColourIfItHasNotAlreadyBeenSet(): void
    {
        $redOnBlue = (new MessageFormatter())
            ->color('red')
            ->backgroundColor('blue')
        ;

        // Both specified colours honoured.
        $this->assertSame("\033[91;104mFOO\033[0m", $redOnBlue->format('FOO'));

        $blackOnBrightCyan = (new MessageFormatter())
            ->backgroundColor('aqua')
        ;

        // Contrasting foreground colour applied automatically.
        $this->assertSame("\033[30;106mFOO\033[0m", $blackOnBrightCyan->format('FOO'));
    }

    public function testTextdecorationAllowsToUnderlineTheMessage(): void
    {
        $formatter = new MessageFormatter();

        $something = $formatter->textDecoration('underline');
        $this->assertSame("\033[4mFOO\033[0m", $formatter->format('FOO'));
        $this->assertSame($formatter, $something);

        // Overwrites any previous setting.
        $formatter->textDecoration('none');
        $this->assertSame("\033[24mFOO\033[0m", $formatter->format('FOO'));

        // Clears the previous setting.
        $formatter->textDecoration(null);
        $this->assertSame('FOO', $formatter->format('FOO'));
    }

    public function testTextdecorationThrowsAnExceptionIfTheValueIsInvalid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("The text decoration, `overline`, does not exist.");

        (new MessageFormatter())->textDecoration('overline');
    }

    public function testVisibilityAllowsToHideTheMessage(): void
    {
        $formatter = new MessageFormatter();

        $something = $formatter->visibility('hidden');
        $this->assertSame("\033[8mFOO\033[0m", $formatter->format('FOO'));
        $this->assertSame($formatter, $something);

        // Overwrites any previous setting.
        $formatter->visibility('visible');
        $this->assertSame("\033[28mFOO\033[0m", $formatter->format('FOO'));

        // Clears the previous setting.
        $formatter->visibility(null);
        $this->assertSame('FOO', $formatter->format('FOO'));
    }

    public function testVisibilityThrowsAnExceptionIfTheValueIsInvalid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("The visibility, `collapse`, does not exist.");

        (new MessageFormatter())->visibility('collapse');
    }

    public function testStylesCanBeCombined(): void
    {
        $formatter = (new MessageFormatter())
            ->visibility('hidden')
            ->fontWeight('bold')
            ->textDecoration('underline')
            ->color('white')
            ->backgroundColor('blue')
            ->color('yellow')
            ->fontWeight('normal')  // Switches-off all font-weight attributes.
            ->visibility(null)
        ;

        $this->assertSame("\033[93;21;22;4;104mFOO\033[0m", $formatter->format('FOO'));
    }

    /** @return array<mixed[]> */
    public function providesSupportedStyleProperties(): array
    {
        return [
            [
                true,
                'visibility',
            ],
            [
                true,
                'fontWeight',
            ],
            [
                true,
                'fontStyle',
            ],
            [
                true,
                'textDecoration',
            ],
            [
                true,
                'color',
            ],
            [
                true,
                'backgroundColor',
            ],
            [
                false,
                '',
            ],
            [
                false,
                'fontFamily',
            ],
        ];
    }

    /** @dataProvider providesSupportedStyleProperties */
    public function testSupportsstylepropertyReturnsTrueIfTheStylePropertyIsSupported(
        bool $expected,
        string $property
    ): void {
        $this->assertSame($expected, MessageFormatter::supportsStyleProperty($property));
    }

    /** @return array<mixed[]> */
    public function providesFormattedFromStyleDeclarations(): array
    {
        return [
            [
                "\033[93;8;1;3;4;104mFOO\033[0m",
                [
                    'visibility' => 'hidden',
                    'fontWeight' => 'bold',
                    'fontStyle' => 'italic',
                    'textDecoration' => 'underline',
                    'color' => 'yellow',
                    'backgroundColor' => 'blue',
                ],
                'FOO',
            ],
            [
                "\033[93;8;1;3;4;104mFOO\033[0m",
                [
                    'visibility' => 'hidden',
                    'font-weight' => 'bold',
                    'font-style' => 'italic',
                    'text-decoration' => 'underline',
                    'color' => 'yellow',
                    'background-color' => 'blue',
                ],
                'FOO',
            ],
        ];
    }

    /**
     * @param array<string, string> $styleDeclarations
     * @dataProvider providesFormattedFromStyleDeclarations
     */
    public function testCreatefromstyledeclarationsCreatesAnInstanceFromAnArrayOfStyleDeclarations(
        string $expected,
        array $styleDeclarations,
        string $message
    ): void {
        $formatter = MessageFormatter::createFromStyleDeclarations($styleDeclarations);

        $this->assertSame($expected, $formatter->format($message));
    }

    /** @return array<mixed[]> */
    public function providesInvalidStyleDeclarations(): array
    {
        return [
            [
                '',
                [
                    '' => 'initial',
                ],
            ],
            [
                'font-family',
                [
                    'font-family' => 'comic sans',
                ],
            ],
        ];
    }

    /**
     * @param array<string, string> $styleDeclarations
     * @dataProvider providesInvalidStyleDeclarations
     */
    public function testCreatefromstyledeclarationsThrowsAnExceptionIfAPropertyIsNotSupported(
        string $invalidProperty,
        array $styleDeclarations
    ): void {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("The style property, `{$invalidProperty}`, is not supported.");

        MessageFormatter::createFromStyleDeclarations($styleDeclarations);
    }
}
