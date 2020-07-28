<?php


namespace Ling\ClassCooker\FryingPan\Ingredient;


/**
 * The ParentIngredient class.
 *
 * This class will add a parent to the class we're working on.
 *
 * If the class already extends another parent, then we will abort (we don't override the user's previous work),
 * but we will send a warning via the log system.
 *
 *
 * The value is the symbol representing the class parent.
 * The symbol is what immediately following the "extends" keyword, it references the class parent.
 *
 *
 *
 */
class ParentIngredient extends BaseIngredient
{


    /**
     * @implementation
     */
    public function execute()
    {
        list($symbol, $options) = $this->valueInfo;
        $cooker = $this->fryingPan->getCooker();


        $classParent = $cooker->getParentSymbol();
        if (false !== $classParent && $symbol !== $classParent) {
            $this->fryingPan->sendToLog("The \"$symbol\" class was not added (the class already has a parent). Consider adding the parent manually.", 'warning');
        } else {

            /**
             * Add this before, otherwise the php code won't be valid
             */
            if (array_key_exists('useStatement', $options)) {
                $useStatementClass = $options['useStatement'];
                $ingredient = UseStatementIngredient::create();
                $ingredient->setFryingPan($this->fryingPan);
                $ingredient->setValue($useStatementClass)->execute();
            }
            $cooker->addParentClass($symbol);

        }
    }


}