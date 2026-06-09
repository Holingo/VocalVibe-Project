<?php

require_once __DIR__."/../../Database.php";

/**
 * Główna klasa abstrakcyjna dla wszystkich repozytoriów, dostarczająca instancję połączenia z bazą danych.
 */
abstract class Repository {

    protected Database $database;

    public function __construct() {
        $this->database = new Database();
    }
}