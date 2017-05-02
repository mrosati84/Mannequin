<?php


namespace LastCall\Patterns\Core\Pattern;


class TemplatePattern implements PatternInterface {

  use HasNameAndId;
  use Taggable;

  private $reference;

  private $variables = [];

  public function __construct($id, $name, $reference, $variables = []) {
    $this->setId($id);
    $this->setName($name);
    $this->reference = $reference;
    $this->variables = $variables;
  }

  public function getTemplateReference(): string {
    return $this->reference;
  }

  public function getTemplateVariables() {
    return $this->variables;
  }
}