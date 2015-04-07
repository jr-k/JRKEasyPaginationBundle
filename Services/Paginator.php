<?php

namespace JRK\EasyPaginationBundle\Services;

use Pagerfanta\Exception\OutOfRangeCurrentPageException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Security\Core\SecurityContext;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;
use Pagerfanta\View\TwitterBootstrapView;

class Paginator {

    private $request;
    private $router;

    public function __construct($requestStack, $router)
    {
        $this->request = $requestStack->getCurrentRequest();
        $this->router = $router;
    }

    // Return a block of entities from QueryBuilder, Metrics
    public function apiPaginate($queryBuilder, $limit = 10, $currentPage = null)
    {
        // Paginator
        $adapter = new DoctrineORMAdapter($queryBuilder);
        $pagerfanta = new Pagerfanta($adapter);
        $pagerfanta->setMaxPerPage($limit);
        $needEmpty = false;

        if (null === $currentPage) {
            $currentPage = $this->request->get('page',1);
        }

        try {
            $pagerfanta->setCurrentPage($currentPage);
        } catch(OutOfRangeCurrentPageException $exception) {
            $needEmpty = true;
        }

        $entities = $needEmpty ? array() : $pagerfanta->getCurrentPageResults();

        $compiledPaginator = array($entities, $pagerfanta->getNbResults(),$pagerfanta);

        return $compiledPaginator;
    }

    // Return a block of entities from QueryBuilder, Metrics
    public function paginate($queryBuilder,$route = null,$limit = 10, $quietOutOfRange = false, $currentPage = null, $paginator = false)
    {
        // Paginator
        $adapter = new DoctrineORMAdapter($queryBuilder);
        $pagerfanta = new Pagerfanta($adapter);
        $pagerfanta->setMaxPerPage($limit);

        if (null === $currentPage) {
            $currentPage = $this->request->get('page',1);
        }

        if ($quietOutOfRange) {
            try {
                $pagerfanta->setCurrentPage($currentPage);
            } catch(OutOfRangeCurrentPageException $exception) {}
        } else {
            try {
                $pagerfanta->setCurrentPage($currentPage);
            } catch(OutOfRangeCurrentPageException $exception) {
                $pagerfanta->setCurrentPage(1);
            }
        }

        $entities = $pagerfanta->getCurrentPageResults();
        $compiledPaginator = array($entities, $pagerfanta->getNbResults(),$pagerfanta);

        if ($paginator) {
            // Paginator - route generator
            $router = $this->router;
            $routeGenerator = function($page) use ($router,$route)
            {
                if (is_array($route)) {
                    return $router->generate($route['route'], array_merge($route['args'],array('page' => $page)) );
                } else {
                    return $router->generate($route, array('page' => $page));
                }
            };

            // Paginator - view
            $view = new TwitterBootstrapView();
            $compiledPaginator[] = $view->render($pagerfanta, $routeGenerator, array(
                'proximity' => 3,
                'prev_message' => '<',
                'next_message' => '>',
            ));
        }

        return $compiledPaginator;
    }

    // Test if the "page" request is the last
    public function isLastPage($pagerfanta, $currentPage = null)
    {
        if (null === $currentPage) {
            $currentPage = $this->request->get('page');
        }

        try {
            $pagerfanta->setCurrentPage(++$currentPage);
        } catch(OutOfRangeCurrentPageException $exception) {
            return true;
        }

        return false;
    }


    // Test if the "page" request is the last
    public function getNextPage($pagerfanta, $currentPage = null)
    {
        if (null === $currentPage) {
            $currentPage = $this->request->get('page');
        }

        if ($this->isLastPage($pagerfanta, $currentPage)) {
            return "";
        }

        return (int)$currentPage + 1;
    }




    // Return a block of entities from multiple QueryBuilders, Metrics
    public function paginateMany($queryBuilders,$route,$limit = 10, $quietOutOfRange = false, $paginator = false)
    {
        $results = array();

        foreach($queryBuilders as $queryBuilder) {
            $results = $this->paginate($queryBuilder,null,$limit,$quietOutOfRange,$paginator);
        }

        return $results;
    }




    public function getName()
    {
        return 'jrk_easypagination';
    }



}
