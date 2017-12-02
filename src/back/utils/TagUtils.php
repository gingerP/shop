<?php
/**
 * Created by PhpStorm.
 * User: vinni
 * Date: 5/4/14
 * Time: 1:13 PM
 */

include_once("src/back/import/import");
include_once("src/back/import/tag");

class TagUtils {

    public static function buildHtml($tag, Num $depth) {
        try {
            $tabs = "";//implode("", array_fill(0, $depth->_inc()->getNum(), "  "));
            $html = "";
            if ($tag instanceof Tag) {
                $beginTag = "$tabs<".$tag->getTagName();
                $tagContent = "";
                $endTag = "$tabs</".$tag->getTagName().">";
                $id = "";
                $class = "";
                $attributes = "";
                if (!Utils::isNullOrEmptyString($tag->getId())) {
                    $id = TagLabels::ID."='".$tag->getId()."'";
                }
                if (count($tag->getClassList()) > 0) {
                    $class = TagLabels::_CLASS."='";
                    for($index = 0, $max = count($tag->getClassList()); $index < $max; $index++) {
                        $class.= $tag->getClassList()[$index]." ";
                    }
                    $class.="'";
                }
                if (count($tag->getAttributeList()) > 0) {
                    foreach($tag->getAttributeList() as $key => $value) {
                        $attributes .= " $key=\"$value\" ";
                    }
                }
                $beginTag .= " ".$id
                            ." ".$class
                            ." ".$attributes
                            ." >";

                if (!($tag instanceof SingleTag)) {
                    for($index = 0, $max = count($tag->getChildList()); $index < $max; $index++) {
                        $tagContent .= TagUtils::buildHtml($tag->getChildList()[$index], $depth);
                        $depth->_dec();
                    }
                    $html = $beginTag.$tagContent.$endTag;
                } else {
                    $html = $beginTag;
                }
            } else if (is_string($tag) || is_numeric($tag)) {
                return $tag;
            }
        } catch (Exception $e) {
            log::info("Caught exception: ',  $e->getMessage()");
        }
        return $html;
    }

    public static function createShadow(&$mainDiv) {

    }

    public static function createNote($text, $link) {
        $note = new Div();
        $note->addStyleClass("note");
        $note->addChild($text);
        if (strlen($link) > 0) {
            $note->addAttribute("href", $link);
            $note->addStyleClasses(["hover_text_underline"]);
        } else {
            $note->addStyleClasses(["cursor_default"]);
        }
        return $note;
    }

    public static function createList($arr) {
        if (is_array($arr) && count($arr) > 0) {
            $mainTag = new Ul();
            for ($arrIndex = 0; $arrIndex < count($arr); $arrIndex++) {
                $li = new Li();
                $li->addChild($arr[$arrIndex]);
                $mainTag->addChild($li);
            }
            return $mainTag;
        }
        return "";
    }

} 