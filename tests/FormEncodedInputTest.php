<?php
class FormEncodedInputTest extends PHPUnit_Framework_TestCase {

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
    $this->assertEquals(false, $request->error);
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

  public function testAllowURLInInput() {
    $_POST = [
      'h' => 'entry',
      'content' => 'Hello World',
      'url' => 'http://example.com/100'
    ];
    $request = \p3k\Micropub\Request::createFromPostArray($_POST);
    $this->assertEquals('create', $request->action);
    $expected = [
      'type' => ['h-entry'],
      'properties' => [
        'content' => ['Hello World'],
        'url' => ['http://example.com/100'],
      ]
    ];
    $this->assertEquals(true, $request->toMf2() == $expected);
  }

  public function testFailOnHWithAction() {
    $_POST = [
      'h' => 'entry',
      'action' => 'update', 
      'content' => 'Hello World',
    ];
    $request = \p3k\Micropub\Request::createFromPostArray($_POST);
    $this->assertInstanceOf(\p3k\Micropub\Error::class, $request);
    $this->assertEquals('action', $request->error_property);
  }

  public function testFailOnInvalidInput() {
    $_POST = [
      'not' => 'micropub'
    ];
    $request = \p3k\Micropub\Request::createFromPostArray($_POST);
    $this->assertInstanceOf(\p3k\Micropub\Error::class, $request);
    $this->assertEquals('{"error":"invalid_input","error_property":null,"error_description":"No Micropub request data was found in the input"}', $request->__toString());
    $this->assertEquals('invalid_input', $request->error);
    $this->assertEquals(null, $request->error_property);
    $this->assertEquals('No Micropub request data was found in the input', $request->error_description);

    $this->expectException(\p3k\Micropub\Exception::class);
    $request->foo;
  }

  public function testSingleMPAction() {
    $_POST = [
      'h' => 'entry',
      'content' => 'Hello World',
      'mp-syndicate-to' => 'twitter',
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

  public function testMultipleMPActions() {
    $_POST = [
      'h' => 'entry',
      'content' => 'Hello World',
      'mp-syndicate-to' => ['twitter','facebook'],
    ];
    $request = \p3k\Micropub\Request::createFromPostArray($_POST);
    $expected = [
      'type' => ['h-entry'],
      'properties' => [
        'content' => ['Hello World']
      ]
    ];
    $commands = [
      'mp-syndicate-to' => ['twitter','facebook']
    ];
    $this->assertEquals('create', $request->action);
    $this->assertEquals(true, $request->toMf2() == $expected);
    $this->assertEquals(true, $request->commands == $commands);
  }

  public function testFailOnNamedArrayKeyInputs() {
    $_POST = [
      'h' => 'entry',
      'content' => [
        'html' => '<b>Hello World</b>',
      ]
    ];
    $request = \p3k\Micropub\Request::createFromPostArray($_POST);
    $this->assertInstanceOf(\p3k\Micropub\Error::class, $request);
    $this->assertEquals('content', $request->error_property);
  }

  public function testFailOnNestedValues() {
    $_POST = [
      'h' => 'entry',
      'x-foo' => [
        [
          'type' => 'foo',
          'properties' => [
            'bar' => 'baz',
          ]
        ]
      ]
    ];
    $request = \p3k\Micropub\Request::createFromPostArray($_POST);
    $this->assertInstanceOf(\p3k\Micropub\Error::class, $request);
    $this->assertEquals('x-foo', $request->error_property);
  }

  public function testDeleteAction() {
    $_POST = [
      'action' => 'delete',
      'url' => 'http://example.com/100'
    ];
    $request = \p3k\Micropub\Request::createFromPostArray($_POST);
    $this->assertEquals('delete', $request->action);
    $this->assertEquals('http://example.com/100', $request->url);
  }

  public function testInvalidActionMissingURL() {
    $_POST = [
      'action' => 'delete',
    ];
    $request = \p3k\Micropub\Request::createFromPostArray($_POST);
    $this->assertInstanceOf(\p3k\Micropub\Error::class, $request);
    $this->assertEquals('url', $request->error_property);
  }

  public function testFailForUpdateAction() {
    $_POST = [
      'action' => 'update',
      'url' => 'http://example.com/100'
    ];
    $request = \p3k\Micropub\Request::createFromPostArray($_POST);
    $this->assertInstanceOf(\p3k\Micropub\Error::class, $request);
    $this->assertEquals('action', $request->error_property);
  }

}
