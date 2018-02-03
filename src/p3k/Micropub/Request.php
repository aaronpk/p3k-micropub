<?php
namespace p3k\Micropub;

class Request {

  private $_action;
  private $_url;
  private $_type;
  private $_properties = [];
  private $_commands = [];

  public static function createFromPostArray($POST) {
    $request = new Request();

    if(isset($POST['h'])) {
      $request->_action = 'create';
      $request->_type = 'h-'.$POST['h'];
      unset($POST['h']);
      unset($POST['access_token']);

      // Can't create posts while specifying a URL
      if(isset($POST['url']))
        return new Error('invalid_input', 'url', 'Cannot create posts while specifying a URL');

      // Can't specify an action when creating a post
      if(isset($POST['action']))
        return new Error('invalid_input', 'action', 'Cannot specify an action when creating a post');

      foreach($POST as $k=>$v) {
        // Values in form-encoded input can only be numeric indexed arrays
        if(is_array($v) && !isset($v[0]))
          return new Error('invalid_input', $k, 'Values in form-encoded input can only be numeric indexed arrays');

        // All values in mf2 json are arrays
        if(!is_array($v))
          $v = [$v];

        if(substr($k, 0, 3) == 'mp-') {
          $request->_commands[$k] = $v;
        } else {
          $request->_properties[$k] = $v;
        }
      }

      return $request;
    } else {
      return new Error('invalid_input', null, 'No Micropub request properties were found in the input');
    }
  }

  public function toMf2() {
    return [
      'type' => [$this->_type],
      'properties' => $this->_properties
    ];
  }

  public function __get($k) {
    switch($k) {
      case 'action':
        return $this->_action;
      case 'commands':
        return $this->_commands;
    }
    return null;
  }

}

