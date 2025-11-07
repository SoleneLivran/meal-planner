<?php

namespace App\Service;

class MealPlannerService
{
    private array $recipes;

    public function __construct(?array $recipes = null)
    {
        $this->recipes = $recipes ?? include __DIR__ . '/../../config/data/recipes.php';
    }

    public function generateWeeklyPlan(array $params = []): array
    {
        $mealsByDay = $params['mealsByDay'] ?? [];
        $numberOfMeals = !empty($mealsByDay) ? count(array_merge(...array_values($mealsByDay))) : 0;
        if (count($this->recipes) < $numberOfMeals) {
            throw new \Exception("Not enough recipes available to generate requested plan");
        }

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
