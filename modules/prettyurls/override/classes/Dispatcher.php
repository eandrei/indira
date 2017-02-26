<?php
/**
 * FMM PrettyURLs
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @category  FMM Modules
 * @package   PrettyURLs
 * @author    FMM Modules
 * @copyright Copyright 2016 © FMM Modules All right reserved
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*/

class Dispatcher extends DispatcherCore
{
	protected function __construct()
	{
		parent::__construct();
		$this->loadRoutes();
	}

	public $default_routes = array(
		'category_rule' => array(
			'controller' =>	'category',
			'rule' =>		'{rewrite}',
			'keywords' => array(
				'id' =>				array('regexp' => '[0-9]+'),
				'rewrite' =>		array('regexp' => '[_a-zA-Z0-9-\pL]*', 'param' => 'category_rewrite'),
				'meta_keywords' =>	array('regexp' => '[_a-zA-Z0-9-\pL]*'),
				'meta_title' =>		array('regexp' => '[_a-zA-Z0-9-\pL]*'),
			),
		),
		'supplier_rule' => array(
			'controller' =>	'supplier',
			'rule' =>		'supplier/{rewrite}',
			'keywords' => array(
				'id' =>				array('regexp' => '[0-9]+'),
				'rewrite' =>		array('regexp' => '[_a-zA-Z0-9-\pL]*', 'param' => 'supplier_rewrite'),
				'meta_keywords' =>	array('regexp' => '[_a-zA-Z0-9-\pL]*'),
				'meta_title' =>		array('regexp' => '[_a-zA-Z0-9-\pL]*'),
			),
		),
		'manufacturer_rule' => array(
			'controller' =>	'manufacturer',
			'rule' =>		'manufacturer/{rewrite}',
			'keywords' => array(
				'id' =>				array('regexp' => '[0-9]+'),
				'rewrite' =>		array('regexp' => '[_a-zA-Z0-9-\pL]*', 'param' => 'manufacturer_rewrite'),
				'meta_keywords' =>	array('regexp' => '[_a-zA-Z0-9-\pL]*'),
				'meta_title' =>		array('regexp' => '[_a-zA-Z0-9-\pL]*'),
			),
		),
		'cms_rule' => array(
			'controller' =>	'cms',
			'rule' =>		'content/{rewrite}',
			'keywords' => array(
				'id' =>				array('regexp' => '[0-9]+'),
				'rewrite' =>		array('regexp' => '[_a-zA-Z0-9-\pL]*', 'param' => 'cms_rewrite'),
				'meta_keywords' =>	array('regexp' => '[_a-zA-Z0-9-\pL]*'),
				'meta_title' =>		array('regexp' => '[_a-zA-Z0-9-\pL]*'),
			),
		),
		'cms_category_rule' => array(
			'controller' =>	'cms',
			'rule' =>		'content/category/{rewrite}',
			'keywords' => array(
				'id' =>				array('regexp' => '[0-9]+'),
				'rewrite' =>		array('regexp' => '[_a-zA-Z0-9-\pL]*', 'param' => 'cms_category_rewrite'),
				'meta_keywords' =>	array('regexp' => '[_a-zA-Z0-9-\pL]*'),
				'meta_title' =>		array('regexp' => '[_a-zA-Z0-9-\pL]*'),
			),
		),
		'module' => array(
			'controller' =>	null,
			'rule' =>		'module/{module}{/:controller}',
			'keywords' => array(
				'module' =>			array('regexp' => '[_a-zA-Z0-9_-]+', 'param' => 'module'),
				'controller' =>		array('regexp' => '[_a-zA-Z0-9_-]+', 'param' => 'controller'),
			),
			'params' => array(

				'fc' => 'module',
			),
		),
		'product_rule' => array(
			'controller' =>	'product',
			'rule' =>		'{categories:/}{rewrite}',
			'keywords' => array(
				'id' =>				array('regexp' => '[0-9]+'),
				'rewrite' =>		array('regexp' => '[_a-zA-Z0-9-\pL]*', 'param' => 'product_rewrite'),
				'ean13' =>			array('regexp' => '[0-9\pL]*'),
				'category' =>		array('regexp' => '[_a-zA-Z0-9-\pL]*'),
				'categories' =>		array('regexp' => '[/_a-zA-Z0-9-\pL]*'),
				'reference' =>		array('regexp' => '[_a-zA-Z0-9-\pL]*'),
				'meta_keywords' =>	array('regexp' => '[_a-zA-Z0-9-\pL]*'),
				'meta_title' =>		array('regexp' => '[_a-zA-Z0-9-\pL]*'),
				'manufacturer' =>	array('regexp' => '[_a-zA-Z0-9-\pL]*'),
				'supplier' =>		array('regexp' => '[_a-zA-Z0-9-\pL]*'),
				'price' =>			array('regexp' => '[0-9\.,]*'),
				'tags' =>			array('regexp' => '[a-zA-Z0-9-\pL]*'),
			),
		),
		/*	Must be after the product and category rules in order to avoid conflict	*/
		'layered_rule' => array(
			'controller' =>	'category',
			'rule' =>		'{rewrite}/filter{selected_filters}',
			'keywords' => array(
				'id' =>				array('regexp' => '[0-9]+'),
				/*	Selected filters is used by the module blocklayered	*/
				'selected_filters' =>		array('regexp' => '.*', 'param' => 'selected_filters'),
				'rewrite' => array('regexp' => '[_a-zA-Z0-9-\pL]*', 'param' => 'category_rewrite'),
				'meta_keywords' => array('regexp' => '[_a-zA-Z0-9-\pL]*'),
				'meta_title' =>	array('regexp' => '[_a-zA-Z0-9-\pL]*'),
			),
		),
	);

