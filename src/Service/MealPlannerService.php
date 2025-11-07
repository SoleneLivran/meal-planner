<?php

namespace App\Service;

class MealPlannerService
{
    private array $recipes;

    public function __construct()
    {
        $this->recipes = include __DIR__.'/../Model/recipes.php';
    }

    public function generateWeeklyPlan(array $params = []): array
    {
        $mealsByDay = $params['mealsByDay'] ?? [];
        $numberOfMeals = !empty($mealsByDay) ? count(array_merge(...array_values($mealsByDay))) : 0;

        $weekPlan = [
          'Lundi' => [],
          'Mardi' => [],
          'Mercredi' => [],
          'Jeudi' => [],
          'Vendredi' => [],
          'Samedi' => [],
          'Dimanche' => [],
        ];

        if (0 === $numberOfMeals) {
            return $weekPlan;
        }

        shuffle($this->recipes);
        $weekRecipes = array_slice($this->recipes, 0, $numberOfMeals);
        $currentIndex = 0;

        foreach ($weekPlan as $day => $dayPlan) {
            $dayMeals = $mealsByDay[$day] ?? null;
            if (!empty($dayMeals)) {
                foreach ($dayMeals as $meal) {
                    $weekPlan[$day][$meal] = $weekRecipes[$currentIndex]['name'] ?? null;
                    $currentIndex++;
                }
            }
        }

        return $weekPlan;
    }
}
