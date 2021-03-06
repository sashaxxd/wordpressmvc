<?php
namespace Wdpro;


abstract class BasePage extends BaseEntity
{

	/**
	 * Инициализация типа страниц
	 */
	public static function init() {

		parent::init();
		/** @var BasePage $className */
		$className = get_called_class();
		wdpro_register_post_type($className);
		$type = static::getType();
		add_action('init', function () use (&$type)
		{
			register_post_type( $type, array(
				'public'       => true,
				'hierarchical'=>true,

			) );
		});

		// Это чтобы главная загружалась
		add_action('pre_get_posts', function ($query) use (&$type) {

			if(
				(!isset($query->query_vars['post_type'])
					||'' == $query->query_vars['post_type'])
				&& isset($query->query_vars['page_id'])
				&& 0 != $query->query_vars['page_id']) {

				$post = wdpro_get_post_by_id($query->query_vars['page_id']);
				$query->query_vars['post_type'] = array( 'page', $post->getType());
			}

			return $query;
		});
	}


	/**
	 * Текст страницы
	 *
	 * @param string $content Текущий текст страницы
	 */
	public function getCard(&$content) {

	}


	/**
	 * Инициализация страницы до отправки html кода в браузер
	 */
	public function initCard() {

		if (isset($this->data['image']) && $this->data['image']) {
			wdpro_data('ogImage', WDPRO_UPLOAD_IMAGES_URL.$this->data['image']);
		}
	}


	/**
	 * Инициализация текущей страницы, когда она открывается
	 *
	 * Этот метод срабатывает еще до getCard
	 */
	public function initSite() {

	}


	/**
	 * Возвращает данные сущности вместе с данными своего поста
	 *
	 * @return array
	 */
	public function getDataWithPost()
	{
		$data = wdpro_extend(get_post($this->id(), ARRAY_A), $this->getData());

		$data['url'] = home_url($data['post_name']);

		return $data;
	}


	/**
	 * Возвращает данные поста
	 *
	 * @return array|null|\WP_Post
	 */
	public function getPostData()
	{
		return get_post($this->id(), ARRAY_A);
	}


	/**
	 * Возвращает родительскую страницу (раздел)
	 *
	 * @return \Wdpro\BasePage
	 * @throws \Exception
	 */
	public function getParent()
	{
		$data = $this->getPostData();
		if (isset($data['post_parent']) && $data['post_parent'])
		{
			return wdpro_object_by_post_id($data['post_parent']);
		}

		// Когда есть адрес родительской страницы
		// Это не нужно в админке, а то хзлебные крошки пртятся
		if (!is_admin() && $uri = static::getParentUri()) {

			/** @var \Wdpro\BasePage $post */
			$post = wdpro_get_post_by_name($uri);
			/*if (static::getType() != $post::getType()) {
				throw new Exception('Родительская страница, указанная в '
					.get_class($this).'::getSubParentUri() относится к тому же классу
					страниц');
			}*/
			return $post;
		}
	}


	/**
	 * Адрес страницы, которая является родительской для всех страниц данного типа
	 *
	 * Это нужно для того, чтобы можно было указать адрес той страницы,
	 * которая в хлебных крошках должна быть левее страниц данного класса
	 */
	public static function getParentUri() {

	}


	/**
	 * Возвращает адрес страницы
	 *
	 * @return string
	 */
	public function getUri()
	{
		return get_post_field('post_name', $this->id());
	}


	/**
	 * Возвращает адрес для хлебных крошек на сайте
	 *
	 * @return string
	 */
	public function getBreadcrumbsUrl() {
		return wdpro_home_url_with_lang().$this->getData('post_name').'/';
	}


	/**
	 * Возвращает адрес страницы с подразделами этого поста
	 *
	 * @param string|null $childsType Тип дочерних элементов
	 * У одного и того же раздела могут быть разные типы дочерних элементов. Например,
	 * у одного и того же раздела каталога могут быть как товары, так и подразделы
	 * каталога
	 * @return string
	 */
	public function getConsoleListUri($childsType=null)
	{
		if (!$childsType)
		{
			$childsType = static::getType();
		}
		return 'edit.php?post_type='.$childsType.'&sectionId='.$this->id();
	}


	/**
	 * Возвращает адрес редактирования
	 *
	 * @return string
	 */
	public function getEditUrl() {
		return WDPRO_CONSOLE_URL.'post.php?post='.$this->id().'&action=edit';
	}


	/**
	 * Возвращает адрес страницы для хлебных крошек
	 *
	 * @param string|null $childsType Тип дочерних элементов
	 * У одного и того же раздела могут быть разные типы дочерних элементов. Например,
	 * у одного и того же раздела каталога могут быть как товары, так и подразделы
	 * каталога
	 *
	 * @return string
	 */
	public function getConsoleBreadcrumbsUri($childsType=null) {

		// Если это фотогалерея, то возвращаем адрес редактироваания
		if ($_GET['childsType']) {

		}

		// Если это что-то другое (подразделы), то возвращаем адрес с подразделами
		return $this->getConsoleListUri($childsType);
	}


