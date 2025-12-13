<?php

/**
 * This file has been auto-generated
 * by the Symfony Routing Component.
 */

return [
    false, // $matchHost
    [ // $staticRoutes
        '/waitroom/normal_game' => [[['_route' => 'create_waitroom_normal', '_controller' => 'App\\Controller\\Controller::createNormalRoom'], null, null, null, false, false, null]],
        '/waitroom/path_game' => [[['_route' => 'create_waitroom_path', '_controller' => 'App\\Controller\\Controller::createPathRoom'], null, null, null, false, false, null]],
        '/game_loop' => [[['_route' => 'game_loop', '_controller' => 'App\\Controller\\Controller::gameLoop'], null, null, null, false, false, null]],
        '/join_game' => [[['_route' => 'join_game', '_controller' => 'App\\Controller\\Controller::joinGame'], null, null, null, false, false, null]],
        '/neo4j-test' => [[['_route' => 'test-neo4j', '_controller' => 'App\\Controller\\Controller::index'], null, null, null, false, false, null]],
        '/' => [[['_route' => 'homepage', '_controller' => 'App\\Controller\\HomeController::index'], null, ['GET' => 0, 'POST' => 1], null, false, false, null]],
        '/testtwig' => [[['_route' => 'test_twig', '_controller' => 'App\\Controller\\TestTwigController::index'], null, null, null, false, false, null]],
    ],
    [ // $regexpList
        0 => '{^(?'
                .'|/_error/(\\d+)(?:\\.([^/]++))?(*:35)'
                .'|/waitroom/([^/]++)/players(*:68)'
            .')/?$}sDu',
    ],
    [ // $dynamicRoutes
        35 => [[['_route' => '_preview_error', '_controller' => 'error_controller::preview', '_format' => 'html'], ['code', '_format'], null, null, false, true, null]],
        68 => [
            [['_route' => 'room_players', '_controller' => 'App\\Controller\\Controller::roomPlayers'], ['id'], null, null, false, false, null],
            [null, null, null, null, false, false, 0],
        ],
    ],
    null, // $checkCondition
];
