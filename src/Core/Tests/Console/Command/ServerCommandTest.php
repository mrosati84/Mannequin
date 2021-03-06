<?php

/*
 * This file is part of Mannequin.
 *
 * (c) 2017 Last Call Media, Rob Bayliss <rob@lastcallmedia.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace LastCall\Mannequin\Core\Tests\Console\Command;

use LastCall\Mannequin\Core\Console\Command\StartCommand;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Helper\DebugFormatterHelper;
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Helper\ProcessHelper;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\ProcessBuilder;
use Symfony\Component\Console\Output\OutputInterface;

class ServerCommandTest extends TestCase
{
    public function getInputOutput()
    {
        return [
            [null, '127.0.0.1:8000'],
            ['0.0.0.0', '0.0.0.0:8000'],
            ['*:8002', '0.0.0.0:8002'],
            ['8002', '127.0.0.1:8002'],
        ];
    }

    /**
     * @dataProvider getInputOutput
     */
    public function testCommandIo($inputAddress, $expectedListenAddress)
    {
        $command = new StartCommand('server', __FILE__, __FILE__);
        $command->setHelperSet(new HelperSet([
            new ProcessHelper(),
            new DebugFormatterHelper(),
        ]));
        $builder = $this->prophesize(ProcessBuilder::class);
        $builder
            ->setArguments([
                'php',
                '-S',
                $expectedListenAddress,
                realpath(__DIR__.'/../../../Resources/router.php'),
            ])
            ->shouldBeCalled()
            ->willReturn($builder);

        $builder
            ->addEnvironmentVariables([
                'MANNEQUIN_CONFIG' => __FILE__,
                'MANNEQUIN_AUTOLOAD' => __FILE__,
                'MANNEQUIN_DEBUG' => false,
                'MANNEQUIN_VERBOSITY' => OutputInterface::VERBOSITY_NORMAL,
            ])
            ->shouldBeCalled()
            ->willReturn($builder);
        $builder
            ->setWorkingDirectory(__DIR__)
            ->shouldBeCalled()->willReturn($builder);
        $builder
            ->setTimeout(null)
            ->shouldBeCalled()
            ->willReturn($builder);

        $process = new Process('/bin/true');
        $builder
            ->getProcess()
            ->willReturn($process);

        $command->setProcessBuilder($builder->reveal());

        $tester = new CommandTester($command);
        $tester->execute(['address' => $inputAddress]);

        $expectedOutput = sprintf('Starting server on http://%s', $expectedListenAddress);
        $this->assertContains($expectedOutput, $tester->getDisplay());
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Port test is not valid
     */
    public function testInvalidPort()
    {
        $command = new StartCommand('server', __FILE__, __FILE__);
        $command->setHelperSet(new HelperSet([
            new ProcessHelper(),
            new DebugFormatterHelper(),
        ]));
        $builder = $this->prophesize(ProcessBuilder::class);
        $command->setProcessBuilder($builder->reveal());
        $tester = new CommandTester($command);
        $tester->execute(['address' => 'test:test']);
    }
}
