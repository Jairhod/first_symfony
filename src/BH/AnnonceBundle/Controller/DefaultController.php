<?php

namespace BH\AnnonceBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

/**
 * @Route("/france")
 */
class DefaultController extends Controller {

    /**
     * @Route("/listing")
     */
    public function indexAction() {
        return $this->render('BHAnnonceBundle:Default:index.html.twig');
    }

}
