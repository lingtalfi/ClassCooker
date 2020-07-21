Ling/ClassCooker
================
2020-07-21 --> 2020-07-21




Table of contents
===========

- [ClassCooker](https://github.com/lingtalfi/ClassCooker/blob/master/doc/api/Ling/ClassCooker/ClassCooker.md) &ndash; The ClassCooker class.
    - [ClassCooker::create](https://github.com/lingtalfi/ClassCooker/blob/master/doc/api/Ling/ClassCooker/ClassCooker/create.md) &ndash; Creates a new instance of this class, and returns it.
    - [ClassCooker::setFile](https://github.com/lingtalfi/ClassCooker/blob/master/doc/api/Ling/ClassCooker/ClassCooker/setFile.md) &ndash; Sets the file to work with.
    - [ClassCooker::addContent](https://github.com/lingtalfi/ClassCooker/blob/master/doc/api/Ling/ClassCooker/ClassCooker/addContent.md) &ndash; Adds a string to the class.
    - [ClassCooker::addMethod](https://github.com/lingtalfi/ClassCooker/blob/master/doc/api/Ling/ClassCooker/ClassCooker/addMethod.md) &ndash; Adds the given method(s) to a class if it doesn't exist.
    - [ClassCooker::addProperty](https://github.com/lingtalfi/ClassCooker/blob/master/doc/api/Ling/ClassCooker/ClassCooker/addProperty.md) &ndash; Adds a property to the current class.
    - [ClassCooker::addUseStatements](https://github.com/lingtalfi/ClassCooker/blob/master/doc/api/Ling/ClassCooker/ClassCooker/addUseStatements.md) &ndash; Adds the given use statement(s) to the class, if it doesn't exist.
    - [ClassCooker::getMethodContent](https://github.com/lingtalfi/ClassCooker/blob/master/doc/api/Ling/ClassCooker/ClassCooker/getMethodContent.md) &ndash; Returns the method content, by default including the signature and the wrapping curly brackets.
    - [ClassCooker::getMethodSignature](https://github.com/lingtalfi/ClassCooker/blob/master/doc/api/Ling/ClassCooker/ClassCooker/getMethodSignature.md) &ndash; Returns the given method' signature, or false if the method doesn't exist.
    - [ClassCooker::getClassName](https://github.com/lingtalfi/ClassCooker/blob/master/doc/api/Ling/ClassCooker/ClassCooker/getClassName.md) &ndash; Returns the class name of the current class (i.e.
    - [ClassCooker::getMethods](https://github.com/lingtalfi/ClassCooker/blob/master/doc/api/Ling/ClassCooker/ClassCooker/getMethods.md) &ndash; Return an array of all the method signatures of the current class.
    - [ClassCooker::getMethodBoundariesByName](https://github.com/lingtalfi/ClassCooker/blob/master/doc/api/Ling/ClassCooker/ClassCooker/getMethodBoundariesByName.md) &ndash; Get the boundaries for a given method.
    - [ClassCooker::getMethodsBoundaries](https://github.com/lingtalfi/ClassCooker/blob/master/doc/api/Ling/ClassCooker/ClassCooker/getMethodsBoundaries.md) &ndash; Proxy to the [ClassCookerHelper::getMethodsBoundaries](https://github.com/lingtalfi/ClassCooker/blob/master/doc/api/Ling/ClassCooker/Helper/ClassCookerHelper/getMethodsBoundaries.md) method.
    - [ClassCooker::hasMethod](https://github.com/lingtalfi/ClassCooker/blob/master/doc/api/Ling/ClassCooker/ClassCooker/hasMethod.md) &ndash; Returns whether the class contains the given method.
    - [ClassCooker::getMethodsBasicInfo](https://github.com/lingtalfi/ClassCooker/blob/master/doc/api/Ling/ClassCooker/ClassCooker/getMethodsBasicInfo.md) &ndash; Returns an array of propertyName => informationItem about the class methods, in the order they appear in the class file.
    - [ClassCooker::getClassStartLine](https://github.com/lingtalfi/ClassCooker/blob/master/doc/api/Ling/ClassCooker/ClassCooker/getClassStartLine.md) &ndash; Returns the number of the start line of the class.
    - [ClassCooker::getClassFirstEmptyLine](https://github.com/lingtalfi/ClassCooker/blob/master/doc/api/Ling/ClassCooker/ClassCooker/getClassFirstEmptyLine.md) &ndash; Returns the number of the first empty line found after the class declaration, or false if there is no empty line.
    - [ClassCooker::getClassLastLineInfo](https://github.com/lingtalfi/ClassCooker/blob/master/doc/api/Ling/ClassCooker/ClassCooker/getClassLastLineInfo.md) &ndash; Returns an array containing information related to the end of the class.
    - [ClassCooker::hasProperty](https://github.com/lingtalfi/ClassCooker/blob/master/doc/api/Ling/ClassCooker/ClassCooker/hasProperty.md) &ndash; Returns whether the current class contains the given property.
    - [ClassCooker::hasUseStatement](https://github.com/lingtalfi/ClassCooker/blob/master/doc/api/Ling/ClassCooker/ClassCooker/hasUseStatement.md) &ndash; Returns whether the current class contains an use statement which references the given useStatementClass.
    - [ClassCooker::removeMethod](https://github.com/lingtalfi/ClassCooker/blob/master/doc/api/Ling/ClassCooker/ClassCooker/removeMethod.md) &ndash; or does nothing and returns false if the method was not found.
    - [ClassCooker::updateMethodContent](https://github.com/lingtalfi/ClassCooker/blob/master/doc/api/Ling/ClassCooker/ClassCooker/updateMethodContent.md) &ndash; Updates the inner content of a method, using a callable.
    - [ClassCooker::updatePropertyComment](https://github.com/lingtalfi/ClassCooker/blob/master/doc/api/Ling/ClassCooker/ClassCooker/updatePropertyComment.md) &ndash; Updates the [docblock comment](https://github.com/lingtalfi/TheBar/blob/master/discussions/docblock-comment.md) of the given property (if there is one), using the given callable.
- [ClassCookerException](https://github.com/lingtalfi/ClassCooker/blob/master/doc/api/Ling/ClassCooker/Exception/ClassCookerException.md) &ndash; The ClassCookerException class.
- [ClassCookerHelper](https://github.com/lingtalfi/ClassCooker/blob/master/doc/api/Ling/ClassCooker/Helper/ClassCookerHelper.md) &ndash; The ClassCookerHelper class.
    - [ClassCookerHelper::createSectionComment](https://github.com/lingtalfi/ClassCooker/blob/master/doc/api/Ling/ClassCooker/Helper/ClassCookerHelper/createSectionComment.md) &ndash; Creates a section comment.
    - [ClassCookerHelper::getSectionLineNumber](https://github.com/lingtalfi/ClassCooker/blob/master/doc/api/Ling/ClassCooker/Helper/ClassCookerHelper/getSectionLineNumber.md) &ndash; Returns the number of the line (in the file) containing the beginning of the [section comment](https://github.com/lingtalfi/TheBar/blob/master/discussions/section-comment.md), or false if the section wasn't found.
    - [ClassCookerHelper::getMethodsBoundaries](https://github.com/lingtalfi/ClassCooker/blob/master/doc/api/Ling/ClassCooker/Helper/ClassCookerHelper/getMethodsBoundaries.md) &ndash; Returns an array of method => [startLine, endLine].


Dependencies
============
- [Bat](https://github.com/lingtalfi/Bat)
- [TokenFun](https://github.com/lingtalfi/TokenFun)


