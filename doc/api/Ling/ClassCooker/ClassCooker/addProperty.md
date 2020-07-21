[Back to the Ling/ClassCooker api](https://github.com/lingtalfi/ClassCooker/blob/master/doc/api/Ling/ClassCooker.md)<br>
[Back to the Ling\ClassCooker\ClassCooker class](https://github.com/lingtalfi/ClassCooker/blob/master/doc/api/Ling/ClassCooker/ClassCooker.md)


ClassCooker::addProperty
================



ClassCooker::addProperty — Adds a property to the current class.




Description
================


public [ClassCooker::addProperty](https://github.com/lingtalfi/ClassCooker/blob/master/doc/api/Ling/ClassCooker/ClassCooker/addProperty.md)(string $name, string $content, ?array $options = []) : void




Adds a property to the current class.

If the property already exists, an exception will be thrown.
By default, the property is written below the last property if any.
If not, it's written before the first method of the class if any.
If not, it's added at the beginning of the class (just after the class declaration).


But you can define a property as a target (using the afterProperty option), in which case
we will write the new property immediately after that target.


Available options are:
- afterProperty: string, the name of the property to use as a target (the new property will be written after it)




Parameters
================


- name

    

- content

    

- options

    


Return values
================

Returns void.


Exceptions thrown
================

- [Exception](http://php.net/manual/en/class.exception.php).&nbsp;







Source Code
===========
See the source code for method [ClassCooker::addProperty](https://github.com/lingtalfi/ClassCooker/blob/master/ClassCooker.php#L213-L254)


See Also
================

The [ClassCooker](https://github.com/lingtalfi/ClassCooker/blob/master/doc/api/Ling/ClassCooker/ClassCooker.md) class.

Previous method: [addMethod](https://github.com/lingtalfi/ClassCooker/blob/master/doc/api/Ling/ClassCooker/ClassCooker/addMethod.md)<br>Next method: [addUseStatements](https://github.com/lingtalfi/ClassCooker/blob/master/doc/api/Ling/ClassCooker/ClassCooker/addUseStatements.md)<br>
