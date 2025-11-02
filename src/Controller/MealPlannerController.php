<?php

namespace App\Controller;

use App\Service\MealPlannerService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MealPlannerController extends AbstractController
{
    #[Route('/', name: 'meal_planner_home')]
    public function index(): Response
    {
        return $this->render( 'index.html.twig');
    }

    #[Route('/generate', name: 'meal_planner_generate')]
    public function generate(MealPlannerService $planner): Response
    {
        return $this->render('meal_plan.html.twig', [
            'plan' => $planner->generateWeeklyPlan(),
        ]);
    }
}