	protected function loadRoutes($id_shop = null)
	{
		/* Old Category URL Checking */
		$cat_pattern = '/.*?\/([0-9]+)\-([_a-zA-Z0-9-\pL]*)/';
		preg_match($cat_pattern, $_SERVER['REQUEST_URI'], $url_array);
		if (!empty($url_array))
		{
			if (!strstr($_SERVER['REQUEST_URI'], '/content/'))
				$this->default_routes['category_rule']['rule'] = '{rewrite}';
		}
		/* Old Product URL Checking */
		$prod_pattern = '/.*?\/([0-9]+)\-([_a-zA-Z0-9-\pL]*)/';
		preg_match($prod_pattern, $_SERVER['REQUEST_URI'], $pro_array);
		if (!empty($pro_array))
			$this->default_routes['product_rule']['rule'] = '{categories:/}{rewrite}';

		/* Old Supplier URL Checking */
		$sup_pattern = '/.*?([0-9]+)\_\_([_a-zA-Z0-9-\pL]*)/';
		preg_match($sup_pattern, $_SERVER['REQUEST_URI'], $sup_array);

		if (!empty($sup_array))
			$this->default_routes['supplier_rule']['rule'] = '{rewrite}';

		/* Old Manufacturer URL Checking */
		$man_pattern = '/.*?([0-9]+)\_([_a-zA-Z0-9-\pL]*)/';
		preg_match($man_pattern, $_SERVER['REQUEST_URI'], $man_array);
		if (!empty($man_array))
			$this->default_routes['manufacturer_rule']['rule'] = '{rewrite}';

		/* Old CMS Page URL Checking */
		$cms_pattern = '/.*?content\/([0-9]+)\-([_a-zA-Z0-9-\pL]*)/';
		preg_match($cms_pattern, $_SERVER['REQUEST_URI'], $cms_array);
		if (!empty($cms_array))
			$this->default_routes['cms_rule']['rule'] = 'content/{rewrite}';
		/* Old CMS Category URL Checking */
		$cms_cat_pattern = '/.*?content\/category\/([0-9]+)\-([_a-zA-Z0-9-\pL]*)/';
		preg_match($cms_cat_pattern, $_SERVER['REQUEST_URI'], $cms_cat_array);
		if (!empty($cms_cat_array))
		{
			if (strstr($_SERVER['REQUEST_URI'], '/content/category/'))
				$this->default_routes['cms_category_rule']['rule'] = 'content/category/{rewrite}';
		}
		/* Load custom routes from modules */
		$modules_routes = Hook::exec('moduleRoutes', array('id_shop' => $id_shop), null, true, false);
		if (is_array($modules_routes) && count($modules_routes))
			foreach ($modules_routes as $module_route)
				foreach ($module_route as $route => $route_details)
					if (array_key_exists('controller', $route_details) && array_key_exists('rule', $route_details)
						&& array_key_exists('keywords', $route_details) && array_key_exists('params', $route_details))
					{
						if (!isset($this->default_routes[$route]))
						$this->default_routes[$route] = array();
						$this->default_routes[$route] = array_merge($this->default_routes[$route], $route_details);
					}
		/* Set default routes */
		foreach (Language::getLanguages() as $lang)
			foreach ($this->default_routes as $id => $route)
				$this->addRoute(
					$id,
					$route['rule'],
					$route['controller'],
					$lang['id_lang'],
					$route['keywords'],
					isset($route['params']) ? $route['params'] : array(),
					$id_shop
				);
		/* Load the custom routes prior the defaults to avoid infinite loops */
		if ($this->use_routes)
		{
			/* Load routes from meta table */
			$sql = 'SELECT m.page, ml.url_rewrite, ml.id_lang
					FROM `'._DB_PREFIX_.'meta` m
					LEFT JOIN `'._DB_PREFIX_.'meta_lang` ml ON (m.id_meta = ml.id_meta'.Shop::addSqlRestrictionOnLang('ml', (int)$id_shop).')
					ORDER BY LENGTH(ml.url_rewrite) DESC';
			if ($results = Db::getInstance()->executeS($sql))
				foreach ($results as $row)
				{
					if ($row['url_rewrite'])
						$this->addRoute($row['page'], $row['url_rewrite'], $row['page'], $row['id_lang'], array(), array(), $id_shop);
				}
			/* Set default empty route if  no empty route (that's weird I know) */
			if (!$this->empty_route)
				$this->empty_route = array(
					'routeID' =>	'index',
					'rule' =>		'',
					'controller' =>	'index',
				);
			/* Load custom routes */

			foreach ($this->default_routes as $route_id => $route_data)
				if ($custom_route = Configuration::get('PS_ROUTE_'.$route_id, null, null, $id_shop))
					foreach (Language::getLanguages() as $lang)
						$this->addRoute(
							$route_id,
							$custom_route,
							$route_data['controller'],
							$lang['id_lang'],
							$route_data['keywords'],
							isset($route_data['params']) ? $route_data['params'] : array(),
							$id_shop
						);
		}
	}

