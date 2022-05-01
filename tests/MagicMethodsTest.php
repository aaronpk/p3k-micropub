<?php

use p3k\Micropub\Request;

class MagicMethodsTest extends PHPUnit_Framework_TestCase {

  public function testGetType() {
    $request = Request::create([
      'h' => 'entry',
      'content' => 'Hello World'
    ]);
    $this->assertEquals('h-entry', $request->type);
  }

  public function testGetActionCreate() {
    $request = Request::create([
      'h' => 'entry',
      'content' => 'Hello World'
    ]);
    $this->assertEquals('create', $request->action);
  }

  public function testGetActionUpdate() {
    $request = Request::createFromJSONObject([
      'action' => 'update',
      'url' => 'https://example.com/post',
      'replace' => [
        'content' => ['Hello World']
      ],
    ]);
    $this->assertEquals('update', $request->action);
  }

  public function testGetActionDelete() {
    $request = Request::createFromPostArray([
      'action' => 'delete',
      'url' => 'https://example.com/post',
    ]);
    $this->assertEquals('delete', $request->action);
  }

}

