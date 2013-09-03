To start using DStyles just simply include it and then create DStyles object as follows:
$obj = new DSTyles([folder], [styleName], [extension]);

(if omitted, default values will be used)

After that You can start using DStyles by referring to its object. Here is a simple tour how it works:

#1:
header($data) method is an equivalent to: get('header')->set(array $data)
footer($data) method is an equivalent to: get('footer')->set(array $data)

#2:
set() method can be used in following ways:
 - set('CONSTANT', 'value')
 - set(array(
    'CONSTANT1'	=> 'value1',
    'CONSTANT2'	=> 'value2',
    ...
 ))
However, header() and footer() methods accept only arrays as a parameter.

#3:
Every method used after get() can by linked directly to another, for example:
$obj->get( ... )->registerTrigger( ... )->set( ... )->trigger( ... )
or
$el = $obj->get( ... )
$el->registerTrigger( ... )->set( ... )->trigger( ... )

#4:
You can both saving template object to a variable (useful in loops) or display result directly. To save generated data use create() method. To display it just use result() method. However be careful while trying to get split data. create() method will return an array with all split parts and numbers as keys (starting from 0). result() method will display whole data joined when no parameters. If any (only numbers are accepted), then it will display only specified part. In example, following codes result in the same:

~~~~~~
<?php
$el = $obj->get( ... )->split( ... );
$elArr = $el->create();

$elArr[0] == $el->result(0)
?>
~~~~~~