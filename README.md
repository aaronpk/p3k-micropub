p3k\Micropub
============

[![Build Status](https://travis-ci.org/aaronpk/p3k-micropub.svg?branch=master)](https://travis-ci.org/aaronpk/p3k-micropub)

Usage
-----

### Creating the Request Object

If you don't know whether the client has sent a form-encoded or JSON request, you can use the method below to automatically detect the type of input and create the request object. You can pass the raw string input into this function, or an array.

```php
$input = file_get_contents('php://input');
$request = \p3k\Micropub\Request::create($input);
```

If you are using a framework like Laravel which handles parsing the input already, you can pass an array with the input:

```php
$request = \p3k\Micropub\Request::create(Request::all());
```

### Form-Encoded Input

If you know you are handling a form-encoded request, create a new Micropub Request object given form-encoded input:

```php
$request = \p3k\Micropub\Request::createFromPostArray($_POST);
```

### JSON Input

Create a new Micropub Request object given an array from JSON input:

```php
$input = json_decode(file_get_contents('php://input'), true);
$request = \p3k\Micropub\Request::createFromJSONObject($input);
```

(This actually works given either an Object or Array created from the JSON, but internally it uses an array so it's more efficient to decode it to an array at first.)

### Handling Errors

If the input data could not be interpreted as a Micropub request, the object returned will be an error instead. You can check for this by testing whether the type of object returned is a `\p3k\Micropub\Error`, or you can test the `error` property.

```php
$request = \p3k\Micropub\Request::createFromPostArray($_POST);

if($request->error) {
  // Something went wrong. More information is available here:
  // $request->error_property
  // $request->error_description
}

if(get_class($request) == \p3k\Micropub\Error::class) {
  // Another way to test for errors
}

```

### Handling the Request

Now that you have a `$request` object corresponding to the Micropub request, you can inspect it to determine how to handle the request. An outline illustrating a basic Micropub workflow is below.

```php
switch($request->action) {
  case 'create':

    // The type of Microformats object being created:
    $request->type; // typically this is `h-entry`

    // All the values of the post are available in this array:
    $request->properties;

    /* 
      {
        "content": ["This is a plaintext note"]
      }

      {
        "name": ["Hello World"],
        "content": [{"html": "This is an <i>HTML</i> blog post"]
      }
    */

    // Any Micropub actions such as mp-syndicate-to are available in this array:
    foreach($request->commands as $command=>$value) {
      switch($command) {
        case 'mp-syndicate-to': 
          // ...
          break;
        // ... etc
      }
    }

    break;
  case 'update':
    // The URL being updated is available here
    $request->url;

    // ... retrieve the post given by that URL

    // Update actions have three parts: replace, add and delete
    // See https://www.w3.org/TR/micropub/#update for more details

    foreach($request->update['replace'] as $key=>$value) {

    }

    foreach($request->update['add'] as $key=>$value) {
      
    }

    foreach($request->update['delete'] as $key=>$value) {
      
    }

    break;
  case 'delete':
    // The URL being deleted is available here:
    $request->url;

    // ... you'll want to retrieve that post and delete it

    break;
}

```

### Output as Microformats JSON

You can output the Micropub request as a Microformats JSON array.

```php
print_r($request->toMf2());
```



License
-------

Copyright 2018 by Aaron Parecki

Available under the Apache 2.0 and MIT licenses.

#### Apache 2.0

Licensed under the Apache License, Version 2.0 (the "License");
you may not use this file except in compliance with the License.
You may obtain a copy of the License at

   http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software
distributed under the License is distributed on an "AS IS" BASIS,
WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
See the License for the specific language governing permissions and
limitations under the License.

#### MIT

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.

