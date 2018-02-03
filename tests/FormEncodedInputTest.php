<?php
class FormEncodedInputTest extends PHPUnit_Framework_TestCase {

  public function testTest() {
    $this->assertEquals(1, 1);
  }

  public function testBasicHEntry() {
    $_POST = [
      'h' => 'entry',
      'content' => 'Hello World'
    ];
    $request = \p3k\Micropub\Request::createFromPostArray($_POST);
    $expected = [
      'type' => ['h-entry'],
      'properties' => [
        'content' => ['Hello World']
      ]
    ];
    $this->assertEquals('create', $request->action);
    $this->assertEquals(true, $request->toMf2() == $expected);
    $this->assertEquals(null, $request->foo);
  }

  public function testHEntryWithArrayValues() {
    $_POST = [
      'h' => 'entry',
      'content' => 'Hello World',
      'category' => ['one','two']
    ];
    $request = \p3k\Micropub\Request::createFromPostArray($_POST);
    $expected = [
      'type' => ['h-entry'],
      'properties' => [
        'content' => ['Hello World'],
        'category' => ['one','two']
      ]
    ];
    $this->assertEquals('create', $request->action);
    $this->assertEquals(true, $request->toMf2() == $expected);
  }

  public function testIgnoreAccessToken() {
    $_POST = [
      'h' => 'entry',
      'content' => 'Hello World',
      'access_token' => 'xxxxxx'
    ];
    $request = \p3k\Micropub\Request::createFromPostArray($_POST);
    $expected = [
      'type' => ['h-entry'],
      'properties' => [
        'content' => ['Hello World']
      ]
    ];
    $this->assertEquals('create', $request->action);
    $this->assertEquals(true, $request->toMf2() == $expected);
  }

  public function testFailOnURLInInput() {
    $_POST = [
      'h' => 'entry',
      'content' => 'Hello World',
      'url' => 'http://example.com/'
    ];
    $request = \p3k\Micropub\Request::createFromPostArray($_POST);
    $this->assertInstanceOf(\p3k\Micropub\Error::class, $request);
    $this->assertEquals('url', $request->property);
  }

  public function testFailOnHWithAction() {
    $_POST = [
      'h' => 'entry',
      'action' => 'update', 
      'content' => 'Hello World',
    ];
    $request = \p3k\Micropub\Request::createFromPostArray($_POST);
    $this->assertInstanceOf(\p3k\Micropub\Error::class, $request);
    $this->assertEquals('action', $request->property);
  }

  public function testFailOnInvalidInput() {
    $_POST = [
      'not' => 'micropub'
    ];
    $request = \p3k\Micropub\Request::createFromPostArray($_POST);
    $this->assertInstanceOf(\p3k\Micropub\Error::class, $request);
    $this->assertEquals('{"error":"invalid_input","error_property":null,"error_description":"No Micropub request properties were found in the input"}', $request->__toString());
    $this->assertEquals('invalid_input', $request->error);
    $this->assertEquals(null, $request->property);
    $this->assertEquals('No Micropub request properties were found in the input', $request->description);
    $this->assertEquals(null, $request->foo);
  }

  public function testMPActions() {
    $_POST = [
      'h' => 'entry',
      'content' => 'Hello World',
      'mp-syndicate-to' => ['twitter'],
    ];
    $request = \p3k\Micropub\Request::createFromPostArray($_POST);
    $expected = [
      'type' => ['h-entry'],
      'properties' => [
        'content' => ['Hello World']
      ]
    ];
    $commands = [
      'mp-syndicate-to' => ['twitter']
    ];
    $this->assertEquals('create', $request->action);
    $this->assertEquals(true, $request->toMf2() == $expected);
    $this->assertEquals(true, $request->commands == $commands);
  }

  public function testNamedArrayKeyInputs() {
    $_POST = [
      'h' => 'entry',
      'content' => [
        'html' => '<b>Hello World</b>',
      ]
    ];
    $request = \p3k\Micropub\Request::createFromPostArray($_POST);
    $this->assertInstanceOf(\p3k\Micropub\Error::class, $request);
    $this->assertEquals('content', $request->property);
  }

}
