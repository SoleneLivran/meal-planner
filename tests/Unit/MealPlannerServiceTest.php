<?php

namespace App\Tests\Unit;

use App\Service\MealPlannerService;
use App\Service\NotEnoughRecipesException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class MealPlannerServiceTest extends TestCase
{
    public function testItThrowsExceptionIfNotEnoughRecipesAreAvailable(): void
    {
        $params = ['mealsByDay' =>
            [
                'Lundi' => ['lunch', 'dinner'],
                'Mardi' => ['lunch', 'dinner'],
                'Mercredi' => ['lunch', 'dinner'],
            ],
        ];

        $mealPlannerService = $this->getMealPlannerService(3);

        $this->expectException(NotEnoughRecipesException::class);
        $this->expectExceptionMessage(
            "Impossible de générer un menu correspondant aux critères sélectionnés : le nombre de recettes adaptées est insuffisant.",
        );
        $mealPlannerService->generateWeeklyPlan($params);
    }

    #[DataProvider('selectedMealsProvider')]
    public function testGenerateWeeklyPlanMatchesSelectedMealsByDay(array $mealsByDay): void
    {
        $params = ['mealsByDay' => $mealsByDay];
        $plan = $this->getMealPlannerService(14)->generateWeeklyPlan($params);

        $this->assertCount(7, $plan, "Should always return a seven days plan");

        foreach ($mealsByDay as $day => $expectedMeals) {
            foreach ($expectedMeals as $meal) {
                $this->assertArrayHasKey($meal, $plan[$day], "$day should contain $meal");
            }
        }

        $filledDays = array_keys($mealsByDay);
        foreach (array_diff(array_keys($plan), $filledDays) as $day) {
            $this->assertEmpty($plan[$day], "$day should be empty");
        }
    }

    public function testGenerateWeeklyPlanDoesNotContainDuplicateRecipes(): void
    {
        $mealsByDay = [
            'Lundi' => ['lunch', 'dinner'],
            'Mardi' => ['lunch', 'dinner'],
            'Mercredi' => ['lunch', 'dinner'],
            'Jeudi' => ['lunch', 'dinner'],
            'Vendredi' => ['lunch', 'dinner'],
            'Samedi' => ['lunch', 'dinner'],
            'Dimanche' => ['lunch', 'dinner'],
        ];

        $service = $this->getMealPlannerService(14);
        $plan = $service->generateWeeklyPlan(['mealsByDay' => $mealsByDay]);

        $mappedRecipesNames = array_values(array_filter(array_map('array_values', $plan)));
        $allRecipesNames = !empty($mappedRecipesNames) ? array_merge(...$mappedRecipesNames) : [];

        $this->assertSameSize(
            array_unique($allRecipesNames),
            $allRecipesNames,
            'Generated plan should not contain duplicate recipes.'
        );
    }

    private function getMealPlannerService(int $numberOfRecipes): MealPlannerService
    {
        $testRecipes = [];
        for ($i = 1; $i <= $numberOfRecipes; $i++) {
            $testRecipes[] = ['name' => 'Recipe' . $i];
        }

        return new MealPlannerService($testRecipes);
    }

    public static function selectedMealsProvider(): array
    {
        return [
            'empty week' => [
                [],
            ],
            'single day lunch only' => [
                ['Mercredi' => ['lunch']],
            ],
            'single day lunch and dinner' => [
                ['Mercredi' => ['lunch', 'dinner']],
            ],
            'two days mixed meals' => [
                [
                    'Mercredi' => ['lunch'],
                    'Vendredi' => ['lunch', 'dinner'],
                ],
            ],
            'full week' => [
                [
                    'Lundi' => ['lunch', 'dinner'],
                    'Mardi' => ['lunch', 'dinner'],
                    'Mercredi' => ['lunch', 'dinner'],
                    'Jeudi' => ['lunch', 'dinner'],
                    'Vendredi' => ['lunch', 'dinner'],
                    'Samedi' => ['lunch', 'dinner'],
                    'Dimanche' => ['lunch', 'dinner'],
                ],
            ],
            'full week only lunches' => [
                [
                    'Lundi' => ['lunch'],
                    'Mardi' => ['lunch'],
                    'Mercredi' => ['lunch'],
                    'Jeudi' => ['lunch'],
                    'Vendredi' => ['lunch'],
                    'Samedi' => ['lunch'],
                    'Dimanche' => ['lunch'],
                ],
            ],
            'full week only dinner' => [
                [
                    'Lundi' => ['dinner'],
                    'Mardi' => ['dinner'],
                    'Mercredi' => ['dinner'],
                    'Jeudi' => ['dinner'],
                    'Vendredi' => ['dinner'],
                    'Samedi' => ['dinner'],
                    'Dimanche' => ['dinner'],
                ],
            ],
        ];
    }

    public function testGenerateWeeklyPlanOnlySelectsVegetarianRecipesIfRequired(): void
    {
        $vegetarianRecipes = [];
        for ($i = 1; $i <= 6; $i++) {
            $vegetarianRecipes[] = ['name' => 'Vegetarian recipe' . $i, 'vegetarian' => true];
        }

        $meatRecipes = [];
        for ($i = 1; $i <= 6; $i++) {
            $meatRecipes[] = ['name' => 'Meat recipe' . $i];
        }

        $testRecipes = array_merge($vegetarianRecipes, $meatRecipes);
        $mealPlannerService = new MealPlannerService($testRecipes);

        $params = [
            'mealsByDay' =>
                [
                    'Lundi' => ['lunch', 'dinner'],
                    'Mardi' => ['lunch', 'dinner'],
                    'Mercredi' => ['lunch', 'dinner'],
                ],
            'vegetarianOnly' => true,
        ];

        mt_srand(1234);
        $plan = $mealPlannerService->generateWeeklyPlan($params);
        mt_srand();

        $mappedPlanRecipesNames = array_values(array_filter(array_map('array_values', $plan)));
        $allPlanRecipesNames = !empty($mappedPlanRecipesNames) ? array_merge(...$mappedPlanRecipesNames) : [];

        $this->assertEmpty(
            array_intersect($allPlanRecipesNames, array_column($meatRecipes, 'name')),
            'Weekly plan should only contain vegetarian recipes, but some meat recipes were found.'
        );
    }
}
