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

  public function toMf2() {
    return $this->toArray();
  }

  public function __toString() {
    return json_encode($this->toArray());
  }

  public function __get($k) {
    switch($k) {
      case 'error':
        return $this->_error;
      case 'error_property':
        return $this->_property;
      case 'error_description':
        return $this->_description;
    }
    throw new Exception('A Micropub error occurred, and you attempted to access the Error object as though it was a successful request. You should check that the object returned was an error and handle it properly.');
  }

}
