<?php

namespace App\Service;

use Exception;

class MealPlannerService
{
    private array $recipes;

    public function __construct(?array $recipes = null)
    {
        $this->recipes = $recipes ?? include __DIR__ . '/../../config/data/recipes.php';
    }

    public function generateWeeklyPlan(array $params = []): array
    {
        $recipes = $this->recipes;

        $mealsByDay = $params['mealsByDay'] ?? [];
        $numberOfMeals = !empty($mealsByDay) ? count(array_merge(...array_values($mealsByDay))) : 0;

        $vegetarianOnly = $params['vegetarianOnly'] ?? false;
        if ($vegetarianOnly) {
            $recipes = array_filter($recipes, function ($recipe) {
                return true === $recipe['vegetarian'];
            });
        }

        if (count($recipes) < $numberOfMeals) {
            throw new Exception(
                "Impossible de générer un menu correspondant aux critères sélectionnés : le nombre de recettes adaptées est insuffisant.",
            );
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

        shuffle($recipes);
        $weekRecipes = array_slice($recipes, 0, $numberOfMeals);
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
