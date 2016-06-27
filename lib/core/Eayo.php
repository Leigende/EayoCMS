<?php
/**
  * This file is part of EayoCMS.
  *
  * @package EayoCMS
  * @author Alexis Rouillard / Leigende <contact@arouillard.fr>
  * @link http://arouillard.fr
  *
  * For the full copyright and license information, please view the LICENSE
  * file that was distributed with this source code.
  */

namespace Core;

defined('EAYO_ACCESS') OR exit('No direct script access.');

class Eayo
{
    /** @var core An instance of the Eayo class */
    protected static $_instance = null;

    /** Version de Eayo */
    const VERSION = '0.0.2';

    /** Common environment type constants for consistency and convenience */
    const PRODUCTION  = 1;
    const DEVELOPMENT = 2;

    /** @var string Contenu de la page */
    public static $content = '';

    /** @var string Eayo environment */
    public static $environment = Eayo::PRODUCTION;

    /** @var array Eayo environment names */
    public static $environment_names = array(
        Eayo::PRODUCTION  => 'production',
        Eayo::DEVELOPMENT => 'development',
    );

    public $self_user;

    public static $router = [];

    public $twig;

    public $twig_vars = [];

    /** @access  protected clone method */
    protected function __clone()
    {
        // Nothing here.
    }

    protected function __construct()
    {
        // Local
        date_default_timezone_set('Europe/Paris');

        /* Define App */
        defined('LIB_DIR') || define('LIB_DIR', ROOT_DIR . 'lib' . DS);
        defined('APP_DIR') || define('APP_DIR', LIB_DIR . 'app' . DS);
        defined('CONTENT_DIR') || define('CONTENT_DIR', APP_DIR . 'views' . DS);
        defined('CONTENT_EXT') || define('CONTENT_EXT', '.md');
        defined('PLUGINS_DIR') || define('PLUGINS_DIR', ROOT_DIR . 'plugins' . DS);
        defined('THEMES_DIR') || define('THEMES_DIR', ROOT_DIR . 'themes' . DS);
        defined('CACHE_DIR') || define('CACHE_DIR', LIB_DIR . 'cache' . DS);
        defined('STORAGE_DIR') || define('STORAGE_DIR', LIB_DIR . 'datastorage' . DS);

        /** Set Eayo Environment */
        Eayo::$environment = Eayo::DEVELOPMENT;

        /* Init Session */
        $this->sessionStart();

        /** Load Core file */
        $this->__autoload();

        /* Init default Route */
        $this->initRoute();
    }

    /** Initialize the autoloader */
    protected function __autoload()
    {
        spl_autoload_extensions(".php");
        spl_autoload_register(
            function ($className) {
                $fileName = LIB_DIR . str_replace("\\", DS, $className) . '.php';
                if (file_exists($fileName)) {
                    include $fileName;
                }
            }
        );
        spl_autoload_register('\Core\Plugin::autoload');
        $vendor = LIB_DIR . 'vendor' . DS . 'autoload.php';
        if(is_file($vendor)) {
            include $vendor;
        } else {
            throw new \Exception('Cannot find `lib/vendor/autoload.php`. Run `composer install`.', 1568);
        }

        /* Init Config API */
        $this->config = Config::init();

        /* Init Tools API*/
        $this->tools = Tools::init();

        /* Init Admin */
        new Admin\Core();

        /* Init Plugins API*/
        $this->initPlugins();
    }

    /**
     * Init plugins
     */
    protected function initPlugins()
    {
        $loadPlugin = Plugin::init();
        $plugins = $this->config->get('plugins');
        if (isset($plugins) && is_array($plugins)) {
            $this->plugins = $loadPlugin->loadAll($plugins);
        }
    }

    public function Router()
    {
        $_query = [];
        $content_file;
        $template_file;
        $routing = false;

        $query = isset($_SERVER['QUERY_STRING']) ? $_SERVER['QUERY_STRING'] : '';
        $queryLength = strpos($query, '&') !== false ? $query = substr($query, 0, $queryLength) : '';
        $queryPart = explode('/', $query);
        $index = empty($queryPart) ? '' : $queryPart[0];

        if (count($queryPart) > 1) {
            if (isset(Eayo::$router) && array_key_exists($index, Eayo::$router)) {
                $routing = true;
                unset($queryPart[0]);
                $query = implode($queryPart, DS);
                if (Eayo::$router[$index] === true) {
                    $_query = [$index => '@default'];
                } else {
                    $_query = [$query => '@'.$index];
                }
            } else {
                $query = implode($queryPart, DS);
                $_query = [$query => '@default'];
            }
        } else {
            if (empty($query)) {
                $_query = ['index' => '@default'];
            } else {
                $_query = [$index => '@default'];
            }
        }

        $query = key($_query);
        $namespace = current($_query);
        $template = $this->tools->findTemplate($namespace);
        $content_dir = CONTENT_DIR;

        if ($namespace === '@default' && !$routing) {
            $content_file = $content_dir.$query;
            if (is_dir($content_dir.$query)) {
                $content_file .= DS.'index';
            }
        } else {
            $content_dir = rtrim($template, '\/').DS.'views'.DS;
            $content_file = $content_dir.$query;
            if (is_dir($content_dir.$query)) {
                $content_file .= 'index';
            }
        }

        $content_file = glob($content_file.'.{md,html,htm,twig,php}', GLOB_BRACE);
        if (!empty($content_file)) {
            $content_file = $content_file[0];
        } else {
            $content_file = glob(CONTENT_DIR.'404.{md,html,htm,php}', GLOB_BRACE)[0];
        } //throw new 404 not found

        return [$index, $content_file, $namespace, $template];
    }

