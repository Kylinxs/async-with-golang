
<?php

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

use Symfony\Component\Routing\Exception\ExceptionInterface;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

class Services_ApiBridge
{
    protected $jitRequest;
    protected $routes;
    protected $context;

    public function __construct(JitFilter $jitRequest = null)
    {
        $this->jitRequest = $jitRequest;
        $this->routes = $this->prepareRoutes();
        $this->context = $this->prepareContext();
    }

    public function handle()
    {
        $route = $this->parseRoute();
        $request = $this->jitRequest->asArray();
        foreach ($route as $key => $value) {
            if (! in_array($key, ['controller', 'action', '_route']) && ! isset($request[$key])) {
                $request[$key] = $value;
                if ($key == 'confirmForm') {
                    $_POST[$key] = $value;
                }
            }
        }
        $this->jitRequest = new JitFilter($request);
        if ($route['_route'] == 'home') {
            $this->renderDocs();
        } elseif ($route['_route'] == 'docs') {
            $this->renderDocsYaml();
        } elseif ($route['_route'] == 'version') {
            $this->renderVersion();
        } else {
            $broker = TikiLib::lib('service')->getBroker();
            $broker->process($route['controller'], $route['action'], $this->jitRequest);
        }
    }

    public function getRoutes()
    {
        return $this->routes;
    }

    public function generateRoute($name, $args = [])
    {
        $generator = new UrlGenerator($this->routes, $this->context);
        $relative_path = $generator->generate($name, $args, UrlGenerator::RELATIVE_PATH);
        return preg_replace('/^(\.\.\/)*/', '', $relative_path);
    }

    protected function parseRoute()
    {
        try {
            $route = $this->jitRequest->route->none();
            $matcher = new UrlMatcher($this->routes, $this->context);
            return $matcher->match('/' . $route);
        } catch (ResourceNotFoundException $e) {
            TikiLib::lib('access')->display_error('API', $e->getMessage(), 404);
        } catch (RouteNotFoundException $e) {
            TikiLib::lib('access')->display_error('API', $e->getMessage(), 404);
        } catch (ExceptionInterface $e) {
            TikiLib::lib('access')->display_error('API', $e->getMessage(), 400);
        }
    }

    protected function prepareContext()
    {
        global $base_uri, $base_host, $url_host, $url_scheme, $prefs;
        $path_info = str_replace($base_host, '', $base_uri);
        if (false !== $pos = strpos($path_info, '?')) {
            $path_info = substr($path_info, 0, $pos);
        }
        return new RequestContext($base_uri, $_SERVER['REQUEST_METHOD'] ?? '', $url_host, $url_scheme, $prefs['http_port'] ? $prefs['http_port'] : 80, $prefs['https_port'] ? $prefs['https_port'] : 443, $path_info, http_build_query($_GET));
    }

