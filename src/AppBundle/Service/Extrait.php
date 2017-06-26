<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace AppBundle\Service;

/**
 * Description of Extrait
 *
 * @author Human Booster
 */
class Extrait {

    private $session;
    private $max;
    private $suite;

    public function __construct(\Symfony\Component\HttpFoundation\Session\Session $session, $max, $suite) {
        $this->session = $session;
        $this->max = $max;
        $this->suite = $suite;
    }

    public function get($text) {
        $text = strip_tags($text);
        if (strlen($text) >= $this->max) {
            $text = substr($text, 0, $this->max);
            $text = substr($text, 0, strrpos($text, " ")) . $this->suite;
        }
        $this->session->getFlashBag()->add('succes', 'Extrait ok');

        return $text;
    }

}
