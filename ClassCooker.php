<?php


namespace Ling\ClassCooker;


use Ling\Bat\ClassTool;
use Ling\Bat\FileTool;
use Ling\ClassCooker\Exception\ClassCookerException;
use Ling\ClassCooker\Helper\ClassCookerHelper;
use Ling\TokenFun\TokenFinder\Tool\TokenFinderTool;

/**
 * The ClassCooker class.
 */
class ClassCooker
{

    /**
     * Path to the file containing the class.
     *
     * @var string
     */
    private $file;


    /**
     * Creates a new instance of this class, and returns it.
     *
     * @return static
     */
    public static function create()
    {
        return new static();
    }

    /**
     * Sets the file to work with.
     *
     * @param $file
     * @return $this
     */
    public function setFile($file)
    {
        $this->file = $file;
        return $this;
    }


    //--------------------------------------------
    //
    //--------------------------------------------
    /**
     * Adds a string to the class.
     * The string can be any string that fits in a class: a method, multiple methods, a property, some comments, etc...
     *
     * By default, the string is appended at the end of the class.
     * You can define the location where you want to add the string with the options.
     *
     *
     * Available options are:
     * - afterMethod: string, the method after which to append the string
     * - beforeMethod: string, the method before which to append the string
     * - afterProperty: string, the property after which to append the string
     * - beforeProperty: string, the property before which to append the string
     * - classStart: bool=false, the string will be appended at the beginning of the class
     *
     *
     * Note: in most of the cases, you want the content to end up with the PHP_EOL char,
     * otherwise this might lead to unexpected/weird results.
     *
     *
     *
     * @param string $content
     * @param array $options
     */
    public function addContent(string $content, array $options = [])
    {

        $insertLine = null;
        if (array_key_exists("afterMethod", $options)) {
            $method = $options['afterMethod'];
            $methods = $this->getMethodsBasicInfo();
            if (false === array_key_exists($method, $methods)) {
                $this->error("The method \"$method\" was not found in this class.");
            }
            $methodInfo = $methods[$method];
            $insertLine = $methodInfo['endLine'] + 1;
        } elseif (array_key_exists("beforeMethod", $options)) {
            $method = $options['beforeMethod'];
            $methods = $this->getMethodsBasicInfo();
            if (false === array_key_exists($method, $methods)) {
                $this->error("The method \"$method\" was not found in this class.");
            }
            $methodInfo = $methods[$method];
            $insertLine = $methodInfo['startLine'];
        } elseif (array_key_exists("afterProperty", $options)) {
            $property = $options['afterProperty'];
            $properties = TokenFinderTool::getClassPropertyBasicInfo($this->getClassName());
            if (false === array_key_exists($property, $properties)) {
                $this->error("The property \"$property\" was not found in this class.");
            }
            $propertyInfo = $properties[$property];
            $insertLine = $propertyInfo['endLine'] + 1;

        } elseif (array_key_exists("beforeProperty", $options)) {
            $property = $options['beforeProperty'];
            $properties = TokenFinderTool::getClassPropertyBasicInfo($this->getClassName());
            if (false === array_key_exists($property, $properties)) {
                $this->error("The property \"$property\" was not found in this class.");
            }
            $propertyInfo = $properties[$property];
            $startLine = $propertyInfo['commentStartLine'];
            if (false === $startLine) {
                $startLine = $propertyInfo['startLine'];
            }
            $insertLine = $startLine;

        } elseif (array_key_exists("classStart", $options) && true === $options['classStart']) {
            $properties = TokenFinderTool::getClassPropertyBasicInfo($this->getClassName());

            if ($properties) { // insert before first property...

                $firstProperty = array_shift($properties);
                $startLine = $firstProperty['commentStartLine'];
                if (false === $startLine) {
                    $startLine = $firstProperty['startLine'];
                }
                $insertLine = $startLine;
            } else {
                $methods = $this->getMethodsBasicInfo();
                if ($methods) // ...or else insert before first method...
                {
                    $methodInfo = array_shift($methods);
                    $insertLine = $methodInfo['startLine'];
                } else { // ...or else insert before the class end
                    $lastInfo = $this->getClassLastLineInfo();
                    $insertLine = $lastInfo['endLine'];
                }
            }
        } else {
            $lastInfo = $this->getClassLastLineInfo();
            $insertLine = $lastInfo['endLine'];
        }


        FileTool::insert($insertLine, $content, $this->file);
    }

