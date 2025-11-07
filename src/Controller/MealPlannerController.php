<?php

namespace App\Controller;

use App\Service\MealPlannerService;
use Exception;
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

        $weeklyPlanParameters = [
            'mealsByDay' => $params['meals'] ?? [],
        ];

        if (isset($params['vegetarian']) && !!$params['vegetarian']) {
            $weeklyPlanParameters['vegetarianOnly'] = true;
        }

        try {
            $mealPlan = $planner->generateWeeklyPlan($weeklyPlanParameters);
        } catch (Exception $e) {
            $error = !empty($e->getMessage()) ? $e->getMessage() : 'Impossible de générer le menu';
            return $this->render('meal_plan_error.html.twig', ['error' => $error]);
        }

        return $this->render('meal_plan.html.twig', ['plan' => $mealPlan]);
    }
}
