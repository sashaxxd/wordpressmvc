<?php
namespace Wdpro\Contacts;

class Controller extends \Wdpro\BaseController {
	
	/** @var  \Wdpro\Form\Form */
	protected static $backFormClass = BackForm::class;

	/**
	 * Выполнение скриптов после инициализаций всех модулей (в админке)
	 */
	public static function runConsole() {

		// Меню - Контакты
		\Wdpro\Console\Menu::add(array(
			'roll'=>ConsoleRoll::class,
			'n'=>95,
			'icon'=>'fas fa-map-marked',
		));
		
		// Настройки
		\Wdpro\Console\Menu::addSettings('Страница &quot;Контакты&quot;', function ($form) {
			
			/** @var \Wdpro\Form\Form $form */
			$form->add(array(
				'name'=>'contacts_form_sended[lang]',
				'top'=>'Текст после отправки формы',
				'type'=>'ckeditor',
			));

			$form->addHeader('Форма');
			$contactsForm = new static::$backFormClass();
			/** @var $contactsForm \Wdpro\Form\Form */
			$contactsForm->eachElements(function ($element) use (&$form) {
				/** @var $element \Wdpro\Form\Elements\Base */

				$params = $element->getParams();
				if (isset($params['name'])) {
					if (isset($params['top'])) {
						$form->add([
							'name'=>'contacts_form_element_'.$params['name'].'_top[lang]',
							'top'=>$params['top'],
						]);
					}

					if (isset($params['left'])) {
						$form->add([
							'name'=>'contacts_form_element_'.$params['name'].'_left[lang]',
							'top'=>$params['left'],
						]);
					}

					if (isset($params['right'])) {
						$form->add([
							'name'=>'contacts_form_element_'.$params['name'].'_right[lang]',
							'top'=>$params['right'],
						]);
					}

				}

				if (isset($params['type']) && $params['type'] == 'submit') {
					$form->add([
						'name'=>'contacts_form_element_submit[lang]',
						'top'=>'Отправить',
					]);
				}
			});

			$form->add('submitSave');
			
			return $form;
		});
	}


	/**
	 * Выполнение скриптов после инициализаций всех модулей (на сайте)
	 */
	public static function runSite() {

		add_shortcode('contacts_list', function ($params) {

			/*wdpro_default_file(
				__DIR__.'/default/templates/contacts_list.php',
				WDPRO_TEMPLATE_PATH.'contacts_list.php'
			);*/
			
			return Roll::getHtml('ORDER BY sorting');
		});
		
		add_shortcode('contacts_form', function () {
			
			/** @var \Wdpro\Form\Form $form */
			$form = new static::$backFormClass();

			$form->eachElements(function ($element) use (&$form) {
				/** @var $element \Wdpro\Form\Elements\Base */

				$params = $element->getParams();
				if (isset($params['name'])) {
					$top = wdpro_get_option('contacts_form_element_'.$params['name'].'_top[lang]');
					if ($top) {
						$element->mergeParams(['top'=>$top]);
					}

					$left = wdpro_get_option('contacts_form_element_'.$params['name'].'_left[lang]');
					if ($left) {
						$element->mergeParams(['left'=>$left]);
					}

					$right = wdpro_get_option('contacts_form_element_'.$params['name'].'_right[lang]');
					if ($right) {
						$element->mergeParams(['right'=>$right]);
					}
				}

				if (isset($params['type']) && $params['type'] == 'submit') {
					$value = wdpro_get_option('contacts_form_element_submit[lang]');
					if ($value) {
						$element->mergeParams(['value'=>$value]);
					}
				}
			});
			
			return $form->getHtml();
		});
	}


	/**
	 * Дополнительная инициализация для сайта
	 */
	public static function init() {

		// Отправка формы
		wdpro_ajax('contactsBack', function ($data) {

			/** @var \Wdpro\Form\Form $form */
			$form = new static::$backFormClass();
			
			$form->setData($data);
			$form->sendToAdmins('Форма обратной связи');
			
			return array(
				'message'=>wdpro_get_option(
					'contacts_form_sended[lang]', 'Ваше сообщение отправлено.'),
			);
		});
	}


}

return __NAMESPACE__;