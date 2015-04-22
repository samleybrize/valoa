<?php

namespace Samleybrize\Valoa;

class AnnotationParser
{
    // TODO
    public function parse($docComment)
    {
        $matchedAnnotations = array();
        $hasAnnotations     = preg_match_all(
            "#@(\w+)(?:\s*(?:\(\s*)?(.*?)(?:\s*\))?)??\s*(?:\n|\*/)#",
            $docComment,
            $matchedAnnotations,
            PREG_SET_ORDER
        );

        if (!$hasAnnotations) {
            return array();
        }

        // parse matched annotations
        $annotationList = array();

        foreach ($matchedAnnotations as $matchedAnnotation) {
            $name   = strtolower($matchedAnnotation[1]);
            $val    = true;

            // process annotation value, if any
            if (array_key_exists(2, $matchedAnnotation)) {
                $params     = array();
                $hasParams  = preg_match_all('#(\w+)\s*=\s*(\[[^\]]*\]|"[^"]*"|[^,)]*)\s*(?:,|$)#', $matchedAnnotation[2], $params, PREG_SET_ORDER);

                if ($hasParams) {
                    // process params (eg: azerty = 48, qwerty = 's')
                    $val = array();

                    foreach ($params as $param) {
                        $val[$param[1]] = $this->parseValue($param[2]);
                    }
                } else {
                    // simple value
                    $val = trim($matchedAnnotation[2]);
                    $val = empty($val) ? true : $this->parseValue($val);
                }
            }

            // adds annotation to the list
            if (!array_key_exists($name, $annotationList)) {
                $annotationList[$name] = array();
            }

            $annotationList[$name][] = $val;
        }

        return $annotationList;
    }

    // TODO
    private function parseValue($value)
    {
        $val        = trim($value);
        $firstChar  = substr($val, 0, 1);
        $lastChar   = substr($val, -1);

        if ("[" === $firstChar && "]" === $lastChar) {
            // array values
            $vals   = explode(',', substr($val, 1, -1));
            $val    = array();

            foreach ($vals as $v) {
                $val[] = $this->parseValue($v);
            }

            return $val;
        } elseif ('"' === $firstChar && '"' === $lastChar) {
            // quoted value
            $val = substr($val, 1, -1);
            return $this->parseValue($val);
        } else if ("true" === strtolower($val)) {
            // boolean true
            return true;
        } else if ("false" === strtolower($val)) {
            // boolean false
            return false;
        } else if (is_numeric($val)) {
            // numeric value
            if ((float) $val == (int) $val) {
                return (int) $val;
            } else {
                return (float) $val;
            }
        }

        return $val;
    }
}
