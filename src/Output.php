<?php

declare(strict_types=1);

namespace DanBettles\CommandLineTools;

class Output
{
    public function writeLine(string $message = ''): void
    {
        // phpcs:ignore
        echo "{$message}\n";
    }

    public function command(string $message): void
    {
        $formattedMessage = (new MessageFormatter())
            ->fontWeight('thin')
            ->color('white')
            ->format(">$ {$message}")
        ;

        $this->writeLine($formattedMessage);
    }

    public function danger(string $message): void
    {
        $formattedMessage = (new MessageFormatter())
            ->fontWeight('bold')
            ->backgroundColor('maroon')
            ->format("!! {$message}")
        ;

        $this->writeLine($formattedMessage);
    }

    public function info(string $message): void
    {
        $formattedMessage = (new MessageFormatter())
            ->backgroundColor('blue')
            ->format("[i] {$message}")
        ;

        $this->writeLine($formattedMessage);
    }
}
