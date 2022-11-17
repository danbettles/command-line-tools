<?php

declare(strict_types=1);

namespace DanBettles\CommandLineTools\Tests;

use DanBettles\CommandLineTools\Output;
use PHPUnit\Framework\TestCase;

class OutputTest extends TestCase
{
    /** @return array<mixed[]> */
    public function providesLines(): array
    {
        return [
            [
                "Hello, World!\n",
                'Hello, World!',
            ],
            [
                "\n",
                '',
            ],
        ];
    }

    /** @dataProvider providesLines */
    public function testWritelineWritesALine(string $expectedOutput, string $message): void
    {
        $this->expectOutputString($expectedOutput);

        (new Output())->writeLine($message);
    }

    public function testCommandFormatsTheMessageAsACommand(): void
    {
        $this->expectOutputString("\033[2;97m>$ ls -al --color=always\033[0m\n");

        (new Output())->command('ls -al --color=always');
    }

    public function testErrorFormatsTheMessageAsAnError(): void
    {
        $this->expectOutputString("\033[97;1;41m!! Something went wrong\033[0m\n");

        (new Output())->danger('Something went wrong');
    }

    public function testInfoFormatsTheMessageAsInfo(): void
    {
        $this->expectOutputString("\033[97;104m[i] Information about something\033[0m\n");

        (new Output())->info('Information about something');
    }
}
