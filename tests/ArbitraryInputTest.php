<?php
class ArbitraryInputTest extends PHPUnit_Framework_TestCase {

  public function testCreateFromFormEncoded() {
    $input = [
      'h' => 'entry',
      'content' => 'Hello World'
    ];
    $request = \p3k\Micropub\Request::create($input);
    $this->assertEquals('create', $request->action);
    $expected = [
      'type' => ['h-entry'],
      'properties' => [
        'content' => ['Hello World']
      ]
    ];
    $this->assertEquals(true, $request->toMf2() == $expected);
  }

  public function testCreateFromJSONArray() {
    $input = [
      'type' => ['h-entry'],
      'properties' => [
        'content' => ['Hello World']
      ]
    ];
    $request = \p3k\Micropub\Request::create($input);
    $this->assertEquals('create', $request->action);
    $expected = [
      'type' => ['h-entry'],
      'properties' => [
        'content' => ['Hello World']
      ]
    ];
    $this->assertEquals(true, $request->toMf2() == $expected);
  }

  public function testCreateFromJSONObject() {
    $input = json_decode(json_encode([
      'type' => ['h-entry'],
      'properties' => [
        'content' => ['Hello World']
      ]
    ]));
    $request = \p3k\Micropub\Request::create($input);
    $this->assertEquals('create', $request->action);
    $expected = [
      'type' => ['h-entry'],
      'properties' => [
        'content' => ['Hello World']
      ]
    ];
    $this->assertEquals(true, $request->toMf2() == $expected);
  }

  public function testFailToCreateFromInvalidInput() {
    $request = \p3k\Micropub\Request::create('foo');
    $this->assertInstanceOf(\p3k\Micropub\Error::class, $request);
    $this->assertEquals('No Micropub request data was found in the input', $request->error_description);
    $this->assertEquals(true, $request->toArray() === $request->toMf2());

    $request = \p3k\Micropub\Request::create(2);
    $this->assertInstanceOf(\p3k\Micropub\Error::class, $request);
    $this->assertEquals('Input could not be parsed as either JSON or form-encoded', $request->error_description);
    $this->assertEquals(true, $request->toArray() === $request->toMf2());
  }

}
