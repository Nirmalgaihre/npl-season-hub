<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Points awarded for abandoned / no-result matches
    |--------------------------------------------------------------------------
    |
    | Set this to 1, 2, 3, or 4. Both teams receive the configured points.
    |
    */

    'no_result_points' => env('POINT_TABLE_NO_RESULT_POINTS', 1),
];