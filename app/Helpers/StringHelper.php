<?php

namespace App\Helpers;

use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class StringHelper
{
    public function generateRandomString ($length) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    public function paginate($items, $perPage = 10, $page = null, $options = [])
    {
        $page = $page ?: (Paginator::resolveCurrentPage() ?: 1);
        $items = $items instanceof Collection ? $items : Collection::make($items);
        return new LengthAwarePaginator($items->forPage($page, $perPage), $items->count(), $perPage, $page, $options);
    }

    function fixMdn($mdn) 
    {
        $mdn = preg_replace("/[^0-9]/", "", $mdn);

        if ($mdn == "") {
            return "";
        }
        
        if ($this->startsWith($mdn, "0")) {
            $mdn = "62" . substr($mdn, 1, strlen($mdn));
        }
        
        if (!$this->startsWith($mdn, "62")) {
            $mdn = "62" . $mdn;
        }
        
        return $mdn;
    }

    // Function to check string starting
    // with given substring
    function startsWith($string, $startString)
    {
        $len = strlen($startString);
        return (substr($string, 0, $len) === $startString);
    }

    // Function to check the string is ends 
    // with given substring or not
    function endsWith($string, $endString)
    {
        $len = strlen($endString);
        if ($len == 0) {
            return true;
        }
        return (substr($string, -$len) === $endString);
    }
}
