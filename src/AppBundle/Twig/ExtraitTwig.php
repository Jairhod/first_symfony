<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace AppBundle\Twig;

/**
 * Description of ExtraitTwig
 *
 * @author Human Booster
 */
class ExtraitTwig extends \Twig_Extension {

    private $max;
    private $suite;

    public function __construct($max, $suite) {
        $this->max = $max;
        $this->suite = $suite;
    }

    public function get($text) {
        $text = strip_tags($text);
        if (strlen($text) >= $this->max) {
            $text = substr($text, 0, $this->max);
            $text = substr($text, 0, strrpos($text, " ")) . $this->suite;
        }
        return $text;
    }

    public function getFunctions() {

        return array(new \Twig_SimpleFunction('extrait', array($this, 'get')));
    }

    public function getFilters() {
        return array(
            new \Twig_SimpleFilter('extrait', array($this, 'get')));
    }

}
