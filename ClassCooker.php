<?php


namespace Ling\ClassCooker;


use Ling\Bat\ClassTool;
use Ling\Bat\FileTool;
use Ling\ClassCooker\Exception\ClassCookerException;
use Ling\ClassCooker\Helper\ClassCookerHelper;
use Ling\TokenFun\TokenFinder\Tool\TokenFinderTool;

class ClassCooker
{

    private $file;

    public static function create()
    {
        return new static();
    }

    public function setFile($file)
    {
        $this->file = $file;
        return $this;
    }


    //--------------------------------------------
    //
    //--------------------------------------------

    /**
     * Adds a method to a class if it doesn't exist
     */
    public function addMethod($methodName, $content, array $options = [])
    {


        $methods = $this->getMethodsBoundaries();
        $nbMethod = count($methods);
        if (false === array_key_exists($methodName, $methods)) {

            $lines = $this->getLines();

            if ($nbMethod > 0) {

                $lastMethodInfo = array_pop($methods);
                list($startLine, $endLine) = $lastMethodInfo;
                $lineNumber = $endLine;

            } else {
                $nbLines = count($lines);
                $index = $nbLines - 1;

                while ($index >= 0) {
                    $line = $lines[$index];
                    if ('}' === trim($line)) {
                        break;
                    }
                    $index--;
                }
                $lineNumber = $index;
            }

            $sliceOne = array_slice($lines, 0, $lineNumber);
            $sliceTwo = array_slice($lines, $lineNumber);
            $sliceOneContent = implode("", $sliceOne);
            $sliceTwoContent = implode("", $sliceTwo);
            $c = $sliceOneContent . PHP_EOL . $content . $sliceTwoContent;
            return file_put_contents($this->file, $c);
        } else {
            return true;
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
     * @param string|array $useStatement
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
     * Get the method content, by default including the signature and the wrapping curly brackets
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
     * Get the method names
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
     * See getMethodsBoundaries for more info.
     */
    public function getMethodBoundariesByName($methodName)
    {
        $boundaries = $this->getMethodsBoundaries();
        if (array_key_exists($methodName, $boundaries)) {
            return $boundaries[$methodName];
        }
        return false;
    }


    /**
     * This method will get the startLine and endLine number of every methods it finds.
     * However, in order for this method to work correctly, the class needs to be formatted in a certain way:
     *
     * - there must be only one class in the file
     * - the class ends with a proper } (end curly bracket) on its own line (possibly surrounded with whitespaces)
     * - the method signature is on its own line, and only one line (not split in multiple lines)
     * - a method ends with a proper } (end curly bracket) on its own line (possibly surrounded with whitespaces)
     *
     *
     * $signatureTags: array of desired tags, a tag can be one of the following:
     *                      - public
     *                      - protected
     *                      - private
     *                      - static
     *
     *
     *
     * @return array of method => [startLine, endLine]
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
     * or does nothing if the method was not found
     */
    public function removeMethod($methodName)
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


    public function updateMethodContent($methodName, callable $updator)
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
    protected function error($msg)
    {
        throw new ClassCookerException($msg);
    }


    //--------------------------------------------
    //
    //--------------------------------------------
    private function getLines()
    {
        if (file_exists($this->file)) {
            return file($this->file);
        }
        throw new ClassCookerException("file not found: " . $this->file);
    }


    private function getTagsByLine($line)
    {
        $p = explode('function', $line, 2);
        $tags = explode(' ', $p[0]);
        $tags = array_filter(array_map(function ($v) {
            $v = trim(strtolower($v));
            return $v;
        }, $tags));
        return $tags;
    }

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
     * @param array $slice
     * @param array $originalWrappers , will contain three keys: first, next, and last
     * @return string
     */
    private function getInnerContentByMethodSlice(array $slice, array &$originalWrappers = [])
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