    /**
     * Adds the given method(s) to a class if it doesn't exist.
     *
     * By default, it's appended at the end of the class, but you can decide to put it after a given method, using
     * the afterMethod option.
     *
     * By default if the method already exists, an exception will be thrown.
     * You can change this behaviour using the throwEx option.
     *
     *
     * Available options are:
     * - afterMethod: string, the name of the method after which you wish to add the new method
     * - throwEx: bool=true, whether to throw an exception if the given methodName already exists in the class.
     *      If false and the method already exists, the method will return false.
     *
     *
     *
     *
     * @param $methodName
     * @param $content
     * @param array $options
     * @return false|void
     * @throws \Exception
     */
    public function addMethod($methodName, $content, array $options = [])
    {

        if (true === $this->hasMethod($methodName)) {
            $throwEx = $options['throwEx'] ?? true;
            if (true === $throwEx) {
                $this->error("The method \"$methodName\" already exists in the class.");
            }
            return false;
        }
        $this->addContent($content, $options);
    }


    /**
     *
     * Adds a property to the current class.
     *
     * If the property already exists, an exception will be thrown.
     * By default, the property is written below the last property if any.
     * If not, it's written before the first method of the class if any.
     * If not, it's added at the beginning of the class (just after the class declaration).
     *
     *
     * But you can define a property as a target (using the afterProperty option), in which case
     * we will write the new property immediately after that target.
     *
     *
     * Available options are:
     * - afterProperty: string, the name of the property to use as a target (the new property will be written after it)
     *
     *
     *
     *
     * @param string $name
     * @param string $content
     * @param array $options
     * @throws \Exception
     */
    public function addProperty(string $name, string $content, array $options = [])
    {

        $className = $this->getClassName();
        $classProps = TokenFinderTool::getClassPropertyBasicInfo($className);
        if (array_key_exists($name, $classProps)) {
            $this->error("The property \"$name\" already exists in that class.");
        }


        $classMethods = $this->getMethodsBasicInfo();
        $classFirstEmptyLine = $this->getClassFirstEmptyLine();
        $afterProperty = $options['afterProperty'] ?? null;

        if (null === $afterProperty) {
            if ($classProps) {
                $lastProp = array_pop($classProps);
                $number = $lastProp['endLine'] + 1;
                FileTool::insert($number, $content, $this->file);
            } elseif ($classMethods) {
                $firstMethod = array_shift($classMethods);
                $line = $firstMethod['startLine'];
                FileTool::insert($line, $content, $this->file);
            } else {
                $line = $classFirstEmptyLine;
                if (false === $line) {
                    $info = $this->getClassLastLineInfo();
                    $line = $info['endLine'] - 1;
                }
                FileTool::insert($line, $content, $this->file);
            }

        } else {
            if (array_key_exists($afterProperty, $classProps)) {
                $prop = $classProps[$afterProperty];
                $line = $prop['endLine'] + 1;
                FileTool::insert($line, $content, $this->file);
            } else {
                $this->error("The property \"$afterProperty\" was not found in that class ($className).");
            }
        }
    }


