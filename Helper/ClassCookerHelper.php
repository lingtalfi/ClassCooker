<?php


namespace ClassCooker\Helper;


class ClassCookerHelper
{


    //--------------------------------------------
    // THIS IS A SECTION
    //--------------------------------------------
    /**
     *
     * A section is a special type of comment written on 3 lines, it looks like the one just above this comment.
     * It's easier to find a section if your section label contains only alpha-numeric chars (see the source code
     * of this method to understand why).
     *
     *
     *
     * @param $sectionLabel
     * @param $file
     * @return false|int, return the number of the line (in the file) of the beginning of the section comment,
     *          or false if the section wasn't found
     */
    public static function getSectionLineNumber($sectionLabel, $file)
    {
        $lines = file($file);


        $patternLine = '!//--------------------------------------------!';
        $pattern2 = '!//\s*' . $sectionLabel . '!'; // we want the user to have regex power if needed, so no escaping here
        $n = 1;
        $match1 = false;
        $match2 = false;
        foreach ($lines as $line) {
            if (false === $match1 && preg_match($patternLine, $line)) {
                $match1 = true;
            } elseif (true === $match1 && false === $match2 && preg_match($pattern2, $line)) {
                $match2 = true;
            } elseif (true === $match1 && true === $match2 && preg_match($patternLine, $line)) {
                return $n - 2;
            }
            $n++;
        }
        return false;
    }
}