<?php

namespace App\Service;

use Exception;

class NotEnoughRecipesException extends Exception
{
    public function __construct() {
        $message = "Impossible de générer un menu correspondant aux critères sélectionnés : ";
        $message .= "le nombre de recettes adaptées est insuffisant.";
        parent::__construct($message);
    }
}