    /**
     * Prepare Twig Environment
     */
    protected function initTwig($template, $namespace)
    {
        $loader = new \Twig_Loader_Filesystem(CONTENT_DIR);

        $loader->addPath($template, $namespace);

        $twigConf = $this->config->get('twig');
        $twigConf['cache'] = $twigConf['cache'] === true ? LIB_DIR.'cache'.DS : false;

        $this->twig = new \Twig_Environment($loader, $twigConf);
        $this->twig->addExtension(new \Jralph\Twig\Markdown\Extension(
            new \Jralph\Twig\Markdown\Parsedown\ParsedownExtraMarkdown
        ));
        $this->twig->addExtension(new \Twig_Extension_Debug());
        $this->twig_vars = array_merge($this->twig_vars, array(
            'version' => Eayo::VERSION,
            'config' => $this->config->getAll(),
            'base_url' => $this->tools->rooturl,
            'user' => $this->self_user
        ));
    }

    /**
     * Prepare default Route
     */
    protected function initRoute()
    {
        $this->tools->AddRoute('login', true);
        $this->tools->template = empty($this->tools->template) ? array('@default' => THEMES_DIR.$this->config->get('theme').DS.'templates'.DS) : array_merge($this->tools->template, array('@default' => THEMES_DIR.$this->config->get('theme').DS.'templates'.DS));
    }

    public function Process($router)
    {
        $index = $router[0];
        $page = $router[1];
        $namespace = ltrim($router[2], '@');
        $template = $router[3];
        $classRoot = explode('\\', Eayo::$router[$index]);
        $classRootCount = count($classRoot);
        unset($classRoot[$classRootCount - 1]);
        $classRoot = implode('\\', $classRoot);
        $this->InitTwig($template, $namespace);
        $is_markdown = false;
        $controller = null;
        try {
            switch (pathinfo($page)['extension']) {
                case 'php':
                    Eayo::$content = include $page;
                    if ($namespace === 'default') {
                        $controller = "\\App\\Controller\\".$index."Ctrl";
                        $controller = (new $controller)->index();
                    } else {
                        $controller = DS.$classRoot."\\Controller\\".$index."Ctrl";
                        $controller = (new $controller)->index();
                    }
                    break;
                case 'twig':
                    Eayo::$content = $this->twig->render('@'.$namespace.'/'.str_replace($template, '',$page));
                    if ($namespace === 'default') {
                        $controller = "\\App\\Controller\\".$index."Ctrl";
                        $controller = new $controller;
                        $controller->index();
                    } else {
                        $controller = DS.$classRoot."\\Controller\\".$index."Ctrl";
                        $controller = new $controller;
                        $controller->index();
                    }
                    break;
                case 'html' || 'htm' || 'md':
                    $is_markdown = pathinfo($page)['extension'] === 'md' ? true : false;
                    $fpage = fopen($page, 'r');
                    $page = fread($fpage, filesize($page));
                    Eayo::$content = $is_markdown ? nl2br($page) : $page;
                    fclose($fpage);
                    break;
            }
            $this->twig_vars = array_merge($this->twig_vars, array(
                'load_time' => number_format(microtime(true) - PERF_START, 3),
                'template' => $this->tools->rooturl.'/'.str_replace(DS, '/', str_replace(ROOT_DIR, '', $template.DS)),
                'content' => Eayo::$content,
                'ctrl' => $controller,
                'is_markdown' => $is_markdown
            ));
            $output = $this->twig->render('@'.$namespace.'/default.twig', $this->twig_vars);

            return $output;
        } catch (\Twig_Error_Loader $e) {
            throw new \Exception($e->getRawMessage(), 4054);
        }
    }

    public function login($emailid, $pass) {
        if (!isset($_SESSION['login_str'])) {
            $wanted_user;
            foreach ($this->config->getAllAccounts() as $key => $val){
                if(strcasecmp($emailid, $val['username']) === 0 || strcasecmp($emailid, $val['email']) === 0) {
                    $wanted_user = $key;
                }
            }
            if (password_verify($pass, $this->config->getAccount($wanted_user)['pass_hash'])) {
                $this->self_user = $this->config->getAccount($wanted_user);
                $_SESSION['user_id'] = preg_replace("/[^0-9]+/", "", $wanted_user);
                $_SESSION['username'] = preg_replace("/[^a-zA-Z0-9_\-]+/",
                                                     "",
                                                     $this->self_user['username']);
                $_SESSION['login_str'] = hash('sha512', $this->self_user['pass_hash'].$_SERVER['HTTP_USER_AGENT']);
                return true;
            } else {
                return false;
            }
        } else {
            return 'Vous ête déjà connecté.';
        }
    }

    public function register() {
        //echo password_hash($pass, PASSWORD_DEFAULT);
    }

    protected function sessionStart() {
        if (ini_set('session.use_only_cookies', 1) === '0') {
            throw new \Exception('Could not initiate a safe session (ini_set)', 145);
        }
        session_name('eayo_Session');
        session_set_cookie_params(0, '/', $_SERVER['SERVER_NAME'], isset($_SERVER['HTTPS']), true);
        session_start();
        session_regenerate_id(true);
    }

    /**
     * Return instance of Eayo class as singleton
     *
     * @return $_instance
     */
    public static function start()
    {
        if (is_null(static::$_instance)) {
            self::$_instance = new Eayo();
        }
        return static::$_instance;
    }

}
