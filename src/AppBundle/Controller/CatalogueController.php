<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Categorie;
use AppBundle\Entity\Note;
use AppBundle\Form\NoteType;
use Exception;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

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

        $note = new Note();
        $note->setProduit($produit);

        $form = $this->createForm(NoteType::class, $note);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $session = $this->get('session');

            try {
                $em->persist($note);
                $em->flush();
                $session->getFlashBag()
                        ->add('succes', 'Noté');
                return $this->redirectToRoute('detail_catalogue', ['id' => $id]);
            } catch (Exception $e) {
                $session->getFlashBag()
                        ->add('erreur', 'Non noté' . $e->getMessage() . $e->getFile());
            }
        }

        return $this->render('catalogue/detail.html.twig', ['produit' => $produit, 'form' => $form->createView()]);
    }

    /**
     *
     * @Route("/ajax_note_catalogue", name="ajax_note_catalogue")
     *
     *
     */
    public function ajouterAjaxNoteAction(Request $request) {

        if ($request->isXmlHttpRequest()) {
            $id = $request->request->get('id');
            $valeur = $request->request->get('valeur');

            $em = $this->getDoctrine()->getManager();
            $pr = $em->getRepository('AppBundle:Produit');
            $produit = $pr->find($id);

            $note = new Note();
            $note->setProduit($produit);
            $note->setValeur($valeur);
            $em->persist($note);

            try {
                $em->flush();
                return new JsonResponse(
                        ['success' => true,
                    'note' => ['id' => $note->getId(),
                        'valeur' => $note->getValeur(),
                    ]
                ]);
            } catch (Exception $e) {
                return new JsonResponse(['success' => false]);
            }
        } else {
            throw new HttpException(403);
        }
    }

    /**
     *
     * @Route("/ajax_nvx_prod", name="ajax_nvx_prod")
     *
     *
     */
    public function derniersProduitsAction(Request $request) {

        if ($request->isXmlHttpRequest()) {

            $em = $this->getDoctrine()->getManager();
            $pr = $em->getRepository('AppBundle:Produit');
            $produits = $pr->getLatestProduit(3);

            return $this->render('catalogue/ajax_new.html.twig', ['produits' => $produits]);
        } else {
            throw new HttpException(403);
        }
    }

}
