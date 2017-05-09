<?php


namespace LastCall\Patterns\Twig\Tests\Renderer;


use LastCall\Patterns\Core\Pattern\PatternInterface;
use LastCall\Patterns\Core\Render\RenderedInterface;
use LastCall\Patterns\Core\Variable\VariableInterface;
use LastCall\Patterns\Core\Variable\VariableSet;
use LastCall\Patterns\Twig\Pattern\TwigPattern;
use LastCall\Patterns\Twig\Render\TwigRenderer;
use PHPUnit\Framework\TestCase;

class TwigRendererTest extends TestCase {

  private function getTwig() {
    $twig = $this->prophesize(\Twig_Environment::class);
    return $twig;
  }

  public function testDoesntSupportOtherPatterns() {
    $twig = $this->getTwig();
    $pattern = $this->prophesize(PatternInterface::class)->reveal();
    $renderer = new TwigRenderer($twig->reveal());
    $this->assertFalse($renderer->supports($pattern));
  }

  public function testSupportsTwigPatterns() {
    $twig = $this->getTwig();
    $pattern = $this->prophesize(TwigPattern::class)->reveal();
    $renderer = new TwigRenderer($twig->reveal());
    $this->assertTrue($renderer->supports($pattern));
  }

  public function testAddsGlobalScriptsAndStyles() {
    $scripts = ['foo', 'bar'];
    $styles = ['bar', 'baz'];
    $twig = $this->getTwig();
    $twig->render('foo', [])->willReturn('rendered');
    $pattern = new TwigPattern('foo', 'Foo', 'foo', new VariableSet());
    $renderer = new TwigRenderer($twig->reveal(), NULL, $styles, $scripts);
    $rendered = $renderer->render($pattern);
    $this->assertEquals($scripts, $rendered->getScripts());
  }

  public function testMergesGlobalVariablesAndManifests() {
    $locals = $this->prophesize(VariableSet::class);
    $globals = $this->prophesize(VariableSet::class);
    $merged = $this->prophesize(VariableSet::class);
    $merged->manifest()
      ->shouldBeCalled()
      ->willReturn(['foo' => 'bar']);
    $locals->applyGlobals($globals)
      ->shouldBeCalled()
      ->willReturn($merged);
    $twig = $this->getTwig();
    $twig->render('foo', ['foo' => 'bar'])->willReturn('rendered');
    $pattern = new TwigPattern('foo', 'Foo', 'foo', $locals->reveal());
    $renderer = new TwigRenderer($twig->reveal(), $globals->reveal());
    $renderer->render($pattern);
  }

  public function testRendersSubPattern() {
    $rendered = $this->prophesize(RenderedInterface::class);
    $rendered->getScripts()
      ->shouldBeCalled()
      ->willReturn(['fooscript']);
    $rendered->getStyles()
      ->shouldBeCalled()
      ->willReturn(['foostyle']);
    $rendered->getMarkup()
      ->shouldBeCalled()
      ->willReturn('This is the markup');
    $markup = new \Twig_Markup('This is the markup', 'UTF-8');

    $patternVar = $this->prophesize(VariableInterface::class);
    $patternVar->getValue()->willReturn($rendered);
    $patternVar->hasValue()->willReturn(TRUE);

    $locals = new VariableSet([
      'subpattern' => $patternVar->reveal(),
    ]);
    $pattern = new TwigPattern('foo', 'Foo', 'foo', $locals);
    $twig = $this->getTwig();
    $twig->getCharset()->willReturn('UTF-8');
    $twig->render('foo', ['subpattern' => $markup])->willReturn('rendered');
    $renderer = new TwigRenderer($twig->reveal());
    $renderer->render($pattern);
  }
}