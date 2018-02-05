<?php
namespace Models;
/**
 *
 */
class User
{
  private $name;
  function __construct()
  {
    $this->name = 'Pepe';
  }
  public function getName () {
    return $this->name;
  }
}
