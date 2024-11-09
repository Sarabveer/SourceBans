<?php

// This file is called when a steamid is passed as input for searching
// It returns the Steam2 format of the steamid passed as input
// That create support for all steamid formats in search

require_once 'SteamID.php';
use SteamID\SteamID;

header('Content-Type: application/json');

// Get raw input
$rawInput = file_get_contents('php://input');
$input = json_decode($rawInput, true);
$steamid = $input['steamid'] ?? '';

try {
    SteamID::init();
    
    if (!SteamID::isValidID($steamid)) {
        throw new Exception('Invalid Steam ID format');
    }

    $steam2id = SteamID::toSteam2($steamid);
    if ($steam2id === false) {
        throw new Exception('Conversion failed');
    }

    echo json_encode([
        'success' => true, 
        'steam2id' => $steam2id,
        'original' => $steamid
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false, 
        'error' => $e->getMessage(),
        'original' => $steamid
    ]);
}