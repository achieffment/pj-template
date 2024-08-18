<?php
error_reporting(E_ERROR);

define("SITE_DIR", __DIR__);
define("DB_CONN", SITE_DIR . "/db_conn/index.php");
define("SMARTY_CONFIG_DIR", SITE_DIR . "/config/");
define("SMARTY_CACHE_DIR", SITE_DIR . "/cache/");
define("SMARTY_TEMPLATES_DIR", SITE_DIR . "/templates/");
define("SMARTY_COMPILES_DIR", SITE_DIR . "/templates_compiled/");

require_once SITE_DIR . "/src/vendor/autoload.php";

use MatthiasMullie\Minify;
use MiladRahimi\PhpRouter\Router;
use MiladRahimi\PhpRouter\Exceptions\RouteNotFoundException;
use Laminas\Diactoros\Response\HtmlResponse;

$GLOBALS["template"] = "";

/**
 * More about routing:
 * https://github.com/miladrahimi/phprouter
 */
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
    $router->getPublisher()->publish(new HtmlResponse('Not found', 404));
} catch (Throwable $e) {
    $router->getPublisher()->publish(new HtmlResponse('Internal error', 500));
}

if (empty($GLOBALS["template"])) {
    exit('No template');
}

/**
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
    "filename" => "style.css",
    "paths" => [
        "/assets/plugins/node_modules/bootstrap/dist/css/bootstrap-grid.min.css",
        "/assets/css/style.css",
        "/assets/css/additional.css"
    ]
];
$js = [
    "filename" => "script.js",
    "paths" => [
        "/assets/plugins/node_modules/jquery/dist/jquery.min.js",
        "/assets/js/script.js"
    ]
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

$clearCache = false;
if (isset($_GET["clear_cache"])) {
    $clearCache = true;
    $smarty->clearCache($GLOBALS["template"]);
}

$css = site_minify("css", $css, $clearCache);
$smarty->assign('styles_head', $css);

$js = site_minify("js", $js, $clearCache);
$smarty->assign('footer_scripts', $js);

/**
 * More about minify:
 * https://github.com/matthiasmullie/minify
 *
 * @param string $type
 * @param array $paths
 * @param bool $reload
 * @return string
 * @throws Exception
 */
function site_minify(string $type, array $paths, bool $reload = false): string {
    if (!$type || !$paths || !isset($paths["filename"])) {
        return "";
    }

    $minifiedPath = "/assets/compiled/";
    $minifiedFilePath = $minifiedPath . $paths["filename"];
    $minifiedServerPath = SITE_DIR . $minifiedPath;
    $minifiedServerFilePath = SITE_DIR . $minifiedFilePath;

    if (file_exists($minifiedServerFilePath) && !$reload) {
        return $minifiedFilePath;
    }

    if (!is_dir($minifiedServerPath)) {
        if (!mkdir($minifiedServerPath)) {
            throw new \Exception('Не удается создать папку для минифицированных файлов');
        }
    }

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
        if ($path) {
            $minifier->add(SITE_DIR . $path);
        }
    }

    $minifier->minify($minifiedServerFilePath);

    return $minifiedFilePath;
}

$smarty->assign("developer", "achieffment");
$smarty->display($GLOBALS["template"]);
