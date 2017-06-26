<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace AppBundle\Service;

/**
 * Description of ExtraitWithLink
 *
 * @author Human Booster
 */
class ExtraitWithLink {

    private $router;
    private $max;

    public function __construct(\Symfony\Component\Routing\Router $router, $max) {
        $this->router = $router;
        $this->max = $max;
    }

    public function get(\AppBundle\Entity\Article $article) {

        $text = strip_tags($article->getContenu());

        if (strlen($text) >= $this->max) {
            $text = substr($text, 0, $this->max);
            $text = substr($text, 0, strrpos($text, " "));
        }

        $url = $this->router->generate('detail_blog', ['id' => $article->getId()]);

        $text .= ' <a href="' . $url . '"Lire la suite</a>';

        return $text;
    }

}
