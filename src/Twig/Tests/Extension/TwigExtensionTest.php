<?php

/*
 * This file is part of Mannequin.
 *
 * (c) 2017 Last Call Media, Rob Bayliss <rob@lastcallmedia.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace LastCall\Mannequin\Twig\Tests\Extension;

use LastCall\Mannequin\Core\Extension\ExtensionInterface;
use LastCall\Mannequin\Core\Iterator\MappingCallbackIterator;
use LastCall\Mannequin\Core\MannequinConfig;
use LastCall\Mannequin\Core\Tests\Extension\ExtensionTestCase;
use LastCall\Mannequin\Twig\Discovery\TwigDiscovery;
use LastCall\Mannequin\Twig\Driver\SimpleTwigDriver;
use LastCall\Mannequin\Twig\Engine\TwigEngine;
use LastCall\Mannequin\Twig\Subscriber\InlineTwigYamlMetadataSubscriber;
use LastCall\Mannequin\Twig\Subscriber\TwigIncludeSubscriber;
use LastCall\Mannequin\Twig\TemplateNameMapper;
use LastCall\Mannequin\Twig\TwigExtension;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class TwigExtensionTest extends ExtensionTestCase
{
    public function getExtension(): ExtensionInterface
    {
        return new TwigExtension();
    }

    public function getDispatcherProphecy(): ObjectProphecy
    {
        $dispatcher = $this->prophesize(EventDispatcherInterface::class);
        $dispatcher->addSubscriber(
            Argument::type(InlineTwigYamlMetadataSubscriber::class)
        )
            ->shouldBeCalled();
        $dispatcher->addSubscriber(Argument::type(TwigIncludeSubscriber::class))
            ->shouldBeCalled();

        return $dispatcher;
    }

    public function testTwigDiscoveryGetsMappingIterator()
    {
        $root = getcwd();
        $driver = new SimpleTwigDriver($root, ['cache' => '/tmp/mannequin-test/twig']);
        $driver->getTwig();
        $inner = new \ArrayIterator([]);
        $outer = new MappingCallbackIterator($inner, new TemplateNameMapper($root));
        $discovery = new TwigDiscovery($driver, $outer);
        $extension = new TwigExtension();
        $extension->register($this->getMannequin());
        $this->assertEquals([$discovery], $extension->getDiscoverers());
    }

    public function testTwigDiscoveryGetsPassedIterator()
    {
        $iterator = new \ArrayIterator(['foo']);
        $root = getcwd();
        $driver = new SimpleTwigDriver($root, ['cache' => '/tmp/mannequin-test/twig']);
        $driver->getTwig();
        $outer = new MappingCallbackIterator($iterator, new TemplateNameMapper($root));
        $discovery = new TwigDiscovery($driver, $outer);
        $extension = new TwigExtension(['finder' => $iterator]);
        $extension->register($this->getMannequin());
        $this->assertEquals([$discovery], $extension->getDiscoverers());
    }

    public function testCanOverrideTwigOptions()
    {
        $options = [
            'cache' => sys_get_temp_dir(),
            'auto_reload' => false,
        ];
        $driver = new SimpleTwigDriver(getcwd(), $options);
        $engine = new TwigEngine($driver);
        $extension = new TwigExtension(['twig_options' => $options]);
        $extension->register($this->getMannequin());
        $this->assertEquals([$engine], $extension->getEngines());
    }

    public function testPassesStylesAndScriptsToEngine()
    {
        $driver = new SimpleTwigDriver(getcwd());
        $engine = new TwigEngine($driver, ['foo'], ['bar']);
        $extension = new TwigExtension();
        $config = MannequinConfig::create([
            'styles' => ['foo'],
            'scripts' => ['bar'],
        ]);
        $extension->register($this->getMannequin($config));
        $this->assertEquals([$engine], $extension->getEngines());
    }
}