	public function getController($id_shop = null)
	{
		if (defined('_PS_ADMIN_DIR_'))
			$_GET['controllerUri'] = Tools::getvalue('controller');
		if ($this->controller)
		{
			$_GET['controller'] = $this->controller;
			return $this->controller;
		}

		if (isset(Context::getContext()->shop) && $id_shop === null)
			$id_shop = (int)Context::getContext()->shop->id;
		$controller = Tools::getValue('controller');

		if (isset($controller) && is_string($controller) && preg_match('/^([0-9a-z_-]+)\?(.*)=(.*)$/Ui', $controller, $m))
		{
			$controller = $m[1];
			if (isset($_GET['controller']))
				$_GET[$m[2]] = $m[3];
			else if (isset($_POST['controller']))
				$_POST[$m[2]] = $m[3];
		}

		if (!Validate::isControllerName($controller))
			$controller = false;
		// Use routes ? (for url rewriting)
		if ($this->use_routes && !$controller && !defined('_PS_ADMIN_DIR_'))
		{
			if (!$this->request_uri)
				return Tools::strtolower($this->controller_not_found);
			$controller = $this->controller_not_found;
			if (!preg_match('/\.(gif|jpe?g|png|css|js|ico)$/i', parse_url($this->request_uri, PHP_URL_PATH)))
			{
				// Add empty route as last route to prevent this greedy regexp to match request uri before right time
				if ($this->empty_route)
					$this->addRoute($this->empty_route['routeID'], $this->empty_route['rule'], $this->empty_route['controller'],
					Context::getContext()->language->id, array(), array(), $id_shop);
				//FIX for blogs issues
				if (preg_match('/^\/blog.html*/', $this->request_uri) || preg_match('/^\/blog*/', $this->request_uri))
				{
					$captcha_url = explode('?', $this->request_uri);
					$captcha_url = end($captcha_url);
					foreach ($this->routes[$id_shop][Context::getContext()->language->id] as $route)
					{
						if ($route['rule'] == 'blog.html' && !preg_match('/captchaimage/', $captcha_url))
						{
							$_GET['module'] = $route['params']['module'];
							$_GET['fc'] = 'module';
							$controller = $route['controller'];
							$this->front_controller = self::FC_MODULE;
						}
						elseif ($route['controller'] == 'blog' && preg_match('/captchaimage/', $captcha_url))
						{
							$_GET['module'] = $route['params']['module'];
							$_GET['fc'] = 'module';
							$controller = $route['controller'];
							$this->front_controller = self::FC_MODULE;
						}
					}
				}

				if (isset($this->routes[$id_shop][Context::getContext()->language->id]))
					foreach ($this->routes[$id_shop][Context::getContext()->language->id] as $route)
						if (preg_match($route['regexp'], $this->request_uri, $m))
						{
							// Route found ! Now fill $_GET with parameters of uri
							foreach ($m as $k => $v)
								if (!is_numeric($k))
									$_GET[$k] = $v;
							$controller = $route['controller'] ? $route['controller'] : $_GET['controller'];
							if (!empty($route['params']))
								foreach ($route['params'] as $k => $v)
									$_GET[$k] = $v;
							// A patch for module friendly urls
							if (preg_match('#module-([a-z0-9_-]+)-([a-z0-9_]+)$#i', $controller, $m))
							{
								$_GET['module'] = $m[1];
								$_GET['fc'] = 'module';
								$controller = $m[2];
							}

							if (isset($_GET['fc']) && $_GET['fc'] == 'module')
								$this->front_controller = self::FC_MODULE;
							break;
						}
			}

		$req_uri = explode('/', $this->request_uri);
			if (preg_match('/\?/', $req_uri[1]))
			{
				$req_uri_qmark = explode('?', $req_uri[1]);
				$req_uri[1] = $req_uri_qmark[0];
			}
			if ($controller == 'index' || preg_match('/^\/index.php(?:\?.*)?$/', $this->request_uri) || $req_uri[1] == '')
				$controller = (_PS_VERSION_ >= '1.6.0' || _PS_VERSION_ >= '1.6.0.0') ? $this->useDefaultController() : $this->default_controller;
				$check_url_type_existance = (int)$this->getKeyExistance($req_uri[1]);
				$get_controller_page = $this->getControllerPageById($check_url_type_existance);
				if ($check_url_type_existance > 0)
				$controller = $get_controller_page;
		}

		//Fix for Category/Products with parameters
		if (preg_match('/\?/', $this->request_uri) && !preg_match('/module/', $this->request_uri))
		{
			$req_uri_qmark = explode('?', $this->request_uri);
			if (Tools::substr($req_uri_qmark[0], -1) == '/')
				$req_uri_qmark[0] = Tools::substr($req_uri_qmark[0], 0, -1);
			$cat_or_product = explode('/', $req_uri_qmark[0]);
			$request = end($cat_or_product);
			if (preg_match('/html/', $request))
			{
				$request = explode('.', $request);
				$request = $request[0];
			}

			$callback = (int)$this->getProductExistance($request);
			if ($callback > 0)
			{
				$controller = 'product';
				$_POST['id_product'] = $callback;
			}
		}
		elseif (!preg_match('/module/', $this->request_uri) && $controller == 'category')
		{
			$cat_uri_exist_case = explode('/', $this->request_uri);
			$cat_uri_exist_case = array_filter($cat_uri_exist_case);
			$cat_uri_exist_case = end($cat_uri_exist_case);
			$cat_uri_exist = (int)$this->getCategoryId($cat_uri_exist_case);
			if ($cat_uri_exist <= 0)
			{
				$callback = (int)$this->getProductExistance($cat_uri_exist_case);
				if ($callback > 0)
				{
					$controller = 'product';
					$_POST['id_product'] = $callback;
				}
			}
		}

		if ($controller == 'pagenotfound' || $controller == 404)
		{
			$req_uri = explode('/', $this->request_uri);
			$request = end($req_uri);
			$req_uri_qmark = explode('?', $request);
			$clearify_request = str_replace('-', ' ', $req_uri_qmark[0]);
			$manu_existance = (int)$this->getKeyExistanceManuf($clearify_request);
			if ($manu_existance > 0)
			{
				$controller = 'manufacturer';
				$_POST['id_manufacturer'] = $manu_existance;
			}
		}

		if ($controller == 'pagenotfound' || $controller == 404 && preg_match('/content_only/', $this->request_uri))
		{
				$explode_url_params = explode('/', $this->request_uri);
				$explode_url_params = end($explode_url_params);
				$explode_url = explode('?', $explode_url_params);
				$check_for_cms_404 = (int)$this->getKeyExistanceCMS($explode_url[0]);
				if ($check_for_cms_404 > 0)
				{
					$controller = 'cms';
					$_POST['id_cms'] = $check_for_cms_404;
				}
		}

		if ($controller == 'pagenotfound' || $controller == 404)
			$check_url_type_existance_cms = (int)$this->getKeyExistanceCMS($req_uri[1]);
		else
			$check_url_type_existance_cms = 0;
		if ($check_url_type_existance_cms > 0 && !preg_match('/^\/blog.html*/', $this->request_uri) && !preg_match('/^\/blog*/', $this->request_uri))
		{
			$controller = 'cms';
			$_POST['id_cms'] = $check_url_type_existance_cms;
		}

		if ($controller == 'pagenotfound' || $controller == 404 || $controller == 'category')
		{
			$request_uri_match = explode('/', $this->request_uri);
			$request_uri_match = array_filter($request_uri_match);
			$request_uri_match = end($request_uri_match);
			$check_url_type_existance = (int)$this->getKeyExistance($request_uri_match);
			$get_controller_page = $this->getControllerPageById($check_url_type_existance);
			if ($check_url_type_existance > 0)
				$controller = $get_controller_page;
		}

		if (!preg_match('/\?/', $this->request_uri) && preg_match('/.*?\/([0-9]+)\-([_a-zA-Z0-9-\pL]*)/', $this->request_uri)
			&& $controller == 'pagenotfound' || $controller == 404)
		{
			$product_request = explode('/', $this->request_uri);
			$product_request = end($product_request);
			$product_uri = explode('/', $this->request_uri);
			if (!preg_match('/module/', $this->request_uri) && !preg_match('/blog/', $this->request_uri))
			{
				$controller = 'product';
				$_POST['id_product'] = $product_uri[0];
			}
		}

		if ($controller == 'pagenotfound' || $controller == 404 && preg_match('/\?/', $this->request_uri))
		{
			$cat_id_req_uri = explode('/', $this->request_uri);
			$cat_id_req_uri = array_filter($cat_id_req_uri);
			$cat_id_req_uri = end($cat_id_req_uri);
			$cat_id_req_uri = explode('?', $cat_id_req_uri);
			$cat_id_req_uri = $cat_id_req_uri[0];
			$cat_id_req_uri = explode('.', $cat_id_req_uri);
			$cat_id_req_uri = $cat_id_req_uri[0];
			$get_cat_page_id = (int)$this->getCategoryId($cat_id_req_uri);
			if (!preg_match('/module/', $this->request_uri))
			{
				$_POST['id_category'] = $get_cat_page_id;
				$controller = 'category';
			}
		}

		if ($controller == 'category' && $this->request_uri == '/blog')
		{
			foreach ($this->routes[$id_shop][Context::getContext()->language->id] as $route)
			{
				if ($route['rule'] == 'blog')
				{
					$_GET['module'] = $route['params']['module'];
					$_GET['fc'] = 'module';
					$controller = $route['controller'];
					$this->front_controller = self::FC_MODULE;
				}
			}

			if ($controller == 'category' || $controller == 'blog' && preg_match('/blog/', $this->request_uri))
			{
				$_url_query = explode('/', $this->request_uri);
				$_url_query = end($_url_query);
				$_url_query_split = explode('-', $_url_query);
				$_url_query_split = str_split(end($_url_query_split));
				$_GET['module'] = 'prestablog';
				$controller = 'blog';
				if (preg_match('/^\d+$/', end($_url_query_split)))
				{
						if ($_url_query_split[0] !== 'n' && $_url_query_split[0] !== 'c')
						{
							$pager_url = explode('p', $_url_query);
							$_GET['start']  = $pager_url[0];
							$_GET['p']  = $pager_url[1];
						}
						elseif ($_url_query_split[0] == 'n' && $_url_query_split[0] !== 'c')
						{
							$_url_query_split1 = explode('-', $_url_query);
							$news_url = explode('n', end($_url_query_split1));
							$_GET['n']  = $news_url[1];
							$_GET['id'] = $news_url[1];
						}
						else
						{
							$_url_query_split2 = explode('-', $_url_query);
							if (preg_match('/p/', $_url_query_split2[1]))
							{
								$c_pager_url = explode('p', $_url_query_split2[1]);
								$_GET['start']  = $c_pager_url[0];
								$_GET['p']  = $c_pager_url[1];
								$c_url = explode('c', $_url_query_split2[2]);
								$_GET['c']  = $c_url[1];
							}
							else
							{
								// for  blog categories
								$c_url = explode('c', end($_url_query_split2));
								$_GET['c']  = $c_url[1];
							}
						}
				}
				$this->front_controller = self::FC_MODULE;
			}
		}

		if ($controller == 'product' && preg_match('/blog/', $this->request_uri))
		{
			$_product_query = explode('/', $this->request_uri);
			$_product_query = end($_product_query);
			$_product_query_id = explode('-', $_product_query);
			$_product_query_id = (int)$_product_query_id[0];
			$_return_rewrite = $_product_query_id.'-'.$this->getProductExistanceByRewrite($_product_query_id);
			if ($_product_query != $_return_rewrite)
			{
				foreach ($this->routes[$id_shop][Context::getContext()->language->id] as $route)
				{
					if (preg_match('/blog/', $route['regexp']) && $route['controller'] == 'category')
					{
						$_GET['module'] = $route['params']['module'];
						$_GET['fc'] = 'module';
						$controller = $route['controller'];
						$this->front_controller = self::FC_MODULE;
						$_POST['blog_id_category'] = $_product_query_id;
						$_POST['id_blogcategory'] = $_product_query_id;
						$_POST['rewrite'] = $_product_query;
						unset($_GET['product_rewrite']);
					}
				}
			}
			if ($controller == 'product' || $controller == 'blog' && preg_match('/blog/', $this->request_uri))
			{
				$_url_query = explode('/', $this->request_uri);
				$_url_query = end($_url_query);
				$_url_query_split = explode('-', $_url_query);
				$_url_query_split = str_split(end($_url_query_split));
				$_GET['module'] = 'prestablog';
				$controller = 'blog';
				if (preg_match('/^\d+$/', end($_url_query_split)))
				{
						if ($_url_query_split[0] !== 'n' && $_url_query_split[0] !== 'c')
						{
							$pager_url = explode('p', $_url_query);
							$_GET['start']  = $pager_url[0];
							$_GET['p']  = $pager_url[1];
						}
						elseif ($_url_query_split[0] == 'n' && $_url_query_split[0] !== 'c')
						{
							$_url_query_split1 = explode('-', $_url_query);
							$news_url = explode('n', end($_url_query_split1));
							$_GET['n']  = $news_url[1];
							$_GET['id'] = $news_url[1];
						}
						else
						{
							$_url_query_split2 = explode('-', $_url_query);
							if (preg_match('/p/', $_url_query_split2[1]))
							{
								$c_pager_url = explode('p', $_url_query_split2[1]);
								$_GET['start']  = $c_pager_url[0];
								$_GET['p']  = $c_pager_url[1];
								$c_url = explode('c', $_url_query_split2[2]);
								$_GET['c']  = $c_url[1];
							}
							else
							{
								// for  blog categories
								$c_url = explode('c', end($_url_query_split2));
								$_GET['c']  = $c_url[1];
							}
						}
				}
				$this->front_controller = self::FC_MODULE;
			}
		}

		if (preg_match('/\?/', $this->request_uri) && preg_match('/productcomments/', $this->request_uri))
		{
			if ($controller == 'pagenotfound' || $controller == '404' && !preg_match('/token/', $this->request_uri))
			{
				$_GET['module'] = 'productcomments';
				$controller = 'default';
				$this->front_controller = self::FC_MODULE;
			}
		}
		//Paypal issue fix
		if ($controller == 'pagenotfound' && preg_match('/paypal/', $this->request_uri))
		{
			$req_uri = explode('/', $this->request_uri);
			$request = end($req_uri);
			$req_uri_qmark = explode('?', $request);
			$clearify_request = str_replace('-', ' ', $req_uri_qmark[0]);
			$_GET['module'] = 'paypal';
			$controller = $clearify_request;
			$this->front_controller = self::FC_MODULE;
		}

		if ($controller == 'pagenotfound' && preg_match('/quickpay/', $this->request_uri))
		{
			$_GET['module'] = 'quickpay';
			$controller = 'complete';
			$this->front_controller = self::FC_MODULE;
		}
		if ($controller == 'pagenotfound' && preg_match('/loyalty/', $this->request_uri))
		{
			$_GET['module'] = 'loyalty';
			$controller = 'default';
			$_GET['process'] = 'summary';
			$this->front_controller = self::FC_MODULE;
		}
		if ($controller == 'pagenotfound' && preg_match('/bowbuy/', $this->request_uri))
		{
			$controller = 'payment';
			$_GET['fc'] = 'module';
			$_GET['module'] = 'bowbuy';
			$this->front_controller = self::FC_MODULE;
		}
		if ($controller == 'pagenotfound' && preg_match('/socialloginizer/', $this->request_uri))
		{
			$_GET['module'] = 'socialloginizer';
			$controller = 'facebook';
			$_GET['type'] = 'fb';
			$this->front_controller = self::FC_MODULE;
		}
		if ($controller == 'pagenotfound' && preg_match('/mollie/', $this->request_uri))
		{
			$_GET['module'] = 'mollie';
			$controller = 'payment';
			$_GET['method'] = 'ideal';
			$_GET['issuer'] = 'ideal_RABONL2U';
			$this->front_controller = self::FC_MODULE;
		}
		if ($controller == 'pagenotfound' && preg_match('/blockwishlist/', $this->request_uri) && preg_match('/deletelist/', $this->request_uri))
		{
			$req_uri = explode('?', $this->request_uri);
			$request = end($req_uri);
			$ids = explode('&', $request);
			$ids = explode('=', $ids[3]);
			$_GET['module'] = 'blockwishlist';
			$controller = 'mywishlist';
			$_GET['deleted'] = 1;
			$_GET['myajax']  = 1;
			$_GET['id_wishlist']  = end($ids);
			$_GET['action'] = 'deletelist';
			$this->front_controller = self::FC_MODULE;
		}
		if ($controller == 'pagenotfound' && preg_match('/blockwishlist/', $this->request_uri) && preg_match('/setdefault/', $this->request_uri))
		{
			$req_uri = explode('?', $this->request_uri);
			$request = end($req_uri);
			$ids = explode('&', $request);
			$ids = explode('=', $ids[3]);
			$_GET['module'] = 'blockwishlist';
			$controller = 'mywishlist';
			$_GET['default'] = 1;
			$_GET['myajax']  = 1;
			$_GET['id_wishlist']  = end($ids);
			$_GET['action'] = 'setdefault';
			$this->front_controller = self::FC_MODULE;
		}
		if ($controller == 'pagenotfound' && preg_match('/blockwishlist/', $this->request_uri) && preg_match('/view/', $this->request_uri))
		{
			$_GET['module'] = 'blockwishlist';
			$controller = 'view';
			$this->front_controller = self::FC_MODULE;
		}
		if ($controller == 'pagenotfound' && preg_match('/mailalerts/', $this->request_uri) && preg_match('/check/', $this->request_uri))
		{
			$_GET['module'] = 'mailalerts';
			$controller = 'actions';
			$_GET['process'] = 'check';
			$this->front_controller = self::FC_MODULE;
		}
		if ($controller == 'pagenotfound' && preg_match('/mailalerts/', $this->request_uri) && preg_match('/add/', $this->request_uri))
		{
			$_GET['module'] = 'mailalerts';
			$controller = 'actions';
			$_GET['process'] = 'add';
			$this->front_controller = self::FC_MODULE;
		}
		if ($controller == 'pagenotfound' && preg_match('/sagepaycw/', $this->request_uri))
		{
			$sage_url = explode('/', $this->request_uri);
			$controler_name = explode('?', $sage_url[3]);
			$id_module = explode('=', $controler_name[1]);
			if ($id_module[1])
			{
				$_GET['module'] = 'sagepaycw';
				$controller = 'ajax';
				$_GET['id_module'] = $id_module[1];
				$this->front_controller = self::FC_MODULE;
			}
		}
		if ($controller == 'pagenotfound' && preg_match('/sagepaycw/', $this->request_uri) && preg_match('/payment/', $this->request_uri))
		{
			$sage_url = explode('/', $this->request_uri);
			$controler_name = explode('?', $sage_url[3]);
			$id_module = explode('=', $controler_name[1]);
			if ($id_module[1])
			{
				$_GET['module'] = 'sagepaycw';
				$controller = 'payment';
				$_GET['id_module'] = $id_module[1];
				$this->front_controller = self::FC_MODULE;
			}
		}
		if ($controller == 'pagenotfound' && preg_match('/realexredirect/', $this->request_uri))
		{
			$req_uri = explode('/', $this->request_uri);
			$request = end($req_uri);
			$req_uri_qmark = explode('?', $request);
			$clearify_request = str_replace('-', ' ', $req_uri_qmark[0]);
			$_GET['module'] = 'realexredirect';
			$controller = $clearify_request;
			$this->front_controller = self::FC_MODULE;
		}
		//Check for 404 page finally
		if (preg_match('/404/', $this->request_uri) && !preg_match('/token/', $this->request_uri))
			$controller = 'pagenotfound';
		//FIX for Advance CMS module
		if (preg_match('/cms/', $this->request_uri) && !preg_match('/token/', $this->request_uri) && $controller != 'cms')
		{
			//Lets make sure its a module
			$module = (int)$this->getModule('advancedcms');
			if ($module > 0)
			{
				$this->front_controller = self::FC_MODULE;
				$_GET['module'] = 'advancedcms';
				if (!isset($_GET['id_cms']))
				{
					$_GET['rewrite'] = $_GET['product_rewrite'];
					$controller = 'adcms';
					$_GET['fc'] = 'module';
					$_id = (int)$this->getIdAdvanceCms($_GET['rewrite']);
					$_GET['id_cms'] = $_id;
					unset($_GET['product_rewrite']);
				}
			}
		}
		//FIX for Form Maker module
		if (preg_match('/forms/', $this->request_uri) && !preg_match('/Admin/', $this->request_uri) && !preg_match('/module/', $this->request_uri))
		{
			//Lets make sure its a module
			$module = (int)$this->getModule('formmaker');
			if ($module > 0)
			{
				$this->front_controller = self::FC_MODULE;
				$_GET['module'] = 'formmaker';
				if (!isset($_GET['id_form']))
				{
					if (preg_match('/\?/', $this->request_uri))
					{
						$req_uri = explode('?', $this->request_uri);
						$_new_uri = $req_uri[0];
						$_new_uri = explode('/', $_new_uri);
						$_new_uri = end($_new_uri);
						$_GET['rewrite'] = $_new_uri;
					}
					else
						$_GET['rewrite'] = $_GET['product_rewrite'];
					$controller = 'form';
					$_GET['fc'] = 'module';
					$_id = (int)$this->getIdFormsMod($_GET['rewrite']);
					$_GET['id_form'] = $_id;
					unset($_GET['product_rewrite']);
				}
			}
		}
		//FIX for Form Maker module
		if (preg_match('/formmaker/', $this->request_uri) && preg_match('/module/', $this->request_uri) && !preg_match('/token/', $this->request_uri))
		{
			$this->front_controller = self::FC_MODULE;
			$_GET['module'] = 'formmaker';
			$controller = 'formsuccess';
		}
		//FIX for Gallerique Image Gallery module
		if (preg_match('/gallery/', $this->request_uri) && !preg_match('/token/', $this->request_uri))
		{
			//Lets make sure its a module
			$module = (int)$this->getModule('gallerique');
			if ($module > 0)
			{
				$this->front_controller = self::FC_MODULE;
				$_GET['module'] = 'gallerique';
				if (!isset($_GET['id_gallery']))
				{
					$_GET['link_rewrite'] = $_GET['product_rewrite'];
					$controller = 'gallery';
					$_GET['fc'] = 'module';
					$_id = (int)$this->getIdGallery($_GET['link_rewrite']);
					$_GET['id_gallery'] = $_id;
					unset($_GET['product_rewrite']);
				}
			}
		}
		//FIX for Gallerique Image Gallery module
		if (preg_match('/galleries/', $this->request_uri) && !preg_match('/token/', $this->request_uri))
		{
			//Lets make sure its a module
			$module = (int)$this->getModule('gallerique');
			if ($module > 0)
			{
				$this->front_controller = self::FC_MODULE;
				$_GET['module'] = 'gallerique';
				if (!isset($_GET['id_gallery']))
				{
					$_GET['link_rewrite'] = $_GET['product_rewrite'];
					$controller = 'gallerylist';
					$_GET['fc'] = 'module';
					//$_id = (int)$this->getIdGallery($_GET['link_rewrite']);
					//$_GET['id_gallery'] = $_id;
					unset($_GET['product_rewrite']);
				}
				//print_r($_GET); exit;
			}
		}
		$this->controller = str_replace('-', '', $controller);
		$_GET['controller'] = $this->controller;
		return $this->controller;
	}