	/**
	 * Возаращает текст кнопки этого поста
	 *
	 * @return string
	 */
	public function getButtonText()
	{
		return $this->getData('post_title[lang]');
//		return get_post_field('post_title', $this->id());
	}


	/**
	 * Возвращает количество дочерних элементов
	 *
	 * @param \Wdpro\Console\Roll|string $childsRollOrType Дочерний список или тип дочерних
	 * элементов
	 * @return null|number
	 */
	public function getChildsCount($childsRollOrType)
	{
		// Список
		if (is_object($childsRollOrType))
			return parent::getChildsCount($childsRollOrType);


		// Просто тип
		$typeSql = '';

		$postType = $childsRollOrType::getType();
		if ($postType)
		{
			$typeSql = ' AND post_type=%s ';
		}

		return \Wdpro\Page\SqlTable::count(array(
			'WHERE post_parent=%d '.$typeSql
			.' AND post_status!="trash" AND post_status!="auto-draft"',
			$this->id(),
			$postType
		));
	}


	/**
	 * Проверка абсолютного адреса на совпадение с адресом страницы
	 *
	 * @param string $url Проверяемый адрес
	 * @return bool
	 */
	public function isUrl($url)
	{
		return home_url($this->getUri()) == $url;
	}


	/**
	 * Проверка относительного адреса на совпадение с адресом страницы
	 *
	 * @param string $uri Проверяемый адрес
	 * @return bool
	 */
	public function isUri($uri) {

		return $this->getUri() == $uri;
	}


	/**
	 * Возвращает количество подразделов для страницы
	 *
	 * @throws EntityException
	 */
	public function getSubsectionsCount() {
		if ($this->sqlTable()->isField('post_parent')) {
			return $this->sqlTable()->count([
				'WHERE `post_parent`=%d AND `post_status`="publish"',
				[$this->id()]
			]);
		}

		return 0;
	}


	/**
	 * Подготавливает данные для сохранения перед первым сохранением черновика
	 *
	 * В вордпресс страницы сохраняются сразу, как только была открыта форма создания.
	 * То есть еще до того, как заполнили форму создания.
	 *
	 * Этот метод обрабатывает данные еще до того, как форма была отправлена.
	 * То есть сразу, как была открыта форма и еще не сохранялась.
	 *
	 * @param array $data Исходные данные
	 * @return array
	 *
	 * @return mixed
	 */
	protected function prepareDataForCreateDraft($data) {
		return $data;
	}


	/**
	 * Возвращает меню редактирования страницы (кнопками на дочерные элементы)
	 *
	 * @return string
	 */
	public function getEditFormMenu() {

		$html = '';

		$actions = $this->addChildsToActions([]);

		$roll = static::getConsoleRoll();
		/*if ($roll::isSubsections()) {

			$parentId = 0;
			if ($parent = $this->getParent()) {
				$parentId = $parent->id();
			}


			$actions['wdpro_subsections'] =
				'<a href="'
				.WDPRO_CONSOLE_URL.'edit.php?post_type='.static::getType().'&sectionId='
				.$parentId
				.'" class="js-subsections"><span 
									class="fa fa-folder"></span> Подразделы</a>';
		}*/

		foreach ($actions as $link) {
			$html .= '<li>'.$link.'</li>';
		}

		return '<ul class="wdpro-edit-menu">'.$html.'</ul>';
	}


	/**
	 * В BaseEntity и BasePage различается механизм запуска методов для обработки данных
	 * перед первым сохранением (созданием) сущности в базу
	 *
	 * Именно за счет этого метода и реализован разный механизм
	 *
	 * @param $data
	 *
	 * @return mixed
	 */
	protected function _prepareData($data) {
		$data = $this->prepareDataForCreateDraft($data);

		return $data;
	}


	/**
	 * Возвращает title
	 *
	 * @return string
	 */
	public function getTitle() {
		return $this->getData('post_title[lang]');
	}


	/**
	 * Обновляет позицию в меню
	 *
	 * @param int $menu_order Позиция
	 */
	public function setMenuOrder($menu_order) {
		$this->data['menu_order'] = $menu_order;
		$this->save();

		wp_update_post([
			'ID'=>$this->id(),
			'menu_order'=>$menu_order,
		]);
	}


	/**
	 * Проверяет, есть ли у страницы перевод на язык
	 *
	 * Это нужно, например, при формировании на сайте переключателя языков. Когда у
	 * текущей страницы есть перевод на английский. То кнопка "EN" переключает на
	 * английскуюверсию этой страницы. А если нет, то на главную.
	 *
	 * @param string $lang uri языка (ru, en)
	 *
	 * @return bool
	 */
	public function isLang($lang) {
		$key = 'post_title'.\Wdpro\Lang\Data::getSuffix($lang);
		return isset($this->data[$key]) && $this->data[$key];
	}


	/**
	 * Возвращает true если это главная страница
	 *
	 * @return bool
	 */
	public function isHome() {
		return get_option('show_on_front')
		       && $this->id() == wdpro_get_option('page_on_front');
	}
}
