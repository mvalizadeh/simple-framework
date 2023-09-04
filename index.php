<?php

//session
session_start();

use Database\Database;

require_once 'Database/Database.php';
require_once 'activities/Admin/Category.php';

//config
define('BASE_PATH', __DIR__);
define('CURRENT_DOMAIN', currentDomain() . '/news');
define('DISPLAY_ERROR', true);

//database
define('DB_HOST', 'localhost');
define('DB_NAME', 'news');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '');



$db = new Database();


//helpers

function protocol()
{
    $actual_link = (empty($_SERVER['HTTPS']) ? 'http://' : 'https://');
    return $actual_link;
}


function currentDomain()
{
    return protocol() . $_SERVER['HTTP_HOST'];
}


function asset($src)
{
    return currentDomain() . '/' . $src;
}

function url($url)
{
    return currentDomain() . '/' . $url;
}

function currentUrl()
{
    return trim(currentDomain(), ' /') . '/' . trim($_SERVER['REQUEST_URI'], ' /');
}

function methodField()
{
    return $_SERVER['REQUEST_METHOD'];
}

function displayError($displayError)
{
    if ($displayError) {
        ini_set('display_errors', 1);
        ini_set('display_startup_errors', 1);
        error_reporting(E_ALL);
    } else {
        ini_set('display_errors', 0);
        ini_set('display_startup_errors', 0);
        error_reporting(0);
    }
}

global $flashMessage;

if (isset($_SESSION['flash_message'])) {
    $flashMessage = $_SESSION['flash_message'];
    unset($_SESSION['flash_message']);
}

function flash($name, $value = null)
{
    if ($value === null) {
        global $flashMessage;
        $message = isset($flashMessage[$name]) ? $flashMessage[$name] : '';
        return $message;
    } else {
        $_SESSION['flash_message'] = $value;
    }
}

function uri($reservedUrl, $class, $method, $requestMethod = 'GET')
{
    //current url array
    $currentUrl = explode('?', currentUrl())[0];
    $currentUrl = str_replace(CURRENT_DOMAIN, '', $currentUrl);
    $currentUrl = trim($currentUrl, '/');
    $currentUrlArray = explode('/', $currentUrl);

    //reserved url array
    $reservedUrlArray = trim($reservedUrl, '/');
    $reservedUrlArray = explode('/', $reservedUrlArray);
    // dd($reservedUrlArray);

    if (sizeof($currentUrlArray) != sizeof($reservedUrlArray) && methodField() != $requestMethod) {
        return false;
    } else {
        $parameters = [];
        for ($key = 0; $key < sizeof($reservedUrlArray); $key++) {
            if ($reservedUrlArray[$key][0] == "{" && $reservedUrlArray[$key][strlen($reservedUrlArray[$key]) - 1] == "}") {
                array_push($parameters, $currentUrlArray[$key]);
            } else if ($currentUrlArray[$key] != $reservedUrlArray[$key]) {
                return false;
            }
        }
    }

    if (methodField() == "POST") {
        $request = isset($_FILES) ? array_merge($_POST, $_FILES) : $_POST;
        $parameters = array_merge([$request], $parameters);
    }

    $object = new $class;
    call_user_func_array(array($object, $method), $parameters);
    exit;
}

function dd($var)
{
    echo "<pre>";
    var_dump($var);
    exit;
}

echo uri('admin/category', 'Admin\Category', 'index');
echo uri('admin/category/create', 'Admin\Category', 'create');
echo uri('admin/category/store', 'Admin\Category', 'store', 'POST');
echo uri('admin/category/edit/{id}', 'Admin\Category', 'edit');
echo uri('admin/category/update/{id}', 'Admin\Category', 'update', 'POST');
echo uri('admin/category/delete/{id}', 'Admin\Category', 'delete');


echo "404 - Not Found";
