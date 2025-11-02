<?php

namespace App\Service;

class MealPlannerService
{
    private array $recipes;

    public function __construct()
    {
        $this->recipes = include __DIR__.'/../Model/recipes.php';
    }

    public function generateWeeklyPlan(): array
    {
        shuffle($this->recipes);
        $weekRecipes = array_slice($this->recipes, 0, 14);

        $weekPlan = [
          'Lundi' => [],
          'Mardi' => [],
          'Mercredi' => [],
          'Jeudi' => [],
          'Vendredi' => [],
          'Samedi' => [],
          'Dimanche' => [],
        ];

        $currentIndex = 0;

        foreach ($weekPlan as $day => $dayPlan) {
            $lunch = $weekRecipes[$currentIndex]['name'];
            $currentIndex++;
            $dinner = $weekRecipes[$currentIndex]['name'];
            $currentIndex++;
            $weekPlan[$day] = [
                'lunch' => $lunch,
                'dinner' => $dinner,
            ];
        }

        return $weekPlan;
    }
}
