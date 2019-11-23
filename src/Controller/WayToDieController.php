<?php

namespace App\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\WayToDie;

class WayToDieController extends AppController
{
    /**
     * Liste des façons de mourir
     *
     * @Route("/way_to_die/liste/{page}", name="way_to_die_liste")
     *
     * @param int $page
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function liste(int $page = 1)
    {
        $repository = $this->getDoctrine()->getRepository(WayToDie::class);

        $nbr_max = $this->getNbrMax();
        $paginator = $repository->findAllElements($page, $nbr_max);
        $way_to_dies = $paginator['paginator'];

        $pagination = array(
            'page' => $page,
            'total' => $paginator['nombre'],
            'nbPages' => ceil($paginator['nombre'] / $nbr_max),
            'route' => 'way_to_die_liste',
            'route_params' => array()
        );

        $paths = array(
            'home' => $this->homeURL(),
            'paths' => array(),
            'active' => 'Façons de mourir'
        );

        return $this->render('way_to_die/index.html.twig', array(
            'way_to_dies' => $way_to_dies,
            'pagination' => $pagination,
            'paths' => $paths
        ));
    }
}