    /**
     * Adds the given use statement(s) to the class, if it doesn't exist.
     *
     * The statement must look like this (including the semi-colon at the end, but not the PHP_EOL at the very end):
     *
     * - use Ling\Light_Logger\LightLoggerService;
     *
     *
     * @param string|array $useStatements
     */
    public function addUseStatements($useStatements)
    {
        if (false === is_array($useStatements)) {
            $useStatements = [$useStatements];
        }


        $useStatementsInfo = ClassTool::getUseStatementsInfoByFile($this->file);
        if ($useStatementsInfo) {
            $lastStatement = array_pop($useStatementsInfo);
            $lineNumber = $lastStatement[1];
            $newContent = $lastStatement[0];
            $newContent .= implode(PHP_EOL, $useStatements) . PHP_EOL;
            FileTool::replace($this->file, $lineNumber, $lineNumber, $newContent);


        } else {
            /**
             * If there is no useStatement, we want to use a free line, below the namespace if any,
             * or if there is no namespace, we use the line before the class definition.
             *
             * If there is no class definition, we throw an exception.
             *
             */
            $lineNumber = ClassTool::getNamespaceLineNumberByFile($this->file);
            if (false !== $lineNumber) {
                $newContent = FileTool::getContent($this->file, $lineNumber, $lineNumber);
                $newContent .= implode(PHP_EOL, $useStatements) . PHP_EOL;
                FileTool::replace($this->file, $lineNumber, $lineNumber, $newContent);
            } else {
                $lineNumber = ClassTool::getClassStartLineByFile($this->file);
                $newContent = FileTool::getContent($this->file, $lineNumber, $lineNumber);
                $newContent = implode(PHP_EOL, $useStatements) . PHP_EOL . $newContent;
                FileTool::replace($this->file, $lineNumber, $lineNumber, $newContent);
            }
        }
    }


    /**
     * Returns the method content, by default including the signature and the wrapping curly brackets.
     *
     *
     * @param $methodName
     * @param bool $includeWrap
     * @return bool|string
     * @throws ClassCookerException
     */
    public function getMethodContent($methodName, $includeWrap = true)
    {
        if (false !== ($boundaries = $this->getMethodBoundariesByName($methodName))) {
            list($startLine, $endLine) = $boundaries;
            $this->checkBoundaries($startLine, $endLine);

            $lines = $this->getLines();
            $slice = array_slice($lines, $startLine - 1, $endLine - $startLine + 1);
            if (false === $includeWrap) {
                return $this->getInnerContentByMethodSlice($slice);
            }
            return implode("", $slice);
        }
        return false;
    }


    /**
     * Returns the given method' signature, or false if the method doesn't exist.
     *
     * @param $methodName
     * @return bool|string
     * @throws \Exception
     */
    public function getMethodSignature($methodName)
    {
        if (false !== ($content = $this->getMethodContent($methodName, true))) {
            $pattern = '!^.*function\s+[a-zA-Z0-9_]+\s*\(.*\)\s*{!U';
            if (preg_match($pattern, $content, $match)) {
                $line = trim($match[0]);
                $line = rtrim($line, '{');
                return trim($line);
            }
        }
        return false;
    }


    /**
     * Returns the class name of the current class (i.e. the first class found in the file this class is working on).
     *
     * @return string
     */
    public function getClassName(): string
    {
        return ClassTool::getClassNameByFile($this->file);
    }


    /**
     * Return an array of all the method signatures of the current class.
     *
     * @param array $signatureTags
     * @return array
     * @throws \Exception
     */
    public function getMethods(array $signatureTags = [])
    {
        $captureFunctionNamePattern = '!function\s+([a-zA-Z0-9_]+)\s*\(.*\)!';

        $lines = $this->getLines();
        $ret = [];
        $n = count($signatureTags);

        // first capture all method signatures, and all possible end brackets
        foreach ($lines as $line) {
            $line = trim($line);
            if (0 === strpos($line, '//')) {
                continue;
            }
            if (preg_match($captureFunctionNamePattern, $line, $match)) {
                $func = $match[1];
                $tags = $this->getTagsByLine($line);

                if ($n > 0) {
                    foreach ($signatureTags as $tag) {
                        if (false === in_array($tag, $tags, true)) {
                            continue 2;
                        }
                    }
                }
                $ret[] = $func;
            }
        }
        return $ret;
    }

