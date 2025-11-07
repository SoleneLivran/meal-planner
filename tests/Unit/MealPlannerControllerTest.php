<?php

namespace App\Tests\Unit;

use App\Controller\MealPlannerController;
use App\Service\MealPlannerService;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

class MealPlannerControllerTest extends TestCase
{
    #[DataProvider('mealsByDayProvider')]
    public function testGenerateRendersMealPlanWithCorrectParams(array $mealsByDay, array $expectedPlan): void
    {
        $request = new Request([], ['meals' => $mealsByDay]);

        $mealPlannerService = $this->createMock(MealPlannerService::class);
        $mealPlannerService->expects($this->once())
            ->method('generateWeeklyPlan')
            ->with(['mealsByDay' => $mealsByDay])
            ->willReturn($expectedPlan);

        $controller = $this->getMockBuilder(MealPlannerController::class)
            ->onlyMethods(['render'])
            ->getMock();

        $controller->expects($this->once())
            ->method('render')
            ->with('meal_plan.html.twig', ['plan' => $expectedPlan]);

        $controller->generate($request, $mealPlannerService);
    }

    public static function mealsByDayProvider(): array
    {
        return [
            'empty plan' => [
                [],
                [],
            ],
            'single day lunch' => [
                ['Lundi' => ['lunch']],
                ['Lundi' => ['lunch' => 'Recipe1']],
            ],
            'single day lunch & dinner' => [
                ['Mardi' => ['lunch', 'dinner']],
                ['Mardi' => ['lunch' => 'Recipe2', 'dinner' => 'Recipe3']],
            ],
            'multiple days' => [
                [
                    'Mercredi' => ['lunch'],
                    'Vendredi' => ['dinner'],
                ],
                [
                    'Mercredi' => ['lunch' => 'Recipe4'],
                    'Vendredi' => ['dinner' => 'Recipe5'],
                ],
            ],
        ];
    }
}
