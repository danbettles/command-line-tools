<?php

declare(strict_types=1);

namespace DanBettles\CommandLineTools\Tests;

use DanBettles\CommandLineTools\Output;
use DanBettles\CommandLineTools\Host;
use PHPUnit\Framework\TestCase;
use RuntimeException;

use const false;
use const null;

class HostTest extends TestCase
{
    public function testIsInstantiable(): void
    {
        $output = new Output();
        $host = new Host($output);

        $this->assertSame($output, $host->getOutput());
    }

    public function testPassthruExecutesAnExternalCommand(): void
    {
        $command = 'ls -al --color=always';
        $expectedResultCode = 0;

        $outputMock = $this
            ->getMockBuilder(Output::class)
            ->onlyMethods(['command'])
            ->getMock()
        ;

        $outputMock
            ->expects($this->once())
            ->method('command')
            ->with($command)
        ;

        $hostMock = $this
            ->getMockBuilder(Host::class)
            ->onlyMethods(['passthruProxy'])
            ->setConstructorArgs([$outputMock])
            ->getMock()
        ;

        $hostMock
            ->expects($this->once())
            ->method('passthruProxy')
            ->with($command, 1)
            ->willReturnCallback(function ($command, &$resultCode) use ($expectedResultCode) {
                $resultCode = $expectedResultCode;

                return null;  // `passthru()` successful.
            })
        ;

        /** @var Host $hostMock */

        $actualResultCode = $hostMock->passthru($command);

        $this->assertSame($expectedResultCode, $actualResultCode);
    }

    public function testPassthruThrowsAnExceptionIfItFailedToExecuteTheCommand(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('PHP failed to execute the command.');

        $command = 'bums';

        $outputMock = $this
            ->getMockBuilder(Output::class)
            ->onlyMethods(['command'])
            ->getMock()
        ;

        $outputMock
            ->expects($this->once())
            ->method('command')
            ->with($command)
        ;

        $hostMock = $this
            ->getMockBuilder(Host::class)
            ->onlyMethods(['passthruProxy'])
            ->setConstructorArgs([$outputMock])
            ->getMock()
        ;

        $hostMock
            ->expects($this->once())
            ->method('passthruProxy')
            ->with($command, 1)
            ->willReturn(false)
        ;

        /** @var Host $hostMock */

        $hostMock->passthru($command);
    }

    public function testPassthruThrowsAnExceptionIfTheCommandReturnedANonZeroExitStatus(): void
    {
        $expectedResultCode = 2;

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage("The command returned a non-zero, `{$expectedResultCode}`, exit status.");

        $command = 'bums';

        $outputMock = $this
            ->getMockBuilder(Output::class)
            ->onlyMethods(['command'])
            ->getMock()
        ;

        $outputMock
            ->expects($this->once())
            ->method('command')
            ->with($command)
        ;

        $hostMock = $this
            ->getMockBuilder(Host::class)
            ->onlyMethods(['passthruProxy'])
            ->setConstructorArgs([$outputMock])
            ->getMock()
        ;

        $hostMock
            ->expects($this->once())
            ->method('passthruProxy')
            ->with($command, 1)
            ->willReturnCallback(function ($command, &$resultCode) use ($expectedResultCode) {
                $resultCode = $expectedResultCode;

                return null;  // `passthru()` successful.
            })
        ;

        /** @var Host $hostMock */

        $hostMock->passthru($command);
    }

    public function testPassthruCanBeMadeToReturnFalseIfItFailedToExecuteTheCommand(): void
    {
        $command = 'bums';

        $outputMock = $this
            ->getMockBuilder(Output::class)
            ->onlyMethods(['command', 'danger'])
            ->getMock()
        ;

        $outputMock
            ->expects($this->once())
            ->method('command')
            ->with($command)
        ;

        $outputMock
            ->expects($this->once())
            ->method('danger')
            ->with("PHP failed to execute the command.")
        ;

        $hostMock = $this
            ->getMockBuilder(Host::class)
            ->onlyMethods(['passthruProxy'])
            ->setConstructorArgs([$outputMock])
            ->getMock()
        ;

        $hostMock
            ->expects($this->once())
            ->method('passthruProxy')
            ->with($command, 1)
            ->willReturn(false)
        ;

        /** @var Host $hostMock */

        $returnValue = $hostMock->passthru($command, $throwOnError = false);

        $this->assertFalse($returnValue);
    }

    public function testPassthruCanBeMadeToReturnTheResultCodeIfTheCommandReturnedANonZeroExitStatus(): void
    {
        $command = 'bums';
        $expectedResultCode = 2;

        $outputMock = $this
            ->getMockBuilder(Output::class)
            ->onlyMethods(['command', 'danger'])
            ->getMock()
        ;

        $outputMock
            ->expects($this->once())
            ->method('command')
            ->with($command)
        ;

        $outputMock
            ->expects($this->once())
            ->method('danger')
            ->with("The command returned a non-zero, `{$expectedResultCode}`, exit status.")
        ;

        $hostMock = $this
            ->getMockBuilder(Host::class)
            ->onlyMethods(['passthruProxy'])
            ->setConstructorArgs([$outputMock])
            ->getMock()
        ;

        $hostMock
            ->expects($this->once())
            ->method('passthruProxy')
            ->with($command, 1)
            ->willReturnCallback(function ($command, &$resultCode) use ($expectedResultCode) {
                $resultCode = $expectedResultCode;

                return null;  // `passthru()` successful.
            })
        ;

        /** @var Host $hostMock */

        $actualResultCode = $hostMock->passthru($command, $throwOnError = false);

        $this->assertSame($expectedResultCode, $actualResultCode);
    }
}