    /**
     * Get the boundaries for a given method.
     *
     * See getMethodsBoundaries for more info.
     *
     * @param string $methodName
     * @return bool|mixed
     */
    public function getMethodBoundariesByName(string $methodName)
    {
        $boundaries = $this->getMethodsBoundaries();
        if (array_key_exists($methodName, $boundaries)) {
            return $boundaries[$methodName];
        }
        return false;
    }


    /**
     * Proxy to the @page(ClassCookerHelper::getMethodsBoundaries) method.
     * @param array $signatureTags
     * @return array
     * @throws \Exception
     */
    public function getMethodsBoundaries(array $signatureTags = [])
    {
        return ClassCookerHelper::getMethodsBoundaries($this->file, $signatureTags);
    }

    /**
     * Returns whether the class contains the given method.
     *
     *
     * @param string $method
     * @return bool
     */
    public function hasMethod(string $method): bool
    {
        return ClassTool::hasMethodByFile($this->file, $method);
    }

    /**
     *
     * Returns an array of propertyName => informationItem about the class methods, in the order they appear in the class file.
     *
     * Each information item is an array with the following structure:
     *
     * - name: string, the method name
     * - isPublic: bool
     * - isProtected: bool
     * - isPrivate: bool
     * - isStatic: bool
     * - isFinal: bool
     * - isAbstract: bool
     * - docComment: string|false, the doc comment's content if any, or false otherwise
     * - startLine: int, the line where the method starts, this includes the docComment if any
     * - endLine: int, the line where the method ends
     *
     *
     *
     *
     * @return array
     * @throws \Exception
     */
    public function getMethodsBasicInfo(): array
    {

        $ret = [];
        $className = $this->getClassName();
        $r = new \ReflectionClass($className);
        $methods = $r->getMethods();
        foreach ($methods as $method) {


            $startLine = $method->getStartLine();
            $startLineDocComment = $startLine;
            $docComment = $method->getDocComment();
            if (false !== $docComment) {
                $p = explode(PHP_EOL, $docComment);
                $startLineDocComment -= count($p);
            }

            $name = $method->getName();
//            $innerContent = $this->getMethodContent($name, false);

            $ret[$name] = [
                "name" => $name,
                "isPublic" => $method->isPublic(),
                "isProtected" => $method->isProtected(),
                "isPrivate" => $method->isPrivate(),
                "isStatic" => $method->isStatic(),
                "isFinal" => $method->isFinal(),
                "isAbstract" => $method->isAbstract(),
                "docComment" => $docComment,
                "startLine" => $startLineDocComment,
                "endLine" => $method->getEndLine(),
//                "innerContent" => $innerContent,
//                "startLineMethod" => $startLine,
            ];
        }


        return $ret;
    }


    /**
     * Returns the number of the start line of the class.
     *
     * @return int
     * @throws \Exception
     */
    public function getClassStartLine(): int
    {
        $r = new \ReflectionClass($this->getClassName());
        return $r->getStartLine();
    }

    /**
     * Returns the number of the first empty line found after the class declaration, or false if there is no empty line.
     *
     * Note: a line containing only white-spaces is considered empty.
     *
     *
     *
     * @return int|false
     */
    public function getClassFirstEmptyLine()
    {
        $r = new \ReflectionClass($this->getClassName());
        $startLine = $r->getStartLine();

        $lines = file($this->file);
        $classLines = array_slice($lines, $startLine);
        foreach ($classLines as $index => $line) {
            $line = trim($line);
            if ('' === $line) {
                return $startLine + $index + 1;
            }
        }
        return false;
    }


    /**
     * Returns an array containing information related to the end of the class.
     *
     * The returned array has the following structure:
     *
     *
     * - endLine: int, the number of the line containing the class declaration's last char
     * - lastLineContent: string, the content of the last line being part of the class declaration
     *
     *
     * @return array
     */
    public function getClassLastLineInfo(): array
    {
        $r = new \ReflectionClass($this->getClassName());
        $endLine = $r->getEndLine();
        $lastEmptyLine = false;


        $lines = file($this->file);
        $lastLineContent = $lines[$endLine - 1];
//        $classLines = array_slice($lines, 0, $endLine);
//        $classLines = array_reverse($classLines);
//        foreach ($classLines as $index => $line) {
//            $line = trim($line);
//            if ('' === $line) {
//                $lastEmptyLine = $endLine - $index;
//                break;
//            }
//        }

        return [
            "endLine" => $endLine,
//            "lastEmptyLine" => $lastEmptyLine,
            "lastLineContent" => $lastLineContent,
        ];
    }


