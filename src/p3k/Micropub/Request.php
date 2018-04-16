<?php
namespace p3k\Micropub;

class Request {

  private $_action;
  private $_url;
  private $_type;
  private $_properties = [];
  private $_commands = [];
  private $_update = [
    'replace' => [],
    'add' => [],
    'delete' => [],
  ];

  public static function create($input) {
    // Attempt to detect form-encoded or JSON requests
    if(is_object($input))
      return self::createFromJSONObject($input);

    if(is_array($input)) {
      if(isset($input['h']))
        return self::createFromPostArray($input);
      if(isset($input['type']))
        return self::createFromJSONObject($input);
    }

    if(is_string($input))
      return self::createFromString($input);

    return new Error('invalid_input', null, 'Input could not be parsed as either JSON or form-encoded');
  }

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

    } elseif(isset($input['action'])) {

      // Actions require a URL
      if(!isset($input['url'])) {
        return new Error('invalid_input', 'url', 'Micropub actions require a URL property');
      }

      $request->_action = $input['action'];
      $request->_url = $input['url'];

      if($input['action'] == 'update') {
        foreach(array_keys($request->_update) as $a) {
          if(isset($input[$a])) {
            if(!is_array($input[$a])) {
              return new Error('invalid_input', $a, 'Invalid syntax for update action');
            }
            foreach($input[$a] as $p=>$v) {
              if($p != 'delete' && !is_array($v)) {
                return new Error('invalid_input', $a.'.'.$p, 'All values in update actions must be arrays');
              }
            }
            $request->_update[$a] = $input[$a];
          }
        }
      }

    } else {
      return new Error('invalid_input', null, 'No Micropub request data was found in the input');
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

    } elseif(isset($POST['action'])) {

      if($POST['action'] == 'update') {
        return new Error('invalid_input', 'action', 'Micropub update actions require using the JSON syntax');
      }

      // Actions require a URL
      if(!isset($POST['url'])) {
        return new Error('invalid_input', 'url', 'Micropub actions require a URL property');
      }

      $request->_action = $POST['action'];
      $request->_url = $POST['url'];

    } else {
      return new Error('invalid_input', null, 'No Micropub request data was found in the input');
    }

    return $request;
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
      case 'properties':
        return $this->_properties;
      case 'update':
        return $this->_update;
      case 'url':
        return $this->_url;
      case 'error':
        return false;
    }
    return null;
  }

}

