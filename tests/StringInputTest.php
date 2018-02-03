<?php
class StringInputTest extends PHPUnit_Framework_TestCase {

  public function testCreateFromFormEncodedString() {
    $input = http_build_query([
      'h' => 'entry',
      'content' => 'Hello World'
    ]);
    $request = \p3k\Micropub\Request::createFromString($input);
    $this->assertEquals('create', $request->action);
    $expected = [
      'type' => ['h-entry'],
      'properties' => [
        'content' => ['Hello World']
      ]
    ];
    $this->assertEquals(true, $request->toMf2() == $expected);
  }

  public function testCreateFromJSONString() {
    $input = json_encode([
      'type' => ['h-entry'],
      'properties' => [
        'content' => ['Hello World']
      ]
    ]);
    $request = \p3k\Micropub\Request::createFromString($input);
    $this->assertEquals('create', $request->action);
    $expected = [
      'type' => ['h-entry'],
      'properties' => [
        'content' => ['Hello World']
      ]
    ];
    $this->assertEquals(true, $request->toMf2() == $expected);
  }

  public function testFailToCreateFromInvalidString() {
    $request = \p3k\Micropub\Request::createFromString('');
    $this->assertInstanceOf(\p3k\Micropub\Error::class, $request);
    $this->assertEquals('Input could not be parsed as either JSON or form-encoded', $request->error_description);
    $this->assertEquals(true, $request->toArray() === $request->toMf2());
  }

}