    /**
     * Returns whether the current class contains the given property.
     *
     * @param string $propertyName
     * @return bool
     */
    public function hasProperty(string $propertyName): bool
    {
        return ClassTool::hasProperty($this->getClassName(), $propertyName);
    }

    /**
     * Returns whether the current class contains an use statement which references the given useStatementClass.
     *
     * Note: use statement aliases are ignored.
     *
     *
     * @param string $useStatementClass
     * @return bool
     */
    public function hasUseStatement(string $useStatementClass): bool
    {
        return ClassTool::hasUseStatementByFile($this->file, $useStatementClass);
    }

    /**
     * Remove the given method from the class,
     * or does nothing and returns false if the method was not found.
     *
     * @param string $methodName
     * @return false|int
     * @throws \Exception
     */
    public function removeMethod(string $methodName)
    {
        if (false !== ($boundaries = $this->getMethodBoundariesByName($methodName))) {
            list($startLine, $endLine) = $boundaries;
            $this->checkBoundaries($startLine, $endLine);

            $lines = $this->getLines();

            $sliceOne = array_slice($lines, 0, $startLine - 1);
            $sliceTwo = array_slice($lines, $endLine);

            $merge = array_merge($sliceOne, $sliceTwo);
            $newContent = implode("", $merge);
            return file_put_contents($this->file, $newContent);
        }
        return false;
    }


    /**
     * Updates the inner content of a method, using a callable.
     *
     * The callable signature is:
     * - fn ( string innerContent ): string
     *
     * It returns the updated method content.
     *
     *
     *
     * @param string $methodName
     * @param callable $updator
     * @return false|int
     * @throws \Exception
     */
    public function updateMethodContent(string $methodName, callable $updator)
    {
        if (false !== ($boundaries = $this->getMethodBoundariesByName($methodName))) {
            list($startLine, $endLine) = $boundaries;
            $this->checkBoundaries($startLine, $endLine);

            $lines = $this->getLines();

            $sliceOne = array_slice($lines, 0, $startLine - 1);
            $sliceTwo = array_slice($lines, $endLine);


            $slice = array_slice($lines, $startLine - 1, $endLine - $startLine + 1);
            $wrappers = [];
            $innerContent = $this->getInnerContentByMethodSlice($slice, $wrappers);

            $originalFirstLine = $wrappers['first'];
            $originalNextLine = $wrappers['next'];
            $originalLastLine = $wrappers['last'];


            $newInnerContent = call_user_func($updator, $innerContent);

            $sliceOneContent = implode("", $sliceOne);
            $sliceTwoContent = implode("", $sliceTwo);
            $content = $sliceOneContent
                . $originalFirstLine
                . $originalNextLine
                . $newInnerContent
                . $originalLastLine
                . $sliceTwoContent;


            return file_put_contents($this->file, $content);


        }
        return false;
    }


