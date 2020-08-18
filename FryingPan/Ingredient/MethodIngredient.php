<?php


namespace Ling\ClassCooker\FryingPan\Ingredient;


use Ling\Bat\CommentTool;

/**
 * The MethodIngredient class.
 *
 * This class will add a method to the class we're working on.
 *
 * The method will be added using the same heuristics and options as the @page(ClassCooker->addMethod) method.
 *
 *
 *
 */
class MethodIngredient extends BaseIngredient
{


    /**
     * @implementation
     */
    public function execute()
    {
        list($methodName, $options) = $this->valueInfo;
        $addAsComment = $options['addAsComment'] ?? false;
        $cooker = $this->fryingPan->getCooker();
        $className = $cooker->getClassName();


        $hasMethod = false;
        if (true === $cooker->hasMethod($methodName)) {
            $hasMethod = true;
            if (false === $addAsComment) {
                $this->fryingPan->sendToLog("The method \"$methodName\" is already found in class \"$className\".", 'skip');
            } else {
                $this->fryingPan->sendToLog("The method \"$methodName\" was found in class \"$className\", will try add it as comments.", 'skip');
            }
        }


        if (false === $hasMethod ||
            (true === $hasMethod && true === $addAsComment)
        ) {
            if (array_key_exists('template', $options)) {
                $template = $options['template'];


                $sAsComment = '';
                if (true === $hasMethod) {
                    $sAsComment = ' as comment';
                    $template = CommentTool::comment($template) . PHP_EOL; // the php_eol here is crucial
                }


                $this->fryingPan->sendToLog("Adding method \"$methodName\" to class \"$className\"$sAsComment.", 'add');

                unset($options['template']);
                $options['throwEx'] = false;
                $options['checkDuplicate'] = false;
                $cooker->addMethod($methodName, $template, $options);


            } else {
                $this->fryingPan->sendToLog("template option not found for the MethodIngredient.", 'error');
            }
        }


    }


}