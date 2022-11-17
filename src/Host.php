<?php

declare(strict_types=1);

namespace DanBettles\CommandLineTools;

use RuntimeException;

use function passthru;

use const false;
use const null;
use const true;

class Host
{
    private Output $output;

    public function __construct(Output $output)
    {
        $this->setOutput($output);
    }

    /**
     * A proxy for `passthru()`, to make testing easier.
     */
    protected function passthruProxy(string $command, int &$resultCode = null): ?bool
    {
        // PHPStan is wrong about this.  According to the PHP manual, `passthru()` "Returns null on success or false on
        // failure".
        /** @phpstan-ignore-next-line */
        return passthru($command, $resultCode);
    }

    /**
     * Does the same job as the PHP builtin but differs in how it reports status.  By default, this method will throw an
     * exception if something goes wrong.  If, on the other hand, `$throwOnError` is set to `false` then it displays a
     * formatted error message and returns a value indicating the problem that occurred.
     *
     * @return int|bool `false` if it failed to execute the command and `$throwOnError` === `false`.
     * @throws RuntimeException If it failed to execute the command and `$throwOnError` === `true`.
     * @throws RuntimeException If the command returned a non-zero exit status and `$throwOnError` === `true`.
     */
    public function passthru(string $command, bool $throwOnError = true)
    {
        $this->getOutput()->command($command);

        $commandResultCode = 1;
        $passthruReturnValue = $this->passthruProxy($command, $commandResultCode);

        $returnValue = 0;
        $errorMessage = null;

        if (false === $passthruReturnValue) {
            $returnValue = false;
            $errorMessage = 'PHP failed to execute the command.';
        } elseif (0 !== $commandResultCode) {
            $returnValue = $commandResultCode;
            $errorMessage = "The command returned a non-zero, `{$commandResultCode}`, exit status.";
        }

        if (0 !== $returnValue) {
            if ($throwOnError) {
                throw new RuntimeException($errorMessage);
            }

            $this->getOutput()->danger($errorMessage);
        }

        return $returnValue;
    }

    private function setOutput(Output $output): self
    {
        $this->output = $output;

        return $this;
    }

    public function getOutput(): Output
    {
        return $this->output;
    }
}
