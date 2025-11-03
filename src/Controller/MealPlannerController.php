<?php

namespace App\Controller;

use App\Service\MealPlannerService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
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
    public function generate(Request $request, MealPlannerService $planner): Response
    {
        $params = $request->request->all();
        $mealsByDay = $params['meals'] ?? [];

        return $this->render('meal_plan.html.twig', [
            'plan' => $planner->generateWeeklyPlan(['mealsByDay' => $mealsByDay]),
        ]);
    }
}
