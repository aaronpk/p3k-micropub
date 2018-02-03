<?php
class JSONInputTest extends PHPUnit_Framework_TestCase {

  public function testBasicHEntry() {
    $input = [
      'type' => ['h-entry'],
      'properties' => [
        'content' => ['Hello World']
      ]
    ];
    $request = \p3k\Micropub\Request::createFromJSONObject($input);
    $expected = [
      'type' => ['h-entry'],
      'properties' => [
        'content' => ['Hello World']
      ]
    ];
    $this->assertEquals('create', $request->action);
    $this->assertEquals(['h-entry'], $request->toMf2()['type']);
    $this->assertEquals(true, $request->toMf2() == $expected);
  }

  public function testCreateFromJSONObject() {
    $input = json_decode(json_encode([
      'type' => ['h-entry'],
      'properties' => [
        'content' => ['Hello World']
      ]
    ]));
    $request = \p3k\Micropub\Request::createFromJSONObject($input);
    $expected = [
      'type' => ['h-entry'],
      'properties' => [
        'content' => ['Hello World']
      ]
    ];
    $this->assertEquals('create', $request->action);
    $this->assertEquals(['h-entry'], $request->toMf2()['type']);
    $this->assertEquals(true, $request->toMf2() == $expected);
  }

  public function testMPActions() {
    $input = [
      'type' => ['h-entry'],
      'properties' => [
        'content' => ['Hello World'],
        'mp-syndicate-to' => ['twitter'],
      ]
    ];
    $request = \p3k\Micropub\Request::createFromJSONObject($input);
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

  public function testFailOnNonArrayInput() {
    $input = 'foo';
    $request = \p3k\Micropub\Request::createFromJSONObject($input);
    $this->assertInstanceOf(\p3k\Micropub\Error::class, $request);
  }

  public function testFailOnInvalidType() {
    $input = [
      'type' => 'h-entry', // not an array
      'properties' => [
      ]
    ];
    $request = \p3k\Micropub\Request::createFromJSONObject($input);
    $this->assertInstanceOf(\p3k\Micropub\Error::class, $request);
    $this->assertEquals('type', $request->error_property);
  }

  public function testFailOnMissingProperties() {
    $input = [
      'type' => ['h-entry'],
    ];
    $request = \p3k\Micropub\Request::createFromJSONObject($input);
    $this->assertInstanceOf(\p3k\Micropub\Error::class, $request);
    $this->assertEquals('properties', $request->error_property);
  }

  public function testFailOnInvalidProperties() {
    $input = [
      'type' => ['h-entry'],
      'properties' => [
        'content' => 'Hello World'  // not an array
      ]
    ];
    $request = \p3k\Micropub\Request::createFromJSONObject($input);
    $this->assertInstanceOf(\p3k\Micropub\Error::class, $request);
    $this->assertEquals('content', $request->error_property);
  }

  public function testFailOnInvalidInput() {
    $input = [
      'not' => 'micropub'
    ];
    $request = \p3k\Micropub\Request::createFromJSONObject($input);
    $this->assertInstanceOf(\p3k\Micropub\Error::class, $request);
    $this->assertEquals('{"error":"invalid_input","error_property":null,"error_description":"No Micropub request data was found in the input"}', $request->__toString());
    $this->assertEquals('invalid_input', $request->error);
    $this->assertEquals(null, $request->error_property);
    $this->assertEquals('No Micropub request data was found in the input', $request->error_description);
  }

  public function testDeleteAction() {
    $input = [
      'action' => 'delete',
      'url' => 'http://example.com/100'
    ];
    $request = \p3k\Micropub\Request::createFromJSONObject($input);
    $this->assertEquals('delete', $request->action);
    $this->assertEquals('http://example.com/100', $request->url);
  }

  public function testInvalidActionMissingURL() {
    $input = [
      'action' => 'delete',
    ];
    $request = \p3k\Micropub\Request::createFromJSONObject($input);
    $this->assertInstanceOf(\p3k\Micropub\Error::class, $request);
    $this->assertEquals('url', $request->error_property);
  }

}