    protected function prepareRoutes()
    {
        $routes = new RouteCollection();
        $routes->add('home', (new Route(''))->setMethods(['GET']));
        $routes->add('docs', (new Route('docs/{path}.yaml', ['_format' => 'yaml']))->setMethods(['GET']));
        $routes->add('version', (new Route('version'))->setMethods(['GET']));
        $routes->add('oauth-public-key', (new Route('oauth/public-key', ['controller' => 'oauthserver', 'action' => 'public_key']))->setMethods(['GET']));
        $routes->add('oauth-authorize', (new Route('oauth/authorize', ['controller' => 'oauthserver', 'action' => 'authorize']))->setMethods(['GET']));
        $routes->add('oauth-access_token', (new Route('oauth/access_token', ['controller' => 'oauthserver', 'action' => 'access_token']))->setMethods(['GET', 'POST']));
        $routes->add('categories', (new Route('categories', ['controller' => 'category', 'action' => 'list_categories']))->setMethods(['GET']));
        $routes->add('categories-create', (new Route('categories', ['controller' => 'category', 'action' => 'create']))->setMethods(['POST']));
        $routes->add('categories-update', (new Route('categories/{categId}', ['controller' => 'category', 'action' => 'update']))->setMethods(['POST']));
        $routes->add('categories-delete', (new Route('categories/{categId}', ['controller' => 'category', 'action' => 'remove']))->setMethods(['DELETE']));
        $routes->add('categorize', (new Route('categorize', ['controller' => 'category', 'action' => 'categorize']))->setMethods(['POST']));
        $routes->add('uncategorize', (new Route('uncategorize', ['controller' => 'category', 'action' => 'uncategorize']))->setMethods(['POST']));
        $routes->add('comments', (new Route('comments', ['controller' => 'comment', 'action' => 'list']))->setMethods(['GET']));
        $routes->add('comments-create', (new Route('comments', ['controller' => 'comment', 'action' => 'post', 'post' => 1]))->setMethods(['POST']));
        $routes->add('comments-update', (new Route('comments/{threadId}', ['controller' => 'comment', 'action' => 'edit', 'edit' => 1]))->setMethods(['POST']));
        $routes->add('comments-delete', (new Route('comments/{threadId}', ['controller' => 'comment', 'action' => 'remove', 'confirm' => 1]))->setMethods(['DELETE']));
        $routes->add('comments-lock', (new Route('comments/lock', ['controller' => 'comment', 'action' => 'lock', 'confirm' => 1]))->setMethods(['POST']));
        $routes->add('comments-unlock', (new Route('comments/unlock', ['controller' => 'comment', 'action' => 'unlock', 'confirm' => 1]))->setMethods(['POST']));
        $routes->add('comments-approve', (new Route('comments/{threadId}/approve', ['controller' => 'comment', 'action' => 'moderate', 'do' => 'approve', 'confirm' => 1]))->setMethods(['POST']));
        $routes->add('comments-reject', (new Route('comments/{threadId}/reject', ['controller' => 'comment', 'action' => 'moderate', 'do' => 'reject', 'confirm' => 1]))->setMethods(['POST']));
        $routes->add('comments-archive', (new Route('comments/{threadId}/archive', ['controller' => 'comment', 'action' => 'archive', 'do' => 'archive', 'confirm' => 1]))->setMethods(['POST']));
        $routes->add('comments-unarchive', (new Route('comments/{threadId}/unacrhive', ['controller' => 'comment', 'action' => 'archive', 'do' => 'unarchive', 'confirm' => 1]))->setMethods(['POST']));
        $routes->add('connect-new', (new Route('connect/new', ['controller' => 'connect_server', 'action' => 'new']))->setMethods(['POST']));
        $routes->add('connect-confirm', (new Route('connect/confirm', ['controller' => 'connect_server', 'action' => 'confirm']))->setMethods(['POST']));
        $routes->add('connect-receive', (new Route('connect/receive', ['controller' => 'connect_server', 'action' => 'receive']))->setMethods(['POST']));
        $routes->add('connect-cancel', (new Route('connect/cancel', ['controller' => 'connect_server', 'action' => 'cancel']))->setMethods(['POST']));
        $routes->add('export-sync', (new Route('export/sync', ['controller' => 'export', 'action' => 'sync_content']))->setMethods(['GET']));
        $routes->add('groups', (new Route('groups', ['controller' => 'group', 'action' => 'list']))->setMethods(['GET']));
        $routes->add('groups-add', (new Route('groups/add_users', ['controller' => 'group', 'action' => 'add_user', 'confirmForm' => 'y']))->setMethods(['POST']));
        $routes->add('groups-ban', (new Route('groups/ban_users', ['controller' => 'group', 'action' => 'ban_user', 'confirmForm' => 'y']))->setMethods(['POST']));
        $routes->add('groups-create', (new Route('groups', ['controller' => 'group', 'action' => 'new_group', 'confirmForm' => 'y']))->setMethods(['POST']));
        $routes->add('groups-delete', (new Route('groups/delete', ['controller' => 'group', 'action' => 'remove_groups', 'confirmForm' => 'y']))->setMethods(['POST']));
        $routes->add('groups-update', (new Route('groups/{olgroup}', ['controller' => 'group', 'action' => 'modify_group', 'confirmForm' => 'y']))->setMethods(['POST']));
        $routes->add('search-lookup', (new Route('search/lookup', ['controller' => 'search', 'action' => 'lookup']))->setMethods(['GET']));
        $routes->add('search-process-queue', (new Route('search/process-queue', ['controller' => 'search', 'action' => 'process_queue']))->setMethods(['POST']));
        $routes->add('search-rebuild', (new Route('search/rebuild', ['controller' => 'search', 'action' => 'rebuild']))->setMethods(['POST']));
        $routes->add('tabulars', (new Route('tabulars', ['controller' => 'tabular', 'action' => 'manage']))->setMethods(['GET']));
        $routes->add('tabulars-view', (new Route('tabulars/{tabularId}', ['controller' => 'tabular', 'action' => 'edit']))->setMethods(['GET']));
        $routes->add('tabulars-export', (new Route('tabulars/{tabularId}/export', ['controller' => 'tabular', 'action' => 'export_full_csv']))->setMethods(['GET']));
        $routes->add('tabulars-import', (new Route('tabulars/{tabularId}/import', ['controller' => 'tabular', 'action' => 'import_csv']))->setMethods(['POST']));
        $routes->add('trackers', (new Route('trackers', ['controller' => 'tracker', 'action' => 'list_trackers']))->setMethods(['GET']));
        $routes->add('trackers-create', (new Route('trackers', ['controller' => 'tracker', 'action' => 'replace', 'confirm' => 1]))->setMethods(['POST']));
        $routes->add('trackers-view', (new Route('trackers/{trackerId}', ['controller' => 'tracker', 'action' => 'list_items', 'offset' => -1, 'maxRecords' => -1]))->setMethods(['GET']));
        $routes->add('trackers-update', (new Route('trackers/{trackerId}', ['controller' => 'tracker', 'action' => 'replace', 'confirm' => 1]))->setMethods(['POST']));
        $routes->add('trackers-delete', (new Route('trackers/{trackerId}', ['controller' => 'tracker', 'action' => 'remove', 'confirm' => 1]))->setMethods(['DELETE']));
        $routes->add('trackers-clear', (new Route('trackers/{trackerId}/clear', ['controller' => 'tracker', 'action' => 'clear', 'confirmForm' => 'y']))->setMethods(['POST']));
        $routes->add('trackers-duplicate', (new Route('trackers/{trackerId}/duplicate', ['controller' => 'tracker', 'action' => 'duplicate', 'confirm' => 1]))->setMethods(['POST']));
        $routes->add('trackers-dump', (new Route('trackers/{trackerId}/dump', ['controller' => 'tracker', 'action' => 'dump_items']))->setMethods(['GET']));
        $routes->add('trackers-export', (new Route('trackers/{trackerId}/export', ['controller' => 'tracker', 'action' => 'export_profile']))->setMethods(['GET']));
        $routes->add('trackerfields', (new Route('trackers/{trackerId}/fields', ['controller' => 'tracker', 'action' => 'list_fields']))->setMethods(['GET']));
        $routes->add('trackerfields-create', (new Route('trackers/{trackerId}/fields', ['controller' => 'tracker', 'action' => 'add_field']))->setMethods(['POST']));
        $routes->add('trackerfields-update', (new Route('trackers/{trackerId}/fields/{fieldId}', ['controller' => 'tracker', 'action' => 'edit_field']))->setMethods(['POST']));
        $routes->add('trackerfields-delete', (new Route('trackers/{trackerId}/fields', ['controller' => 'tracker', 'action' => 'remove_fields', 'confirm' => 1]))->setMethods(['DELETE']));
        $routes->add('trackerfields-export', (new Route('trackers/{trackerId}/fields/export', ['controller' => 'tracker', 'action' => 'export_fields']))->setMethods(['GET']));
        $routes->add('trackeritems-view', (new Route('trackers/{trackerId}/items/{itemId}', ['controller' => 'tracker', 'action' => 'view']))->setMethods(['GET']));
        $routes->add('trackeritems-clone', (new Route('trackers/{trackerId}/items/{itemId}/clone', ['controller' => 'tracker', 'action' => 'clone_item']))->setMethods(['POST']));
        $routes->add('trackeritems-create', (new Route('trackers/{trackerId}/items', ['controller' => 'tracker', 'action' => 'insert_item']))->setMethods(['POST']));
        $routes->add('trackeritems-update', (new Route('trackers/{trackerId}/items/{itemId}', ['controller' => 'tracker', 'action' => 'update_item']))->setMethods(['POST']));
        $routes->add('trackeritems-delete', (new Route('trackers/{trackerId}/items/{itemId}', ['controller' => 'tracker', 'action' => 'remove_item']))->setMethods(['DELETE']));
        $routes->add('trackeritems-status', (new Route('trackers/{trackerId}/items/{itemId}/status', ['controller' => 'tracker', 'action' => 'update_item_status', 'confirm' => 1]))->setMethods(['POST']));
        $routes->add('translations', (new Route('translations/{type}/{source}', ['controller' => 'translation', 'action' => 'manage']))->setMethods(['GET']));
        $routes->add('translations-attach', (new Route('translations/{type}/{source}/attach', ['controller' => 'translation', 'action' => 'attach']))->setMethods(['POST']));
        $routes->add('translations-detach', (new Route('translations/{type}/{source}/detach', ['controller' => 'translation', 'action' => 'detach', 'confirm' => 1]))->setMethods(['POST']));
        $routes->add('translate', (new Route('translate', ['controller' => 'translation', 'action' => 'translate']))->setMethods(['POST']));
        $routes->add('users', (new Route('users', ['controller' => 'user', 'action' => 'list_users', 'offset' => 0, 'maxRecords' => -1]))->setMethods(['GET']));
        $routes->add('users-register', (new Route('users', ['controller' => 'user', 'action' => 'register']))->setMethods(['POST']));
        $routes->add('users-info', (new Route('users/{username}', ['controller' => 'user', 'action' => 'info']))->setMethods(['GET']));
        $routes->add('users-delete', (new Route('users/delete', ['controller' => 'user', 'action' => 'remove_users', 'confirmForm' => 'y']))->setMethods(['POST']));
        $routes->add('users-groups', (new Route('users/groups', ['controller' => 'user', 'action' => 'manage_groups', 'confirmForm' => 'y']))->setMethods(['POST']));
        $routes->add('users-email-wikipage', (new Route('users/email-wikipage', ['controller' => 'user', 'action' => 'email_wikipage', 'confirmForm' => 'y']))->setMethods(['POST']));
        $routes->add('users-send-message', (new Route('users/send-message', ['controller' => 'user', 'action' => 'send_message', 'confirmForm' => 'y']))->setMethods(['POST']));
        $routes->add('users-message-count', (new Route('message-count', ['controller' => 'user', 'action' => 'get_message_count']))->setMethods(['GET']));
        $routes->add('wiki', (new Route('wiki', ['controller' => 'wiki', 'action' => 'pages']))->setMethods(['GET']));
        $routes->add('wiki-create', (new Route('wiki', ['controller' => 'wiki', 'action' => 'create_update_page', 'create' => 1]))->setMethods(['POST']));
        $routes->add('wiki-view', (new Route('wiki/page/{page}', ['controller' => 'wiki', 'action' => 'get_page']))->setMethods(['GET']));
        $routes->add('wiki-update', (new Route('wiki/page/{page}', ['controller' => 'wiki', 'action' => 'create_update_page', 'update' => 1]))->setMethods(['POST']));
        $routes->add('wiki-delete', (new Route('wiki/delete', ['controller' => 'wiki', 'action' => 'remove_pages', 'confirmForm' => 'y', 'version' => 'all']))->setMethods(['POST']));
        $routes->add('wiki-title', (new Route('wiki/title', ['controller' => 'wiki', 'action' => 'title', 'confirmForm' => 'y']))->setMethods(['POST']));
        $routes->add('wiki-lock', (new Route('wiki/lock', ['controller' => 'wiki', 'action' => 'lock_pages', 'confirmForm' => 'y']))->setMethods(['POST']));
        $routes->add('wiki-unlock', (new Route('wiki/unlock', ['controller' => 'wiki', 'action' => 'unlock_pages', 'confirmForm' => 'y']))->setMethods(['POST']));
        $routes->add('wiki-preview', (new Route('wiki/preview', ['controller' => 'wiki', 'action' => 'preview']))->setMethods(['POST']));
        $routes->add('wiki-zip', (new Route('wiki/zip', ['controller' => 'wiki', 'action' => 'zip', 'confirmForm' => 'y']))->setMethods(['POST']));
        $routes->add('wiki-versions-delete', (new Route('wiki/{page}/delete', ['controller' => 'wiki', 'action' => 'remove_page_versions', 'confirmForm' => 'y']))->setMethods(['POST']));
        return $routes;
    }

    protected function renderDocs()
    {
        global $base_url;
        $smarty = TikiLib::lib('smarty');
        $smarty->assign('asset_path', $base_url . 'vendor_bundled/vendor/swagger-api/swagger-ui/dist/');
        echo $smarty->fetch('api/docs.tpl');
    }

    protected function renderDocsYaml()
    {
        global $base_url, $tikipath;
        $path = $this->jitRequest->path->xss();
        $base = $tikipath . 'templates/api/docs';
        $real = realpath($base . '/' . str_replace('-', '/', $path) . '.yaml');
        if (empty($path) || ! strstr(dirname($real), $base)) {
            $real = $base . '/index.yaml';
        }
        if (is_file($real)) {
            $docs = file_get_contents($real);
            $docs = str_replace('{server-url}', $base_url . 'api/', $docs);
            echo $docs;
        }
    }

    protected function renderVersion()
    {
        global $TWV;
        echo json_encode(['version' => $TWV->version]);
    }
}