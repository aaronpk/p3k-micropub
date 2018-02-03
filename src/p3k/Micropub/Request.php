<?php
namespace p3k\Micropub;

class Request {

  private $_action;
  private $_url;
  private $_type;
  private $_properties = [];
  private $_commands = [];

  public static function createFromString($string) {
    // Attempt to json-decode
    $json = @json_decode($string, true);

    if($json) {
      return self::createFromJSONObject($json);
    }

    // If that failed, attempt to decode as form-encoded
    parse_str($string, $form);
    if($form) {
      return self::createFromPostArray($form);
    }

    // Otherwise fail
    return new Error('invalid_input', null, 'Input could not be parsed as either JSON or form-encoded');
  }

  public static function createFromJSONObject($input) {
    $request = new Request();

    if(is_object($input)) {
      $input = json_decode(json_encode($input, JSON_FORCE_OBJECT), true);
    } else if(!is_array($input)) {
      return new Error('invalid_input', null, 'Input was not an array.');
    }

    if(isset($input['type'])) {

      if(!is_array($input['type']))
        return new Error('invalid_input', 'type', 'Property type must be an array of Microformat vocabularies');

      $request->_action = 'create';
      $request->_type = $input['type'];

      if(!isset($input['properties']) || !is_array($input['properties'])) {
        return new Error('invalid_input', 'properties', 'In JSON format, all properties must be specified in a properties object');
      }

      $properties = $input['properties'];

      foreach($properties as $k=>$v) {
        // Ensure every value is a numeric-indexed array
        if(!is_array($v) || !isset($v[0])) {
          return new Error('invalid_input', $k, 'Values in JSON format must be arrays, even when there is only one value');
        }

        if(substr($k, 0, 3) == 'mp-') {
          $request->_commands[$k] = $v;
        } else {
          $request->_properties[$k] = $v;
        }
      }

    } else {
      return 'TODO';
    }

    return $request;    
  }

  public static function createFromPostArray($POST) {
    $request = new Request();

    if(isset($POST['h'])) {
      $request->_action = 'create';
      $request->_type = ['h-'.$POST['h']];
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

        if(is_array($v) && isset($v[0]) && is_array($v[0])) {
          return new Error('invalid_input', $k, 'Nested objects are not allowed in form-encoded requests');
        }

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
      'type' => $this->_type,
      'properties' => $this->_properties
    ];
  }

  public function __get($k) {
    switch($k) {
      case 'action':
        return $this->_action;
      case 'commands':
        return $this->_commands;
      case 'error':
        return false;
    }
    return null;
  }

}

