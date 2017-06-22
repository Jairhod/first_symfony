<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Categorie;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/catalogue")
 */
class CatalogueController extends Controller {

    /**
     * @Route("/", name="catalogue_homepage")
     *
     *
     */
    public function indexAction(Request $request) {

        $cm = $this->getDoctrine()->getManager()->getRepository('AppBundle:Categorie');

        $categories = $cm->findBy([], ['titre' => 'ASC']);

        return $this->render('catalogue/index.html.twig', ['categories' => $categories]);
    }

    /**
     * @Route("/categorie/{id}", name="categorie_catalogue",
     *
     * requirements={"id": "\d+"})
     *
     */
    public function categorieAction(Categorie $cat) {

        $pm = $this->getDoctrine()->getManager()->getRepository('AppBundle:Produit');
        $produits = $pm->getProduitsByCategorie($cat);

        return $this->render('catalogue/categorie.html.twig', ['produits' => $produits, 'cat' => $cat]);
    }

    /**
     * @Route("/detail/{id}", name="detail_catalogue",
     *
     * requirements={"id": "\d+"})
     *
     */
    public function detailAction(Request $request, $id) {

        $em = $this->getDoctrine()->getManager();

        $pr = $em->getRepository('AppBundle:Produit');

        $produit = $pr->getProduitByIdWithJoin($id);

        $note = new \AppBundle\Entity\Note();
        $note->setProduit($produit);

        $form = $this->createForm(\AppBundle\Form\NoteType::class, $note);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $session = $this->get('session');

            try {
                $em->persist($note);
                $em->flush();
                $session->getFlashBag()
                        ->add('succes', 'Noté');
                return $this->redirectToRoute('detail_catalogue', ['id' => $id]);
            } catch (\Exception $e) {
                $session->getFlashBag()
                        ->add('erreur', 'Non noté' . $e->getMessage() . $e->getFile());
            }
        }

        return $this->render('catalogue/detail.html.twig', ['produit' => $produit, 'form' => $form->createView()]);
    }

}