    /**
     * Updates the @page(docblock comment) of the given property (if there is one), using the given callable.
     *
     * The given callable takes the old comment as input, and must return the new comment.
     *
     * This method will return false if the property doesn't exist or if it doesn't have a block comment.
     *
     * Otherwise it returns true.
     *
     *
     * Available options are:
     * - guessExtraSpacing: bool=true, when the comment is extracted from its class, it's stripped.
     *      Therefore, when we paste it back in place, the whitespaces before and after the comment are removed and
     *      it results in an ugly file (although functional).
     *      To remedy this, this method makes a guess about what those whitespaces were, basically adding
     *      4 spaces before the comment, and a PHP_EOL after.
     *      You can disable this behaviour to have complete control over that extra-spacing.
     *
     *
     *
     *
     *
     * @param string $propertyName
     * @param array $options
     * @param callable $fn
     */
    public function updatePropertyComment(string $propertyName, callable $fn, array $options = [])
    {

        $guessExtraSpacing = $options['guessExtraSpacing'] ?? true;

        $className = $this->getClassName();
        $props = TokenFinderTool::getClassPropertyBasicInfo($className);
        if (array_key_exists($propertyName, $props)) {
            $prop = $props[$propertyName];
            if (true === $prop['hasDocComment']) {

                $oldComment = $prop['docComment'];
                $newComment = call_user_func($fn, $oldComment);


                if (true === $guessExtraSpacing) {
                    $newComment = '    ' . $newComment . PHP_EOL;
                }


                $commentStartLine = $prop['commentStartLine'];
                $commentEndLine = $prop['commentEndLine'];
                FileTool::replace($this->file, $commentStartLine, $commentEndLine, $newComment);
                return true;
            }
        }

        return false;
    }


    //--------------------------------------------
    //
    //--------------------------------------------
    /**
     * Throws an exception.
     *
     * @param $msg
     * @throws ClassCookerException
     */
    protected function error($msg)
    {
        throw new ClassCookerException($msg);
    }


    //--------------------------------------------
    //
    //--------------------------------------------
    /**
     * Returns an array containing the lines of the class file.
     *
     * @return array|false
     * @throws ClassCookerException
     */
    private function getLines()
    {
        if (file_exists($this->file)) {
            return file($this->file);
        }
        throw new ClassCookerException("file not found: " . $this->file);
    }


    /**
     * Returns the tags found in the given line.
     *
     * @param string $line
     * @return array
     */
    private function getTagsByLine(string $line): array
    {
        $p = explode('function', $line, 2);
        $tags = explode(' ', $p[0]);
        $tags = array_filter(array_map(function ($v) {
            $v = trim(strtolower($v));
            return $v;
        }, $tags));
        return $tags;
    }


    /**
     * Checks that the boundaries are safe to work with, and throws an exception if that's not the case.
     *
     * @param $startLine
     * @param $endLine
     * @return bool
     * @throws \Exception
     */
    private function checkBoundaries($startLine, $endLine)
    {
        if ($startLine > 0) {
            $lines = $this->getLines();
            $nbLines = count($lines);
            if ($endLine <= $nbLines) {
                return true;

            } else {
                $this->error("End line cannot exceed the number of lines of the file ($nbLines lines in file " . $this->file . ")");
            }
        } else {
            $this->error("Start line cannot be less than zero");
        }
        return false;
    }


    /**
     * Returns the inner content of a method by using a slice.
     *
     * @param array $slice
     * @param array $originalWrappers , will contain three keys: first, next, and last
     * @return string
     */
    private function getInnerContentByMethodSlice(array $slice, array &$originalWrappers = []): string
    {
        $innerContent = "";
        $sliceCopy = $slice;
        $firstLine = array_shift($sliceCopy);
        $lastLine = array_pop($sliceCopy);
        $originalFirstLine = $firstLine;
        $originalLastLine = $lastLine;
        $originalNextLine = "";


        if ('}' === trim($lastLine)) {
            $firstLine = trim($firstLine);
            if ('{' !== substr($firstLine, -1)) {
                $nextLine = array_shift($sliceCopy);
                $originalNextLine = $nextLine;
                $nextLine = trim($nextLine);
                if ('{' !== $nextLine) {
                    /**
                     * A method opening bracket must be either at the end of the signature,
                     * or on the next line alone on its line.
                     */
                    $this->error("Invalid class method formatting");
                }
            }
            $innerContent = implode('', $sliceCopy);
        } else {
            $this->error("Invalid class formatting");
        }
        $originalWrappers["first"] = $originalFirstLine;
        $originalWrappers["next"] = $originalNextLine;
        $originalWrappers["last"] = $originalLastLine;

        return $innerContent;
    }
}