	private function getCategoryId($request)
	{
		$id_lang = Context::getContext()->language->id;
		$id_shop = Context::getContext()->shop->id;
		$sql = 'SELECT id_category FROM '._DB_PREFIX_.'category_lang
				WHERE link_rewrite = "'.pSQL($request).'" AND id_lang = '.(int)$id_lang.' AND id_shop = '.(int)$id_shop;
		return Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($sql);
	}

	private function getControllerPageById($id)
	{
		return Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue('SELECT `page` 
				FROM '._DB_PREFIX_.'meta
				WHERE id_meta = '.(int)$id);
	}

	private function getKeyExistance($req_uri)
	{
		$id_lang = Context::getContext()->language->id;
		$id_shop = Context::getContext()->shop->id;
		if (strpos($req_uri, '?'))
		{
			$req_uri_qmark = explode('?', $req_uri);
			$req_uri = $req_uri_qmark[0];
			return Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue('SELECT id_meta 
					FROM '._DB_PREFIX_.'meta_lang
					WHERE url_rewrite = "'.pSQL($req_uri).'"'.'
					AND `id_lang` = '.(int)$id_lang.' AND `id_shop` = '.(int)$id_shop);
		}
		else
		{
			return Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue('SELECT id_meta 
					FROM '._DB_PREFIX_.'meta_lang
					WHERE url_rewrite = "'.pSQL($req_uri).'"'.'
					AND `id_lang` = '.(int)$id_lang.' AND `id_shop` = '.(int)$id_shop);
		}
	}

	private function getProductExistance($request)
	{
		$id_lang = Context::getContext()->language->id;
		$id_shop = Context::getContext()->shop->id;
		return Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue('SELECT `id_product`
				FROM '._DB_PREFIX_.'product_lang
				WHERE `link_rewrite` = "'.pSQL($request).'"'.'
				AND `id_lang` = '.(int)$id_lang.'
				AND `id_shop` = '.(int)$id_shop);
	}

	private function getKeyExistanceCMS($request)
	{
		$id_lang = Context::getContext()->language->id;
		$id_shop = Context::getContext()->shop->id;
		return Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue('SELECT `id_cms`
				FROM '._DB_PREFIX_.'cms_lang
				WHERE `link_rewrite` = "'.pSQL($request).'"'.'
				AND `id_lang` = '.(int)$id_lang.'
				AND `id_shop` = '.(int)$id_shop);
	}

	private function getKeyExistanceManuf($request)
	{
		return Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue('SELECT `id_manufacturer`
				FROM '._DB_PREFIX_.'manufacturer
				WHERE `name` LIKE "'.pSQL($request).'"');
	}

	private function getProductExistanceByRewrite($id)
	{
		$id_lang = Context::getContext()->language->id;
		$id_shop = Context::getContext()->shop->id;
		return Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue('SELECT `link_rewrite`
			FROM '._DB_PREFIX_.'product_lang
			WHERE `id_product` = '.(int)$id.'
			AND `id_lang` = '.(int)$id_lang.'
			AND `id_shop` = '.(int)$id_shop);
	}
	
	private function getIdAdvanceCms($request)
	{
		return Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue('SELECT `id_ad_cms`
				FROM '._DB_PREFIX_.'ad_cms_lang
				WHERE `link_rewrite` = "'.pSQL($request).'"');
	}
	
	private function getIdFormsMod($request)
	{
		return Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue('SELECT `id_fm_form`
				FROM '._DB_PREFIX_.'fm_form_lang
				WHERE `link_rewrite` = "'.pSQL($request).'"');
	}
	
	private function getIdGallery($request)
	{
		return Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue('SELECT `id_gallery`
				FROM '._DB_PREFIX_.'gallery_lang
				WHERE `link_rewrite` = "'.pSQL($request).'"');
	}
	
	private function getModule($module)
	{
		return Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue('SELECT `active`
		FROM '._DB_PREFIX_.'module
		WHERE `name` = "'.pSQL($module).'"');
	}
}