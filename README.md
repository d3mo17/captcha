
# Captcha

A library trying to secure form submits without the need of separate user
input (code read from distorted image).

## Mode of operation

Class "Code" generates an array with seven keys. It depends on the current
server time which keys will become set with values and which not. The idea is
to create seven input elements in a form, put the values from the generated
array into the input elements, submit these values along with the other input
data from user, restore the original code array from incoming request on server
side and validate the restored array against class "Code" again.

So basically the only methods you need after instance object from class
`\DMo\Captcha\Code`, are `get` to fetch the generated code and `validate` to
compare restored array from request.

You can use class `\DMo\Captcha\HTMLGenerator` to generate the html-code for
the input elements from instance of class `\DMo\Captcha\Code` and also to
restore the code array from incoming request parameters after the form submit.

Please have a look to the examples for more clarity.

## License

The MIT License (MIT)

Copyright (c) 2022 Daniel Moritz

Permission is hereby granted, free of charge, to any person obtaining a copy of
this software and associated documentation files (the "Software"), to deal in
the Software without restriction, including without limitation the rights to
use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of
the Software, and to permit persons to whom the Software is furnished to do so,
subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS
FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR
COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER
IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN
CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
