<?php
namespace p3k\Micropub;

class Error {

  private $_error;
  private $_property;
  private $_description;

  public function __construct($error, $property, $description) {
    $this->_error = $error;
    $this->_property = $property;
    $this->_description = $description;
  }

  public function toArray() {
    return [
      'error' => $this->_error,
      'error_property' => $this->_property,
      'error_description' => $this->_description,
    ];
  }

  public function __toString() {
    return json_encode($this->toArray());
  }

  public function __get($k) {
    switch($k) {
      case 'error':
        return $this->_error;
      case 'property':
        return $this->_property;
      case 'description':
        return $this->_description;
    }
    return null;
  }

}
