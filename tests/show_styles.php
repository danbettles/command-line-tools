<?php
// phpcs:ignoreFile

use DanBettles\CommandLineTools\MessageFormatter;
use DanBettles\CommandLineTools\Output;

require __DIR__ . '/../vendor/autoload.php';

/**
 * @param mixed[] $array
 */
function maxKeyLen(array $array): int
{
    $lengthOfLongestKey = 0;

    foreach (array_keys($array) as $key) {
        $lengthOfKey = strlen($key);

        if ($lengthOfKey > $lengthOfLongestKey) {
            $lengthOfLongestKey = $lengthOfKey;
        }
    }

    return $lengthOfLongestKey;
}

/**
 * @param array<string, string> $propertyValueMap
 */
function showPropertyValues(
    Output $output,
    MessageFormatter $titleFormatter,
    string $property,
    array $propertyValueMap,
    string $overrideHeading = null
): void {
    $heading = null === $overrideHeading ? $property : $overrideHeading;
    $output->writeLine($titleFormatter->format("## {$heading}"));

    $lengthOfLongestValue = maxKeyLen($propertyValueMap) + 2;

    foreach (array_keys($propertyValueMap) as $propertyValue) {
        $paddedMessage = str_pad(" {$propertyValue}", $lengthOfLongestValue, ' ');

        $formattedMessage = MessageFormatter::createFromStyleDeclarations([
            $property => $propertyValue,
        ])->format($paddedMessage);

        $output->writeLine($formattedMessage);
    }

    $output->writeLine();
}

$output = new Output();

$titleFormatter = (new MessageFormatter())
    ->fontWeight('bold')
;

showPropertyValues($output, $titleFormatter, 'visibility', MessageFormatter::VISIBILITY_MAP);
showPropertyValues($output, $titleFormatter, 'font-weight', MessageFormatter::FONT_WEIGHT_MAP);
showPropertyValues($output, $titleFormatter, 'font-style', MessageFormatter::FONT_STYLE_MAP);
showPropertyValues($output, $titleFormatter, 'text-decoration', MessageFormatter::TEXT_DECORATION_MAP);
showPropertyValues($output, $titleFormatter, 'background-color', MessageFormatter::COLOUR_MAP, 'Colours');
