<?php

define("SITE_DIR", __DIR__);
define("DB_CONN", SITE_DIR . "/db_conn/index.php");
define("SMARTY_CONFIG_DIR", SITE_DIR . "/config/");
define("SMARTY_CACHE_DIR", SITE_DIR . "/cache/");
define("SMARTY_TEMPLATES_DIR", SITE_DIR . "/templates/");
define("SMARTY_COMPILES_DIR", SITE_DIR . "/templates_c/");

require_once SITE_DIR . "/vendor/autoload.php";
use MatthiasMullie\Minify;
use MiladRahimi\PhpRouter\Router;
use MiladRahimi\PhpRouter\Exceptions\RouteNotFoundException;
use Laminas\Diactoros\Response\HtmlResponse;

/*
    More about composer - https://getcomposer.org/doc/

    Example of usage src folder with composer PSR-4
    To add your own namespace with autoloader, add it in composer.json with path
    "autoload": {
        "psr-4": {
            "Chieff\\": "src/chieff"
        }
    },
    Classes will be loaded from src/chieff with using chosen namespace:
    use Chieff\Somelib\Somelib;
    $somelib = new Somelib();
*/

$GLOBALS["template"] = "";

/* More about routing - https://github.com/miladrahimi/phprouter */
$router = Router::create();
$router->get('/', function () {
    $GLOBALS["template"] = "index.tpl";
});
$router->get('/page', function () {
    $GLOBALS["template"] = "page.tpl";
});

try {
    $router->dispatch();
} catch (RouteNotFoundException $e) {
    $router->getPublisher()->publish(new HtmlResponse('Not found.', 404));
} catch (Throwable $e) {
    $router->getPublisher()->publish(new HtmlResponse('Internal error.', 500));
}

if (!isset($GLOBALS["template"]) || !$GLOBALS["template"])
    exit();

/*
    More about smarty:
        https://smarty-php.github.io/smarty/4.x/
        https://www.smarty.net/
*/
$smarty = new Smarty();
$smarty->setConfigDir(SMARTY_CONFIG_DIR);
$smarty->setCacheDir(SMARTY_CACHE_DIR);
$smarty->setTemplateDir(SMARTY_TEMPLATES_DIR);
$smarty->setCompileDir(SMARTY_COMPILES_DIR);
$smarty->debugging = true;
$smarty->caching = false;
$smarty->cache_lifetime = 120;

// Default css and js paths
$css = [
    "paths" => [
        "/assets/plugins/node_modules/bootstrap/dist/css/bootstrap-grid.min.css",
        "/assets/css/style.css",
        "/assets/css/additional.css"
    ],
    "filename" => "style.css"
];
$js = [
    "paths" => [
        "/assets/plugins/node_modules/jquery/dist/jquery.min.js",
        "/assets/js/script.js"
    ],
    "filename" => "script.js"
];

// Additional paths for another pages
switch ($GLOBALS["template"]) {
    case "index.tpl":
        // $css["paths"][] = "assets/css/style2.css";
        break;
    case "page.tpl":
        // $js["paths"][] = "assets/js/script2.js";
        break;
    default:
        break;
}

$clear_cache = false;
if (isset($_GET["clear_cache"])) {
    $clear_cache = true;
    $smarty->clearCache($GLOBALS["template"]);
}

$css = site_minify("css", $css, $clear_cache);
$smarty->assign('styles_head', $css);
$js = site_minify("js", $js, $clear_cache);
$smarty->assign('footer_scripts', $js);

/* More about minify - https://github.com/matthiasmullie/minify */
function site_minify(string $type, array $paths, bool $reload = false) {
    if (!$type || !$paths || !isset($paths["filename"]))
        return "";
    $minified_path = "/assets/compiled/";
    $minified_full_path = $minified_path . $paths["filename"];
    $minified_full_server_path = SITE_DIR . $minified_full_path;
    if (file_exists($minified_full_server_path) && !$reload)
        return $minified_full_path;
    switch ($type) {
        case "css":
            $minifier = new Minify\CSS();
            break;
        case "js":
            $minifier = new Minify\JS();
            break;
        default:
            return "";
    }
    foreach ($paths["paths"] as $path) {
        if ($path)
            $minifier->add(SITE_DIR . $path);
    }
    $minifier->minify($minified_full_server_path);
    return $minified_full_path;
}

$smarty->assign("developer", "chieff");
$smarty->display($GLOBALS["template"]);