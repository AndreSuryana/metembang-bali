<?php

namespace Sorting;

use Exception;

/**
 * Selection Sort Algorithm Implementation
 */
class SelectionSort
{

    const KEY_TITLE = 'title';
    const KEY_DATE = 'date';
    const ASC = 'asc';
    const DESC = 'desc';

    /**
     * Sort by Title
     */
    public static function sortByTitle(array $array, string $method): array
    {
        try {
            // First boundary loop
            for ($i = 0; $i < sizeof($array); $i++) {
                // Set current title index & element
                $currentIndex = $i;
                $currentTitle = $array[$i]['title'];

                // Second boundary loop
                for ($j = $i + 1; $j < sizeof($array); $j++) {
                    // Compare string title
                    switch ($method) {
                        case SelectionSort::ASC:
                            if (strcmp($array[$j]['title'], $currentTitle) < 0) {
                                // If first string is smaller than second string (currentTitle),
                                // then update currentTitle to current element in second boundary loop
                                $currentIndex = $j;
                                $currentTitle = $array[$j]['title'];
                            }
                            break;
                        case SelectionSort::DESC:
                            if (strcmp($array[$j]['title'], $currentTitle) > 0) {
                                // If first string is greater than second string (currentTitle),
                                // then update currentTitle to current element in second boundary loop
                                $currentIndex = $j;
                                $currentTitle = $array[$j]['title'];
                            }
                            break;
                    }

                    // Swap currentTitle that has been found,
                    // then swap it if currentIndex not same as current first boundry iterator ($i)
                    if ($currentIndex != $i) {
                        $temp = $array[$currentIndex];
                        $array[$currentIndex] = $array[$i];
                        $array[$i] = $temp;
                    }
                }
            }

            return $array;
        } catch (Exception $e) {
            // If exception occurred, throw and return original array
            throw $e;
            return $array;
        }
    }

    /**
     * Sort by Date Added
     */
    public static function sortByDate(array $array, string $method): array
    {
        try {
            // First boundary loop
            for ($i = 0; $i < sizeof($array); $i++) {
                // Set current date index & element
                $currentIndex = $i;
                $currentDate = $array[$i]['created_at'];

                // Second boundary loop
                for ($j = $i + 1; $j < sizeof($array); $j++) {
                    // Compare string title
                    switch ($method) {
                        case SelectionSort::ASC:
                            if (strtotime($array[$j]['created_at']) < strtotime($currentDate)) {
                                // If first string is smaller than second string (currentDate),
                                // then update currentDate to current element in second boundary loop
                                $currentIndex = $j;
                                $currentDate = $array[$j]['created_at'];
                            }
                            break;
                        case SelectionSort::DESC:
                            if (strtotime($array[$j]['created_at']) > strtotime($currentDate)) {
                                // If first string is greater than second string (currentDate),
                                // then update currentDate to current element in second boundary loop
                                $currentIndex = $j;
                                $currentDate = $array[$j]['created_at'];
                            }
                            break;
                    }

                    // Swap currentDate that has been found,
                    // then swap it if currentIndex not same as current first boundry iterator ($i)
                    if ($currentIndex != $i) {
                        $temp = $array[$currentIndex];
                        $array[$currentIndex] = $array[$i];
                        $array[$i] = $temp;
                    }
                }
            }

            return $array;
        } catch (Exception $e) {
            // If exception occurred, throw and return original array
            throw $e;
            return $array;
        }
    }
}
